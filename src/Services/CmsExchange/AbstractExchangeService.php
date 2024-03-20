<?php

namespace NotFound\Framework\Services\CmsExchange;

abstract class AbstractExchangeService
{
    protected bool $debug;

    protected bool $dryRun;

    protected string $exportTypeName;

    abstract public function runImport(): void;

    abstract public function exportRetainIds(): bool;

    abstract public function exportTypeName(): string;

    public function exportTypeNamePlural(): string
    {
        return $this->exportTypeName().'s';
    }

    public function import(bool $debug = false, bool $dryRun = false): void
    {
        $this->debug = $debug;
        $this->dryRun = $dryRun;
        $this->runImport();
    }

    protected function debug($text, $force = false)
    {
        if ($this->debug || $force) {
            printf("\n - %s", $text);
        }
    }
}
