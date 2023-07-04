<?php

namespace NotFound\Framework\Services\Indexer;

abstract class AbstractIndexService
{
    private Bool $debug;

    public Int $languageId;

    public Int $siteId;

    abstract public function __construct($debug = false);

    abstract public function startUpdate(): bool;

    abstract public function finishUpdate(): object;

    abstract public function urlNeedsUpdate(string $url, $updated): bool;

    abstract public function upsertUrl(string $url, string $title, string $contents, string $type, string $lang, array $customValues = []): object;

    abstract public function upsertFile(string $url, string $title, string $file, string $type, string $lang, array $customValues): object;
}
