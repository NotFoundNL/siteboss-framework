<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;
use NotFound\Framework\Models\Table;

class ContentBlocks extends Properties
{
    public function description(): string
    {
        return 'ContentBlocks';
    }

    public function properties(): void
    {
        $tables = Table::all();
        $options = [];
        foreach ($tables as $table) {
            $options[] = (object) ['id' => $table->table, 'label' => $table->name];
        }
        $this->addMultiSelect('allowedBlocks', 'Select the possible options', $options);
    }

    public function serverProperties(): void
    {
    }
}
