<?php

namespace NotFound\Framework\Services\Assets\Components;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidDateException;
use Illuminate\Support\Facades\Log;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Inputs\LayoutInputDatePicker;

class ComponentDatePicker extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutInputDatePicker($this->assetItem->internal, $this->assetItem->name);
    }

    public function setValue($value)
    {
        if ($value == null || empty(trim($value))) {
            $this->currentValue = '';

            return;
        }

        if ($this->isValidDate($value)) {
            $this->currentValue = Carbon::parse($value);
        }
    }

    private function isValidDate($value): bool
    {
        try {
            Carbon::parse($value);

            return true;
        } catch (InvalidDateException $exp) {
            Log::warning('Failed date parse: '.$exp->getMessage());

            return false;
        }
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }

    public function getTableOverviewContent(): LayoutTableColumn
    {
        // TODO: implement more general language specific date formatting
        $lang = app()->getLocale() == 'nl' ? 'nl_NL' : 'en_US';
        $format = new \IntlDateFormatter(
            $lang,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            'Europe/Amsterdam',
            \IntlDateFormatter::GREGORIAN
        );
        $date = new \DateTime($this->getCurrentValue());

        return new LayoutTableColumn($format->format($date), 'Text');
    }

    public function setValueFromStorage(string $value): bool
    {
        $timeValue = intval($value);
        if ($timeValue == 0) {
            $this->currentValue = '';
        } else {
            $this->currentValue = date('Y-m-d', intval($value));
        }

        return true;
    }

    /**
     * Get the value used in the default storage mechanism.
     * This is always a string. Use JSON or your own logic for other types of values.
     *
     * @return string
     */
    public function getValueForStorage(): ?string
    {
        if ($this->newValue == null) {
            return '0';
        }

        return strtotime($this->newValue);
    }
}
