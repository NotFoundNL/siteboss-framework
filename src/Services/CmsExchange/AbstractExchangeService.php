<?php

namespace NotFound\Framework\Services\CmsExchange;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

    /**
     * Removes every foreign key from a table. Used on the backup tables,
     * which inherit the constraint names of the table they were renamed
     * from, names InnoDB requires to be unique within the schema.
     */
    protected function dropForeignKeys(string $tableName): void
    {
        $foreignKeys = Schema::getForeignKeys($tableName);

        if ($foreignKeys === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($foreignKeys) {
            foreach ($foreignKeys as $foreignKey) {
                $table->dropForeign($foreignKey['name']);
            }
        });
    }

    protected function debug($text, $force = false)
    {
        if ($this->debug || $force) {
            printf("\n - %s", $text);
        }
    }
}
