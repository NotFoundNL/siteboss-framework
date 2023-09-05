<?php

namespace NotFound\Framework\Services\Indexer;

use NotFound\Framework\Models\CmsSearch;

abstract class AbstractIndexService
{
    public Int $languageId;

    public Int $siteId;

    abstract public function __construct($debug = false);

    abstract public function startUpdate(): bool;

    abstract public function finishUpdate(): object;

    abstract public function upsertItem(SearchItem $searchItem): object;

    abstract public function checkConnection(): bool;

    public function urlNeedsUpdate(string $url, $updated): bool
    {
        $searchItem = CmsSearch::whereUrl($url)->first();
        if ($searchItem && ($searchItem->updated_at !== null && $searchItem->updated_at->timestamp > $updated)) {
            CmsSearch::whereUrl($url)->update(['search_status' => 'SKIPPED']);

            return false;
        }

        return true;
    }

    protected function siteUrl($url): string
    {
        return sprintf('{{%d}}%s', $this->siteId, $url);
    }
}
