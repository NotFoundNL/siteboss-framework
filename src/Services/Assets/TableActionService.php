<?php

namespace NotFound\Framework\Services\Assets;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\TableAction;
use NotFound\Framework\Services\Assets\Enums\TableActionType;
use NotFound\Framework\Services\Legacy\StatusColumn;
use Throwable;

/**
 * Runs the retention actions configured in cms_table_actions.
 *
 * For every action it scans the site table for records of which the
 * timestamp belonging to the condition (created_at, updated_at,
 * archived_at or deleted_at) has not changed for `days` days, and then
 * archives, deletes or purges those records through the TableService.
 */
class TableActionService
{
    private ?Lang $lang = null;

    public function __construct(
        private bool $debug = false,
        private bool $dryRun = false,
    ) {}

    /**
     * Runs the actions of every table that has them.
     */
    public function run(): void
    {
        $tables = Table::has('actions')->with('actions')->get();

        $this->debug(sprintf('Running table actions for %d table(s)', $tables->count()));

        foreach ($tables as $table) {
            $this->runForTable($table);
        }
    }

    public function runForTable(Table $table): void
    {
        try {
            $siteTableName = $table->getSiteTableName();
        } catch (Throwable $e) {
            $this->warn(sprintf('Table %d (%s) has no site table: %s', $table->id, $table->name, $e->getMessage()));

            return;
        }

        if (! Schema::hasTable($siteTableName)) {
            $this->warn(sprintf('Skipping %s, table does not exist', $siteTableName));

            return;
        }

        foreach ($table->actions->sortBy('id') as $action) {
            $this->runAction($table, $action, $siteTableName);
        }
    }

    private function runAction(Table $table, TableAction $action, string $siteTableName): void
    {
        if (! $this->canApply($action, $siteTableName)) {
            return;
        }

        $recordIds = $this->expiredRecordIds($action, $siteTableName);

        if ($recordIds->isEmpty()) {
            $this->debug(sprintf('%s: no records match %s', $siteTableName, $this->describe($action)));

            return;
        }

        $this->debug(sprintf(
            '%s: %s %d record(s) [%s]',
            $siteTableName,
            $this->dryRun ? 'would apply to' : 'applying to',
            $recordIds->count(),
            $this->describe($action)
        ));

        if ($this->dryRun) {
            return;
        }

        $applied = 0;
        foreach ($recordIds as $recordId) {
            if ($this->apply($table, $action, (int) $recordId)) {
                $applied++;
            }
        }

        Log::info(sprintf(
            '[TableActionService] %s: %s applied to %d of %d record(s)',
            $siteTableName,
            $this->describe($action),
            $applied,
            $recordIds->count()
        ));
    }

    /**
     * Whether the site table has the columns this action needs.
     */
    private function canApply(TableAction $action, string $siteTableName): bool
    {
        $conditionColumn = $action->condition->timestampColumn();

        if (! Schema::hasColumn($siteTableName, $conditionColumn)) {
            $this->warn(sprintf('Skipping %s on %s, column %s does not exist', $this->describe($action), $siteTableName, $conditionColumn));

            return false;
        }

        if ($action->action === TableActionType::ARCHIVE && ! Schema::hasColumn($siteTableName, 'archived_at')) {
            $this->warn(sprintf('Skipping %s on %s, column archived_at does not exist', $this->describe($action), $siteTableName));

            return false;
        }

        if ($action->action === TableActionType::DELETE
            && ! Schema::hasColumn($siteTableName, 'deleted_at')
            && ! Schema::hasColumn($siteTableName, 'status')
        ) {
            $this->warn(sprintf('Skipping %s on %s, table has no deleted_at or status column', $this->describe($action), $siteTableName));

            return false;
        }

        return true;
    }

    /**
     * The ids of the records that have not been touched for `days` days,
     * skipping the records the action was already applied to.
     */
    private function expiredRecordIds(TableAction $action, string $siteTableName): Collection
    {
        $conditionColumn = $action->condition->timestampColumn();

        $query = DB::table($siteTableName)
            ->whereNotNull($conditionColumn)
            ->where($conditionColumn, '<=', now()->subDays($action->days));

        if ($action->action === TableActionType::ARCHIVE) {
            $query->whereNull('archived_at');
        }

        if ($action->action === TableActionType::DELETE) {
            StatusColumn::wherePublished($query, $siteTableName);
        }

        return $query->orderBy('id')->pluck('id');
    }

    private function apply(Table $table, TableAction $action, int $recordId): bool
    {
        try {
            $tableService = new TableService($table, $this->lang(), $recordId);

            match ($action->action) {
                TableActionType::ARCHIVE => $tableService->archive(),
                TableActionType::DELETE => $tableService->delete(),
                TableActionType::PURGE => $tableService->purge(),
            };

            return true;
        } catch (Throwable $e) {
            Log::error(sprintf(
                '[TableActionService] %s failed on %s record %d: %s',
                $this->describe($action),
                $table->getSiteTableName(),
                $recordId,
                $e->getMessage()
            ));

            return false;
        }
    }

    /**
     * Only resolved once a record is actually being changed, a dry run
     * should not depend on a default language being present.
     */
    private function lang(): Lang
    {
        return $this->lang ??= Lang::default();
    }

    private function describe(TableAction $action): string
    {
        return sprintf('%s after %d days since %s', $action->action->value, $action->days, $action->condition->value);
    }

    private function warn(string $text): void
    {
        Log::warning('[TableActionService] '.$text);

        $this->debug($text);
    }

    private function debug(string $text): void
    {
        if ($this->debug) {
            printf("\n - %s", $text);
        }
    }
}
