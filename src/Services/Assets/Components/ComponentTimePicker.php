<?php

namespace NotFound\Framework\Services\Assets\Components;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidDateException;
use Illuminate\Support\Facades\Log;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Inputs\LayoutInputTimePicker;

class ComponentTimePicker extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutInputTimePicker($this->assetItem->internal, $this->assetItem->name);
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }

    public function setValue($value)
    {
        if ($value === null || empty(trim($value))) {
            $this->currentValue = '';

            return;
        }

        if ($this->isValidDate($value)) {
            $this->currentValue = Carbon::parse($value)->format('H:i:s');
        }
    }

    private function isValidDate($value): bool
    {
        try {
            Carbon::parse($value)->format('H:i:s');

            return true;
        } catch (InvalidDateException $exp) {
            Log::warning('Failed date parse: '.$exp->getMessage());

            return false;
        }
    }
}
