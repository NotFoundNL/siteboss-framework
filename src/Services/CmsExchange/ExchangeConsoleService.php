<?php

namespace NotFound\Framework\Services\CmsExchange;

class ExchangeConsoleService
{
    public function __construct(
        private bool $debug = false,
        private bool $dryRun = false
    ) {}

    public function import(): void
    {
        $this->debug('Starting CMS Import');
        if ($this->dryRun) {
            $this->debug('Dry Run: true', force: true);
        }

        $tableExchangeService = new TableExchangeService;
        $tableExchangeService->import($this->debug, $this->dryRun);
        $templateExchangeService = new TemplateExchangeService;
        $templateExchangeService->import($this->debug, $this->dryRun);

        $this->debug('DONE');
    }

    private function debug($text, $force = false)
    {
        if ($this->debug || $force) {
            printf("\n - %s", $text);
        }
    }
}
