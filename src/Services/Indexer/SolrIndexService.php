<?php

namespace NotFound\Framework\Services\Indexer;

use NotFound\Framework\Models\CmsSearch;
use NotFound\Framework\Models\Indexes\SolrIndex;
use stdClass;

class SolrIndexService extends AbstractIndexService
{
    private Bool $debug = false;

    public int $siteId;

    public $solrIndex;

    public function __construct($debug = false)
    {
        $this->debug = $debug;
        $this->solrIndex = new SolrIndex();
    }

    public function urlNeedsUpdate(string $url, $updated): bool
    {
        $searchItem = CmsSearch::whereUrl($url)->first();
        if ($searchItem && $searchItem->updated_at->timestamp > $updated) {
            CmsSearch::whereUrl($url)->update(['search_status' => 'SKIPPED']);

            return false;
        }

        return true;
    }

    public function upsertUrl(string $url, string $title, string $contents, string $type, string $lang, array $customValues = [], $priority = 1): object
    {
        $result = $this->solrIndex->addOrUpdateItem($this->siteUrl($url), $title, $contents, $type, $lang, $this->siteId, $customValues, $priority);
        $return = $this->returnvalue();

        if ($result) {
            $cmsSearchItem = CmsSearch::firstOrNew(['url' => $url]);
            $cmsSearchItem->type = $type;
            $cmsSearchItem->url = $url;
            $cmsSearchItem->search_status = 'UPDATED';
            $cmsSearchItem->language = $lang;
            $cmsSearchItem->save();
        } else {
            $return->errorCode = 1;
        }

        return $return;
    }

    public function upsertFile(string $url, string $title, string $file, string $type, string $lang, array $customValues = [], $priority = 1): object
    {
        $result = $this->solrIndex->addOrUpdateFile($this->siteUrl($url), $title, $file, $type, $lang, $this->siteId, $customValues, $priority);

        $return = $this->returnvalue();

        if ($result == 'success') {
            $cmsSearchItem = CmsSearch::firstOrNew(['url' => $url]);
            $cmsSearchItem->type = $type;
            $cmsSearchItem->url = $url;
            $cmsSearchItem->search_status = 'UPDATED';
            $cmsSearchItem->language = $lang;
            $cmsSearchItem->save();
        } elseif ($result == 'fileNotFound') {
            $cmsSearchItem = CmsSearch::firstOrNew(['url' => $url]);
            $cmsSearchItem->type = $type;
            $cmsSearchItem->url = $url;
            $cmsSearchItem->search_status = 'NOT_FOUND';
            $cmsSearchItem->language = $lang;
            $cmsSearchItem->save();
            $return->errorCode = 1;
            $return->message = "failed: file not found \n";
        } else {
            $cmsSearchItem = CmsSearch::firstOrNew(['url' => $url]);
            $cmsSearchItem->type = $type;
            $cmsSearchItem->url = $url;
            $cmsSearchItem->search_status = 'NOT_INDEXABLE';
            $cmsSearchItem->language = $lang;
            $cmsSearchItem->save();
            $return->errorCode = 1;
            $return->message = "failed: file not indexable \n";
        }

        return $return;
    }

    private function siteUrl($url): string
    {
        return sprintf('{{%d}}%s', $this->siteId, $url);
    }

    public function startUpdate(): bool
    {
        if ($this->debug) {
            printf("\n ** Starting SOLR update");
        }
        $emptyResult = $this->solrIndex->emptyCore();
        CmsSearch::setAllPending();

        return $emptyResult;
    }

    public function finishUpdate(): object
    {
        $return = $this->removeAllPending();

        $build = $this->solrIndex->buildSuggester();

        if ($build->error) {
            $return->message .= sprintf("Error when building suggester: %s\n", $build->error->msg);
        } else {
            $return->message .= "Suggester has been built\n";
        }

        return $return;
    }

    public function removeAllPending(): object
    {
        $return = $this->returnValue();
        $pendingDocs = CmsSearch::query()->whereSearchStatus('PENDING')->get();
        $removed = 0;
        $return->data['failed'] = [];
        foreach ($pendingDocs as $pending) {
            if ($this->solrIndex->removeItem($this->siteUrl($pending->url))) {
                CmsSearch::whereUrl($pending->url)->forceDelete();
                $removed++;
            } else {
                $return->data['failed'] = $pending->url;
                $return->errorCode = 1;
            }
        }
        $return->message = sprintf("Successfully removed %d items from index. Failed to remove %d items.\n", $removed, count($return->data['failed']));

        return $return;
    }

    private function returnValue()
    {
        $return = new stdClass();

        $return->errorCode = 0;
        $return->message = '';
        $return->data = [];

        return $return;
    }
}
