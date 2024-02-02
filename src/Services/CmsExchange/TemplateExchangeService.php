<?php

namespace NotFound\Framework\Services\CmsExchange;

use File;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\Template;
use NotFound\Framework\Models\TemplateItem;

class TemplateExchangeService extends AbstractExchangeService
{
    protected string $exportTypeName = 'template';

    public function runImport(): void
    {
        $this->debug('Starting CMS Template Import');
        $this->importTemplates();
    }

    public function hasChanges(Template $table): bool
    {
        // TODO: Implement hasChanges() method + call it when updating templates
        $path = resource_path('siteboss/templates/'.$table->filename.'.json');
        if (! File::exists($path)) {
            return false;
        }
        $data = $table->exportToObject();
        $fileData = json_decode(file_get_contents($path));

        return $data != $fileData;
    }

    private function importTemplates(): object
    {
        $path = resource_path('siteboss/templates');
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
                $table = new Template();
                $table->id = $fileSource->id;
                $table->name = $fileSource->name;
                $table->filename = $fileSource->filename;
                $table->rights = $fileSource->rights;

                $table->allow_children = $fileSource->allow_children;
                $table->params = $fileSource->params;
                $table->desc = $fileSource->desc;
                $table->properties = $fileSource->properties;
                $table->order = $order++;
                $table->enabled = $fileSource->enabled;
                $table->save();
            }
            $itemOrder = 1;
            foreach ($fileSource->items as $item) {
                if ($this->dryRun) {
                    $this->debug(' [x] '.$item->name);

                    continue;
                }
                $tableItem = new TemplateItem();
                if (isset($item->id)) {
                    $tableItem->id = $item->id;
                }
                $tableItem->template = $table->id;
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
        Schema::dropIfExists('cms_template_backup');
        Schema::rename('cms_template', 'cms_template_backup');
        Schema::create('cms_template', function (Blueprint $table) {
            $table->id();
            $table->string('rights', 128)->nullable();
            $table->string('name', 128)->nullable();
            $table->string('desc', 255)->nullable();
            $table->string('filename', 128)->nullable();
            $table->string('allow_children', 128)->nullable();
            $table->string('params', 128)->nullable();
            $table->json('properties')->nullable();
            $table->integer('order')->nullable();
            $table->tinyInteger('enabled')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::dropIfExists('cms_templateitem_backup');
        Schema::rename('cms_templateitem', 'cms_templateitem_backup');
        Schema::create('cms_templateitem', function (Blueprint $table) {
            $table->id();
            $table->string('rights', 128)->default('');
            $table->foreignIdFor(Template::class, 'template')->nullable();
            $table->string('type', 64)->nullable();
            $table->string('internal', 64)->nullable();
            $table->string('name', 128)->nullable();
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->json('server_properties')->nullable();
            $table->boolean('global')->default(0); 
            $table->integer('order')->nullable(); 
            $table->tinyInteger('enabled')->nullable()->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

    }

    public function exportTypeName(): string
    {
        return 'template';
    }

    public function exportRetainIds(): bool
    {
        return true;
    }
}
