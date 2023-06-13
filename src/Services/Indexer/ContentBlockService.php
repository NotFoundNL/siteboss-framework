<?php

namespace NotFound\Framework\Services\Indexer;

use Illuminate\Support\Str;

class ContentBlockService
{
    public function __construct(private $blocks)
    {
    }

    private function getModelByTablename($tableName)
    {
        $className = sprintf("App\Models\%s", Str::studly(strtolower(Str::singular($tableName))));
        if (class_exists($className)) {
            return new $className;
        }
        dd('Unknown class '.$className);
    }

    public function toText()
    {
        $text = '';

        foreach ($this->blocks as $block) {
            $model = $this->getModelByTableName($block->type);
            $indexTypes = $model->indexValues ?? [];
            foreach ($block->values as $t => $v) {
                if (in_array($t, $indexTypes)) {
                    $text .= ' '.$v;
                }
            }
        }

        return $text;
    }
}
