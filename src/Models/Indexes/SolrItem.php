<?php

namespace NotFound\Framework\Models\Indexes;

use Illuminate\Support\Str;
use NotFound\Framework\Models\BaseModel;

class SolrItem extends BaseModel
{
    private $header;

    private $number;

    private $results;

    private $highlights;

    private $spellcheck;

    private $collations;

    private $suggestions;

    private $correctlySpelled;

    private $predictions;

    public $suggest;

    private $q;

    private $collate;

    private $fl;

    // @TODO: implement support for multiple terms
    public function __construct($solr, $q, $collate = false, private int $highlightLength = 50)
    {
        $this->header = isset($solr->responseHeader) ? $solr->responseHeader : null;
        $fl = $this->header->params->fl ?? 'url title type';
        $this->fl = explode(' ', $fl);
        $this->results = isset($solr->response->docs) ? $solr->response->docs : null;
        $this->highlights = isset($solr->highlighting) ? $solr->highlighting : null;
        $this->spellcheck = $solr->spellcheck ?? null;
        $this->suggest = isset($solr->suggest) ? $solr->suggest : null;
        $this->number = isset($solr->response->numFound) ? $solr->response->numFound : 0;

        $this->collations = isset($solr->spellcheck->collations) ? $solr->spellcheck->collations : false;
        $this->suggestions = isset($solr->spellcheck->suggestions) ? $solr->spellcheck->suggestions : false;
        $this->correctlySpelled = isset($solr->spellcheck->correctlySpelled) ? $solr->spellcheck->correctlySpelled : false;

        $predictions = null;
        if (
            isset($this->result->responseHeader->params->facet) && $this->result->responseHeader->params->facet
            && isset($this->result->responseHeader->params->{'facet.field'})
        ) {
            $facet = $this->result->responseHeader->params->{'facet.field'};
            $predictions = $this->result->facet_counts->facet_fields->$facet;
        }
        $this->predictions = $predictions;
        $this->q = $q;

        $this->collate = $collate;
        $this->error = isset($solr->error) ? $solr->error : null;
    }

    public function isValid()
    {
        return $this->header && $this->header->status == 0;
    }

    public function number()
    {
        return $this->number;
    }

    public function resultList()
    {
        if (isset($this->results)) {
            $resultList = [];
            foreach ($this->results as $result) {
                $resultArray = [];
                foreach ($this->fl as $column) {
                    if ($column == 'url') {
                        $resultArray[$column] = $this->parseUrl($result->{$column});
                    } else {
                        $resultArray[$column] = isset($result->{$column}[0]) ? $result->{$column}[0] : '';
                    }
                }

                $resultArray['summary'] = '';
                if (isset($this->highlights->{$result->url}->{$this->header->params->{'hl.fl'}}[0])) {
                    $summary = Str::words(preg_replace("/^\p{P}\s+/", '', $this->highlights->{$result->url}->{$this->header->params->{'hl.fl'}}[0]), $this->highlightLength, ' ...');
                    $summary = Str::limit($summary, 500);
                    $resultArray['summary'] = $summary;
                }
                $resultList[] = (object) $resultArray;
            }

            return $resultList;
        }
    }

    private function parseUrl($rawurl)
    {
        return preg_replace('/\{\{(\d+)\}\}/', '', $rawurl);
    }

    public function getSuggestions()
    {
        $suggestions = $this->suggestions();
        if (! $suggestions) {
            $suggestions = $this->collations();
        }

        return $suggestions;
    }

    public function suggestions()
    {
        $suggestions = [];
        foreach ($this->suggesters as $s) {
            if (isset($this->suggest->$s->{$this->q}->suggestions)) {
                $suggestions = array_merge($suggestions, $this->suggest->$s->{$this->q}->suggestions);
            } else {
                return false;
            }
        }

        return $suggestions;
    }

    public function collations()
    {
        $queries = [];

        if ($this->collations && $this->collate) {
            foreach ($this->collations as $c) {
                if (isset($c->hits) && isset($c->collationQuery)) {
                    $querytext = $this->queryText($c->collationQuery);
                    $corrections = isset($c->misspellingsAndCorrections) ? $this->parseCorrections($c->misspellingsAndCorrections) : [];
                    $emphasizedArray = [];
                    foreach (explode(' ', $querytext) as $word) {
                        if (in_array(str_replace('"', '', $word), $corrections)) {
                            $word = sprintf('<em>%s</em>', $word);
                        }
                        $emphasizedArray[] = $word;
                    }
                    $queries[] = [
                        'term' => implode(' ', $emphasizedArray),
                        'payload' => '?q='.urlencode($querytext).'',
                    ];
                }
            }
        }

        return $queries;
    }

    // @TODO: finish implementation of predictions
    public function predictions()
    {
        $queries = [];
        $term = $this->q;
        while ($current = current($this->predictions)) {
            $next = next($this->predictions);
            if (is_string($current) && is_int($next)) {
                $resultList[$current]['count'] = $next;
                $fullTerm = $term.' '.$current;
                $queries[] = [
                    'term' => $fullTerm,
                    'payload' => '?q='.urlencode($fullTerm).'',
                ];
            }
        }

        return $queries;
    }

    public function spellcheckList()
    {
        $items = [];
        $query = $this->q;

        if (isset($this->spellcheck)) {
            foreach ($this->spellcheck->suggestions as $suggestion) {
                if (isset($suggestion->startOffset)) {
                    $suggest = substr($query, 0, $suggestion->startOffset).'<em>'.$suggestion->suggestion[0].'</em>'.substr($query, $suggestion->endOffset);
                    $suggest = preg_replace('/^([a-zA-Z])+:/', '', $suggest); // remove search field if necessary

                    $suggest_url = substr($query, 0, $suggestion->startOffset).$suggestion->suggestion[0].substr($query, $suggestion->endOffset);
                    $suggest_url = preg_replace('/^([a-zA-Z])+:/', '', $suggest_url); // remove search field if necessary

                    $items[] = (object) ['link' => '?q='.rawurlencode(urldecode($suggest_url)), 'text' => urldecode($suggest)];
                }
            }
        }

        return $items;
    }
}
