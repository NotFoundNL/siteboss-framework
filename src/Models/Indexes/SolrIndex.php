<?php

namespace NotFound\Framework\Models\Indexes;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use NotFound\Framework\Mail\Indexer\FileIndexError;
use NotFound\Framework\Mail\Indexer\QueryError;
use NotFound\Framework\Models\BaseModel;
use NotFound\Framework\Models\CmsSearch;
use NotFound\Framework\Services\Indexer\SearchItem;

/**
 * NotFound\Framework\Models\Indexes\SolrIndex
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SolrIndex newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SolrIndex newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SolrIndex onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SolrIndex query()
 * @method static \Illuminate\Database\Eloquent\Builder|SolrIndex withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SolrIndex withoutTrashed()
 *
 * @mixin \Eloquent
 */
class SolrIndex extends BaseModel
{
    private ?string $solrHost;

    private ?string $solrUser;

    private ?string $solrCore;

    private ?string $solrPass;

    public ?string $selectField = null;

    public string $wt = 'json';

    public string $selectOperator = 'OR';

    public string $hl = 'on';

    public string $hlfl = 'content';

    public string $fl = 'url title type';

    public int $hlfragsize = 300;

    public int $hlmaxAnalyzedChars = 100000;

    public string $df = 'content';

    public ?string $sort = null;

    // suggest parameters
    public array $suggester = [];

    public ?string $suggestField = 'suggest';

    public bool $collate = true;

    // spellcheck parameters
    public ?string $spellcheckField = null;

    public function __construct($debug = false)
    {
        $this->solrHost = config('indexer.solr.host');
        $this->solrUser = config('indexer.solr.user');
        $this->solrCore = config('indexer.solr.core');
        $this->solrPass = config('indexer.solr.pass');
    }

    public function emptyCore()
    {
        $searchItems = CmsSearch::all();
        if (count($searchItems) == 0) {
            $curl = $this->solrHandler();
            $url = sprintf('%s/update/?wt=%s&commit=true*', $this->getSolrBaseUrl(), $this->wt);
            curl_setopt($curl, CURLOPT_URL, $url);
            $payload = ['delete' => ['query' => '*:*']];

            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
            $result = curl_exec($curl);

            $json = json_decode($result);

            if (!$json || !isset($json->responseHeader) || $json->responseHeader->status !== 0) {
                $this->mailQueryError($url, $result);

                return false;
            }

            return true;
        }

        return true;
    }

    // helper functions
    private function solrHandler()
    {
        $handler = curl_init();
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_USERPWD, $this->solrUser . ':' . $this->solrPass);

        curl_setopt($handler, CURLOPT_POST, true);

        curl_setopt($handler, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        return $handler;
    }

    private function getSolrBaseUrl()
    {
        return sprintf('%s/%s', $this->solrHost, $this->solrCore);
    }

    public function testSolrConnection()
    {
        $curl = $this->solrHandler();
        curl_setopt($curl, CURLOPT_URL, sprintf('%s/%s', $this->getSolrBaseUrl(), 'select?q=*%3A*'));

        $result = curl_exec($curl);
        $json = json_decode($result);
        if ($json && isset($json->responseHeader) && $json->responseHeader->status == 0) {
            return true;
        }

        return false;
    }

    public function upsertItem(SearchItem $indexItem, int $siteId = 1): bool
    {
        $curl = $this->solrHandler();
        $doc = [
            sprintf('title_%s', $indexItem->language()) => $indexItem->title(),
            sprintf('content_%s', $indexItem->language()) => html_entity_decode(trim(preg_replace('/\s+/', ' ', preg_replace('#<[^>]+>#', ' ', $indexItem->content())))),
            'type' => $indexItem->type(),
            'url' => $this->siteUrl($indexItem->url(), $siteId),
            'priority' => $indexItem->priority(),
            'site' => $siteId,
            'language' => $indexItem->language(),
            'solr_date' => $indexItem->publicationDate(),
        ];
        foreach ($indexItem->customValues() as $key => $value) {
            $doc[$key] = $value;
        }

        $payload = ['add' => [
            'doc' => $doc,
            'commitWithin' => 1000,
            'overwrite' => true,
        ]];
        curl_setopt($curl, CURLOPT_URL, sprintf('%s/update/?wt=%s', $this->getSolrBaseUrl(), $this->wt));

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($curl);

        if (curl_errno($curl) === 6) {
            exit('[ERROR] Could not resolve solr host: ' . $this->getSolrBaseUrl());
        }
        $json = json_decode($result);
        if ($json && isset($json->responseHeader) && $json->responseHeader->status == 0) {
            return true;
        }

        return false;
    }

