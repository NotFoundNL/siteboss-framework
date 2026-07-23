<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

use App\Services\Assets\Components\AbstractComponent;
use Illuminate\Support\Collection;

class LayoutInputContentBlocks extends AbstractInput
{
    protected string $type = 'InputContentBlocks';

    protected mixed $value = null;

    public function setValue(mixed $value): self
    {
        /* print_r($value); */
        if ($value instanceof Collection) {
            $contentBlocksWithValues = [];

            $this->setContentBlock($value, $contentBlocksWithValues);

            $this->value = $contentBlocksWithValues;
        }

        return $this;
    }

    private function setContentBlock($contentBlocks, &$contentBlocksWithValues)
    {
        foreach ($contentBlocks as $contentBlock) {
            $arrayItems = [];

            $tableId = null;
            $recordId = null;
            foreach ($contentBlock as $component) {
                /** @var AbstractComponent $component */
                $tableId = $component->assetItem->table_id;
                $recordId = $component->getRecordId();
                $arrayItems[] = $component->buildAutoLayoutClass();
            }

            $contentBlockObject = (object) [
                'record_id' => $recordId,
                'table_id' => $tableId,
                'items' => $arrayItems,
            ];

            $contentBlocksWithValues[] = $contentBlockObject;
        }
    }
}
