<?php

namespace NotFound\Framework\Services\Indexer;

use NotFound\Framework\Models\CmsSearch;

abstract class AbstractIndexService
{
    public int $languageId;

    public int $siteId;

    public ?string $domain;

    abstract public function __construct($debug = false);

    abstract public function startUpdate(): bool;

    abstract public function finishUpdate(): object;

    abstract public function upsertItem(SearchItem $searchItem): object;

    abstract public function checkConnection(): bool;

    public function clean(): bool
    {
        CmsSearch::truncate();

        return true;
    }

    public function urlNeedsUpdate(string $url, $updated): bool
    {
        $searchItem = CmsSearch::whereUrl($this->siteUrl($url))->first();
        if ($searchItem && ($searchItem->updated_at !== null && $searchItem->updated_at->timestamp > $updated)) {
            CmsSearch::whereUrl($url)->update(['search_status' => 'SKIPPED']);

            return false;
        }

        return true;
    }

    protected function siteUrl($url): string
    {
        if ($this->domain) {
            return sprintf('%s/%s', rtrim($this->domain, '/'), ltrim($url, '/'));
        }

        return $url;
    }
}
