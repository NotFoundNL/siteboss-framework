<?php

namespace NotFound\Framework\Helpers;

use File;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Models\CmsConfig;
use NotFound\Framework\Models\Table;

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

        // $tables = Table::all();

        // foreach ($tables as $table) {
        //     $tableName = $table->table;
        //     if (in_array($tableName, $filenames)) {
        //         $tableSettings = $table->exportToObject();
        //         if ($tableSettings == $fileSources[$tableName]) {
        //             $this->debug('Unchanged table '.$table->table);
        //         } else {

        //             $this->debug('Updated table '.$table->table);

        //             //    print_r($tableSettings);1
        //             //  print_r($fileSources[$tableName]);

        //         }
        //     } else {
        //         $this->debug('New table '.$table->table);
        //     }
        // }
        // Schema::dropIfExists('cms_table_backup');

        // Schema::rename($currentTableName, $newTableName);

        return (object) [];
    }

    private function getConfigForDatabaseTable(string $tableName): ?object
    {
        $config = CmsConfig::where('table', $tableName)->first();
        if (! $config) {
            return null;
        }

        return $config->exportToString();
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
        Schema::dropIfExists('cms_import_table');
        Schema::create('cms_import_table', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->nullable();
            $table->string('table', 128)->nullable();
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

    }
}
