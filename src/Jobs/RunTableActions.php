<?php

namespace NotFound\Framework\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use NotFound\Framework\Services\Assets\TableActionService;

/**
 * Applies the retention actions configured in cms_table_actions.
 *
 * Scheduled daily by the FrameworkServiceProvider, can also be run by
 * hand with `php artisan siteboss:table-actions`.
 */
class RunTableActions implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Release the uniqueness lock after an hour, so a job that died
     * without cleaning up does not block the next run.
     */
    public int $uniqueFor = 3600;

    public function __construct(private bool $dryRun = false) {}

    public function handle(): void
    {
        (new TableActionService(dryRun: $this->dryRun))->run();
    }
}
