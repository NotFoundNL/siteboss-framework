<?php

namespace NotFound\Framework\Models\Indexes;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use NotFound\Framework\Mail\Indexer\FileIndexError;
use NotFound\Framework\Mail\Indexer\QueryError;
use NotFound\Framework\Models\BaseModel;
use NotFound\Framework\Models\CmsSearch;

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

    private ?String $solrUser;

    private ?String $solrCore;

    private ?String $solrPass;

    public ?string $selectField = null;

    public string $wt = 'json';

    public string $selectOperator = 'AND';

    public string $hl = 'on';

    public string $hlfl = 'content';

    public string $fl = 'url title type';

    public int $hlfragsize = 300;

    public int $hlmaxAnalyzedChars = 100000;

    public string $df = 'content';

    public ?String $sort = null;

    // suggest parameters
    public array $suggester = [];

    public ?string $suggestField = 'suggest';

    public bool $collate = true;

    // spellcheck parameters
    public ?string $spellcheckField = null;

    public function __construct($debug = false)
    {
        $this->solrHost = env('SOLR_HOST', true);
        $this->solrUser = env('SOLR_USER', true);
        $this->solrCore = env('SOLR_CORE', true);
        $this->solrPass = env('SOLR_PASS', true);
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

            if (! $json || ! isset($json->responseHeader) || $json->responseHeader->status !== 0) {
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
        curl_setopt($handler, CURLOPT_USERPWD, $this->solrUser.':'.$this->solrPass);

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

    public function addOrUpdateItem(string $url, string $title, string $contents, string $type, int $lang, int $siteId, array $customValues, int $priority): bool
    {
        $curl = $this->solrHandler();

        $doc = [
            'title' => $title,
            'content' => html_entity_decode(trim(preg_replace('/\s+/', ' ', strip_tags($contents)))),
            'type' => $type,
            'url' => $url,
            'priority' => $priority,
            'site' => $siteId,
            'language' => $lang,
        ];
        foreach ($customValues as $key => $value) {
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
        $json = json_decode($result);
        if ($json && isset($json->responseHeader) && $json->responseHeader->status == 0) {
            return true;
        }

        return false;
    }

    public function removeItem($url)
    {
        if (! is_null($url)) {
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

    public function addOrUpdateFile(string $url, string $title, string $file, string $type, int $lang, int $siteId, array $customValues, int $priority): string
    {
        // find out of document exists
        $result = 0;
        $file = Storage::disk('private')->path($file);

        if (file_exists($file)) {
            $curl = $this->solrHandler();

            $endpoint = sprintf(
                '%s/update/extract?literal.url=%s&literal.title=%s&literal.type=%s&literal.site=%s&literal.language=%d&commit=true',
                $this->getSolrBaseUrl(),
                urlencode($url),
                urlencode($title),
                $type,
                $siteId,
                $lang
            );
            foreach ($customValues as $key => $value) {
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
                $this->mailFileError($title, $url, 'file is leeg');

                return 'fileIsEmpty';
            } else {
                $result = $this->addOrUpdateItem($url, $title, '', $type, $lang, $siteId, $customValues, $priority);
                if ($result) {
                    return 'fileIsNotIndexable';
                } else {
                    return 'unkownFileError';
                }
            }
        } else {
            $this->mailFileError($title, $url, 'file bestaat niet');

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

    public function selectItems($query, $filter = null, $start = null, $rows = null, $extraColumns = [], $highlightLength = 50)
    {
        $curl = $this->solrHandler();
        $url = sprintf(
            '%s/select?q=content:%s&wt=%s&hl=%s&q.op=%s&hl.fl=%s&fl=%s&spellcheck=true&hl.fragsize=%d&hl.maxAnalyzedChars=%d',
            $this->getSolrBaseUrl(),
            rawurlencode($query), // make sure + between search terms is preserved
            $this->wt,
            $this->hl,
            $this->selectOperator,
            $this->hlfl,
            urlencode($this->fl),
            $this->hlfragsize,
            $this->hlmaxAnalyzedChars,
        );
        if ($filter) {
            $url .= '&fq='.$filter;
        }
        if ($start && is_int($start)) {
            $url .= '&start='.$start;
        }

        if ($rows && is_int($rows)) {
            $url .= '&rows='.$rows;
        }

        if (count($extraColumns) > 0) {
        }

        if ($this->sort) {
            $url .= '&sort='.urlencode($this->sort);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        $result = curl_exec($curl);
        $json = json_decode($result);
        $searchResults = new SolrItem($json, $query, false, $highlightLength);
        if (! $searchResults->isValid()) {
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
        if (! $suggestions->isValid()) {
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

    private function explodeSuggesters(): string
    {
        $suggesterString = '';
        foreach ($this->suggester as $s) {
            $suggesterString .= sprintf('&suggest.dictionary=%s', $s);
        }

        return $suggesterString;
    }
}
