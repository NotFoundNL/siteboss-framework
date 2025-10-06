<?php

namespace NotFound\Framework\Services\Indexer;

use NotFound\Framework\Models\CmsSearch;
use NotFound\Framework\Models\Indexes\SolrIndex;
use stdClass;

class SolrIndexService extends AbstractIndexService
{
    public int $siteId;

    public ?string $domain;

    public $solrIndex;

    public function __construct(bool $debug = false, bool $fresh = false)
    {
        $this->debug = $debug;
        $this->fresh = $fresh;
        $this->solrIndex = new SolrIndex;
    }

    public function retainItem(string $url): void
    {
        $cmsSearchItem = CmsSearch::whereUrl($this->domain.$url)->first();
        if ($cmsSearchItem) {
            $cmsSearchItem->search_status = 'UPDATED';
            $cmsSearchItem->save();
        }
    }

    public function upsertItem(SearchItem $searchItem): object
    {
        $return = $this->returnvalue();
        $cmsSearchItemStatus = '';

        if ($searchItem->type() === 'file') {
            $result = $this->solrIndex->upsertFile($searchItem, $this->siteId, $this->domain);

            $return = $this->returnvalue();
            if ($result == 'success') {
                $cmsSearchItemStatus = 'UPDATED';
            } elseif ($result == 'fileNotFound') {
                $cmsSearchItemStatus = 'NOT_FOUND';
                $return->errorCode = 1;
                $return->message = "failed: file not found \n";
            } else {
                $cmsSearchItemStatus = 'NOT_INDEXABLE';
                $result = $this->solrIndex->upsertItem($searchItem, $this->siteId, $this->domain);
                if ($result) {
                    $cmsSearchItemStatus = 'UPDATED';
                } else {
                    $return->errorCode = 1;
                    $return->message = "failed: file not indexable \n";
                }
            }
        } else {
            $result = $this->solrIndex->upsertItem($searchItem, $this->siteId, $this->domain);

            if ($result) {
                $cmsSearchItemStatus = 'UPDATED';
            } else {
                $cmsSearchItemStatus = 'FAILED';
                $return->errorCode = 1;
                $return->message = "failed: item not indexed \n";
            }
        }

        $cmsSearchItem = CmsSearch::firstOrNew(['url' => $this->solrIndex->siteUrl($searchItem->url(), $this->domain)]);
        $cmsSearchItem->setValues($searchItem, $cmsSearchItemStatus);
        $cmsSearchItem->url = $this->solrIndex->siteUrl($searchItem->url(), $this->domain);

        if (in_array($cmsSearchItemStatus, ['NOT_FOUND', 'FAILED'])) {
            $cmsSearchItem->updated_at = null;
        }
        $cmsSearchItem->save();

        return $return;
    }

    public function startUpdate(): bool
    {
        if ($this->debug) {
            printf("\n ** Starting SOLR update");
        }
        $emptyResult = true;
        if ($this->fresh) {
            $emptyResult = $this->solrIndex->emptyCore();
        }
        CmsSearch::setAllPending();

        return $emptyResult;
    }

    public function finishUpdate(): object
    {
        if ($this->debug) {
            printf("\n ** Removing all pending items\n");
        }
        $return = $this->removeAllPending();
        if ($this->debug) {
            printf("\n ** Rebuilding suggester\n");
        }
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
            if ($this->solrIndex->removeItem($pending->url)) {
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
        $return = new stdClass;

        $return->errorCode = 0;
        $return->message = '';
        $return->data = [];

        return $return;
    }

    public function checkConnection(): bool
    {
        return $this->solrIndex->checkConnection();
    }
}
