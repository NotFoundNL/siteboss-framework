<?php

namespace NotFound\Framework\Services\CmsExchange;

use File;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\TableAction;
use NotFound\Framework\Models\TableItem;

class TableExchangeService extends AbstractExchangeService
{
    protected string $exportTypeName = 'table';

    public function runImport(): void
    {
        $this->debug('Starting CMS Table Import');
        $this->importTables();
    }

    public function hasChanges(Table $table): bool
    {
        $path = resource_path('siteboss/tables/'.$table->table.'.json');
        if (! File::exists($path)) {
            return false;
        }
        $data = $table->exportToObject();
        $fileData = json_decode(file_get_contents($path));

        return $data != $fileData;
    }

    private function importTables(): object
    {
        $path = resource_path('siteboss/tables');
        if (! File::exists($path)) {
            $this->debug('No export files found in '.$path);

            return (object) [];
        }

        // Create temp table to import into
        $this->createImportTables();

        $fileSources = [];
        $files = File::files($path);
        foreach ($files as $file) {
            $index = str_replace('.json', '', $file->getFilename());
            $fileSources[$index] = json_decode(file_get_contents($file->getPathname()));
        }
        $this->debug('== Read '.count($fileSources).' files from '.$path);

        $order = 1;
        foreach ($fileSources as $tableName => $fileSource) {
            if ($this->dryRun) {
                $this->debug('CREATE TABLE '.$tableName);
            } else {

                $table = new Table;
                if (isset($fileSource->id)) {
                    $table->id = $fileSource->id;
                }
                $table->name = $fileSource->name;
                $table->model = $fileSource->model ?? null;
                $table->url = $fileSource->url;
                $table->rights = $fileSource->rights;

                $table->comments = $fileSource->comments;
                $table->allow_create = $fileSource->allow_create;
                $table->allow_delete = $fileSource->allow_delete;
                $table->allow_sort = $fileSource->allow_sort;
                // Not present in files exported before version 1.4.0
                $table->allow_archive = $fileSource->allow_archive ?? false;
                $table->allow_duplicate = $fileSource->allow_duplicate ?? false;
                $table->properties = $fileSource->properties;
                $table->order = $order++;
                $table->enabled = $fileSource->enabled;
                $table->table = $tableName;
                $table->save();

                $tableId = $table->id;

                foreach ($fileSource->actions ?? [] as $action) {
                    $tableAction = new TableAction;
                    $tableAction->table_id = $tableId;
                    $tableAction->condition = $action->condition;
                    $tableAction->days = $action->days;
                    $tableAction->action = $action->action;
                    $tableAction->save();
                }
            }
            $itemOrder = 1;
            foreach ($fileSource->items as $item) {
                if ($this->dryRun) {
                    $this->debug(' [x] '.$item->name);

                    continue;
                }
                $tableItem = new TableItem;
                if (isset($item->id)) {
                    $tableItem->id = $item->id;
                }
                $tableItem->table_id = $tableId;
                $tableItem->name = $item->name;
                $tableItem->type = $item->type;
                $tableItem->internal = $item->internal;
                $tableItem->description = $item->description;
                $tableItem->properties = $item->properties;
                $tableItem->server_properties = $item->server_properties;
                $tableItem->order = $itemOrder++;
                $tableItem->enabled = $item->enabled;
                $tableItem->rights = $item->rights;

                $tableItem->save();

            }

        }

        return (object) [];
    }

    private function createImportTables(): void
    {
        if ($this->dryRun) {
            $this->debug('Dry run: skipping table creation');

            return;
        }
        $this->debug('Creating import tables');

        // cms_table_actions references cms_table, so it has to be moved out
        // of the way first. A renamed table keeps its foreign key constraint
        // names, and InnoDB requires those to be unique within the schema, so
        // they are dropped from the backup to free the names for the new table.
        Schema::dropIfExists('cms_table_actions_backup');
        if (Schema::hasTable('cms_table_actions')) {
            Schema::rename('cms_table_actions', 'cms_table_actions_backup');
            $this->dropForeignKeys('cms_table_actions_backup');
        }

        Schema::dropIfExists('cms_table_backup');
        Schema::rename('cms_table', 'cms_table_backup');
        $this->dropForeignKeys('cms_table_backup');
        Schema::create('cms_table', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->nullable();
            $table->string('table', 128)->nullable();
            $table->string('model', 128)->nullable();
            $table->string('url', 128)->nullable();
            $table->string('rights', 128)->nullable();
            $table->text('comments')->nullable();
            $table->boolean('allow_create')->default(true);
            $table->boolean('allow_delete')->default(true);
            $table->boolean('allow_sort')->default(true);
            $table->boolean('allow_archive')->default(false);
            $table->boolean('allow_duplicate')->default(false);
            $table->json('properties')->nullable();
            $table->integer('order')->nullable();
            $table->tinyInteger('enabled')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('cms_table_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('cms_table')->cascadeOnDelete();
            $table->enum('condition', ['create', 'edit', 'archive', 'delete']);
            $table->unsignedInteger('days');
            $table->enum('action', ['archive', 'delete', 'purge']);
            $table->timestamps();
        });

        Schema::dropIfExists('cms_tableitem_backup');
        Schema::rename('cms_tableitem', 'cms_tableitem_backup');
        $this->dropForeignKeys('cms_tableitem_backup');
        Schema::create('cms_tableitem', function (Blueprint $table) {
            $table->id();
            $table->string('rights', 128)->default('');
            $table->foreignIdFor(Table::class, 'table_id')->nullable();
            $table->string('type', 64)->nullable();
            $table->string('internal', 64)->nullable();
            $table->string('name', 128)->nullable();
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->json('server_properties')->nullable();
            $table->integer('order')->nullable(); // TODO: FIX
            $table->tinyInteger('enabled')->nullable()->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

    }

    public function exportTypeName(): string
    {
        return 'table';
    }

    public function exportRetainIds(): bool
    {
        return config('siteboss.export_retain_ids') ?? false;
    }
}
