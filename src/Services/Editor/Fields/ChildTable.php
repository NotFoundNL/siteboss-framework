<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Models\Table;
use NotFound\Framework\Services\Editor\Properties;

class ChildTable extends Properties
{
    public function description(): string
    {
        return 'ChildTable';
    }

    public function properties(): void
    {
        $tables = Table::all();
        $options = [];
        foreach ($tables as $table) {
            $options[] = (object) ['value' => $table->table, 'label' => $table->name];
        }
        $this->addDropDown('allowedBlocks', 'Select child table', $options);
        $this->addText('prefix', 'Remove prefix from foreign key');
    }

    public function serverProperties(): void
    {
    }

    public function checkColumnType(?\Doctrine\DBAL\Types\Type $type): string
    {
        if ($type !== null) {
            return 'COLUMN NOT NEEDED';
        }

        return '';
    }
}
