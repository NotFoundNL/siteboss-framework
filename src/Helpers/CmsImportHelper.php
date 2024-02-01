<?php

namespace NotFound\Framework\Helpers;

use File;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\TableItem;

class CmsImportHelper
{
    public function __construct(
        private bool $debug = false,
        private bool $dryRun = false
    ) {
    }

    public function import(): void
    {
        $this->debug('Starting CMS Import');
        if ($this->dryRun) {
            $this->debug('Dry Run: true', force: true);
        }

        $this->importTables('cms_users');
        $this->debug('DONE');
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

    private function importTables(string $tableName): object
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

                $table = new Table();
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
                $table->properties = $fileSource->properties;
                $table->order = $order++;
                $table->enabled = $fileSource->enabled;
                $table->table = $tableName;
                $table->save();

                $tableId = $table->id;
            }
            $itemOrder = 1;
            foreach ($fileSource->items as $item) {
                if ($this->dryRun) {
                    $this->debug(' [x] '.$item->name);

                    continue;
                }
                $tableItem = new TableItem();
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

    private function debug($text, $force = false)
    {
        if ($this->debug || $force) {
            printf("\n - %s", $text);
        }
    }

    private function createImportTables(): void
    {
        if ($this->dryRun) {
            $this->debug('Dry run: skipping table creation');

            return;
        }
        $this->debug('Creating import tables');
        Schema::dropIfExists('cms_table_backup');
        Schema::rename('cms_table', 'cms_table_backup');
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
            $table->json('properties')->nullable();
            $table->integer('order')->nullable();
            $table->tinyInteger('enabled')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('cms_tableitem_backup');
        Schema::rename('cms_tableitem', 'cms_tableitem_backup');
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
            $table->integer('order')->nullable(); //TODO: FIX
            $table->tinyInteger('enabled')->nullable()->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

    }
}
