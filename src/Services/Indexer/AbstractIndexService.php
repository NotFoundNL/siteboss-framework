<?php

namespace NotFound\Framework\Services\Indexer;

abstract class AbstractIndexService
{
    public Int $languageId;

    public Int $siteId;

    abstract public function __construct($debug = false);

    abstract public function startUpdate(): bool;

    abstract public function finishUpdate(): object;

    abstract public function urlNeedsUpdate(string $url, $updated): bool;

    abstract public function upsertItem(SearchItem $searchItem): bool;
}