    public function removeItem($url)
    {
        if (!is_null($url)) {
            $curl = $this->solrHandler();

            $payload = ['delete' => $url];
            $url = sprintf(
                '%s/update/?wt=json&commit=true',
                $this->getSolrBaseUrl(),
                urlencode($url)
            );
            curl_setopt($curl, CURLOPT_URL, $url);

            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));

            $result = curl_exec($curl);
            $json = json_decode($result);
            if ($json && isset($json->responseHeader) && $json->responseHeader->status == 0) {
                return true;
            }

            return false;
        }

        return false;
    }

    public function upsertFile(SearchItem $indexItem, int $siteId = 1): string
    {

        // find out of document exists
        $result = 0;
        $file = Storage::disk('private')->path($indexItem->file());

        if (file_exists($file)) {
            $curl = $this->solrHandler();
            $endpoint = sprintf(
                '%s/update/extract?literal.url=%s&literal.title_%s=%s&literal.type=%s&literal.site=%s&literal.language=%d&literal.solr_date=%s&uprefix=ignored_&fmap.content=content_%s&commit=true',
                $this->getSolrBaseUrl(),
                urlencode($this->siteUrl($indexItem->url(), $siteId)),
                $indexItem->language(),
                urlencode($indexItem->title()),
                $indexItem->type(),
                $siteId,
                $indexItem->language(),
                $indexItem->publicationDate(),
                $indexItem->language()

            );
            foreach ($indexItem->customValues() as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $endpoint .= sprintf('&literal.%s=%s', $key, $v);
                    }
                } else {
                    $endpoint .= sprintf('&literal.%s=%s', $key, $value);
                }
            }

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, 1);
            $cFile = curl_file_create($file);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data']);
            curl_setopt($curl, CURLOPT_URL, $endpoint);
            $post = ['file_contents' => $cFile];
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
            $result = curl_exec($curl);
            $json = json_decode($result);
            if ($json && isset($json->responseHeader) && $json->responseHeader->status == 0) {
                return 'success';
            }

            if (\filesize($file) == 0) {
                $this->mailFileError($indexItem->title(), $indexItem->url(), 'file is leeg');

                return 'fileIsEmpty';
            } else {

                $result = $this->upsertItem($indexItem->setContent($indexItem->title()));
                if ($result) {
                    return 'fileIsNotIndexable';
                } else {
                    return 'unknownFileError';
                }
            }
        } else {
            $this->mailFileError($indexItem->title(), $indexItem->url(), 'file bestaat niet');

            return 'fileNotFound';
        }
    }

    private function mailFileError($title, $file, $error)
    {
        $email = env('SOLR_FILE_ERROR_MAIL');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Mail::to($email)->send(new FileIndexError($error, env('MAIL_FROM_NAME'), $file, $title));
        }
    }

    private function mailQueryError($query, $result)
    {
        $email = env('SOLR_FILE_ERROR_MAIL');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Mail::to($email)->send(new QueryError($query, $result, env('MAIL_FROM_NAME')));
        }
    }

    public function selectItems($query, $lang = 'nl', $filter = null, $start = null, $rows = null, $extraColumns = [], $highlightLength = 50, $sortField = null, $sortDirection = 'desc')
    {
        if (trim($query) === '') {
            return null;
        }
        $curl = $this->solrHandler();
        $url = sprintf(
            '%s/select?q=title_%s:%s%%20content_%s:%s&spellcheck.q=%s&wt=%s&hl=%s&q.op=%s&hl.fl=%s&fl=%s&spellcheck=true&hl.fragsize=%d&hl.maxAnalyzedChars=%d&spellcheck.dictionary=spellcheck_%s',
            $this->getSolrBaseUrl(),
            $lang,
            rawurlencode($query), // make sure + between search terms is preserved
            $lang,
            rawurlencode($query), // make sure + between search terms is preserved
            rawurlencode($query), // make sure + between search terms is preserved
            $this->wt,
            $this->hl,
            $this->selectOperator,
            sprintf('%s_%s', $this->hlfl, $lang),
            urlencode($this->fl),
            $this->hlfragsize,
            $this->hlmaxAnalyzedChars,
            $lang
        );
        if ($filter) {
            $url .= '&fq=' . $filter;
        }
        if ($start && is_int($start)) {
            $url .= '&start=' . $start;
        }

        if ($rows && is_int($rows)) {
            $url .= '&rows=' . $rows;
        }
        if (count($extraColumns) > 0) {
        }

        if ($sortField) {
            $url .= '&sort=' . urlencode($sortField . ' ' . $sortDirection);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        $result = curl_exec($curl);
        $json = json_decode($result);
        $searchResults = new SolrItem($json, $query, false, $highlightLength);
        if (!$searchResults->isValid()) {
            $this->mailQueryError($url, $result);
        }

        return $searchResults;
    }

    public function suggestItems($query, $filter = null)
    {
        $curl = $this->solrHandler();

        $url = sprintf('%s&q=%s', $this->suggestUrl(), urlencode($query));
        if ($filter) {
            $url = sprintf('%s&suggest.cfq=%s', $url, $filter);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        $result = curl_exec($curl);
        $json = json_decode($result);
        $suggestions = new SolrItem($json, $query);
        if (!$suggestions->isValid()) {
            $this->buildSuggester();
            $result = curl_exec($curl);
            $json = json_decode($result);

            $suggestions = new SolrItem($json, $query);
        }

        $suggestions->suggesters = $this->suggester;
        $suggestions->q = $query;

        return $suggestions;
    }

    private function suggestUrl()
    {
        $url = sprintf(
            '%s/suggest?q.op=AND',
            $this->getSolrBaseUrl()
        );
        $url .= $this->explodeSuggesters();

        return $url;
    }

    public function buildSuggester()
    {
        $curl = $this->solrHandler();

        $url = sprintf('%s&suggest.build=true', $this->suggestUrl());

        curl_setopt($curl, CURLOPT_URL, $url);
        $result = curl_exec($curl);
        $json = json_decode($result);
        $searchResults = new SolrItem($json, null);

        return $searchResults;
    }

    private function getConfig()
    {
        $curl = $this->solrHandler();
        $url = sprintf('%s/config/searchComponent?componentName=suggest', $this->getSolrBaseUrl());
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, false);

        $result = curl_exec($curl);
        $json = json_decode($result);

        return $json;
    }

    private function allSuggesters()
    {
        $json = $this->getConfig();
        $suggesters = [];
        if (
            $json && isset($json->responseHeader)
            && $json->responseHeader->status == 0
            && isset($json->config->searchComponent->suggest->suggester)
        ) {
            $list = $json->config->searchComponent->suggest->suggester;
            foreach ($list as $s) {
                $suggesters[] = $s->name;
            }
        }

        return $suggesters;
    }

    private function explodeSuggesters(): string
    {
        $suggesterString = '';
        $suggesters = $this->suggester;
        if (count($suggesters) == 0) {
            $suggesters = $this->allSuggesters();
        }
        foreach ($suggesters as $s) {
            $suggesterString .= sprintf('&suggest.dictionary=%s', $s);
        }

        return $suggesterString;
    }

    public function checkConnection(): bool
    {
        $curl = $this->solrHandler();
        curl_setopt($curl, CURLOPT_URL, sprintf('%s/admin/ping', $this->getSolrBaseUrl()));

        $result = curl_exec($curl);
        $json = json_decode($result);

        if (curl_errno($curl) !== 6 && $json && isset($json->status) && $json->status === 'OK') {
            return true;
        }

        return false;
    }

    public function siteUrl($url, $siteId): String
    {
        return sprintf("{{%d}}%s", $siteId, $url);
    }
}
