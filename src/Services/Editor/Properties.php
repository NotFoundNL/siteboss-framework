<?php

namespace NotFound\Framework\Services\Editor;

use stdClass;

abstract class Properties
{
    protected $properties = [];

    protected $serverProperties = [];

    protected $server = false;

    protected $values = null;

    /**
     * __construct
     *
     * @param  mixed  $properties  Properties object from database
     *
     * This method will fill the properties arrays with the properties of the field
     * it will alto store the current values in the values object
     * @param  mixed  $values
     * @return void
     */
    public function __construct($values)
    {
        $this->values = $this->updatePropertyNames($values);
        $this->server = false;
        $this->properties();
        $this->server = true;
        $this->serverProperties();
    }

    abstract public function properties(): void;

    abstract public function serverProperties(): void;

    abstract public function description(): string;

    abstract public function checkColumnType(?string $type): string;

    protected function addText($property_name, $display_name, $required = false, $default = null)
    {
        $this->add((object) ['type' => 'Text', 'name' => $property_name, 'display_name' => $display_name, 'required' => $required, 'default' => $default]);
    }

    protected function addNumber($property_name, $display_name, $required = false, $default = null)
    {
        $this->add((object) ['type' => 'Number', 'name' => $property_name, 'display_name' => $display_name, 'required' => $required, 'default' => $default]);
    }

    protected function addCheckbox($property_name, $display_name, $default = false)
    {
        $this->add((object) ['type' => 'Checkbox', 'name' => $property_name, 'display_name' => $display_name, 'default' => $default]);
    }

    protected function addTitle($title)
    {
        $this->add((object) ['type' => 'Header', 'name' => $title]);
    }

    protected function addRepeatable($property_name, $display_name, Repeatable $subItems)
    {
        $this->add((object) ['type' => 'Repeatable', 'name' => $property_name, 'display_name' => $display_name, 'subItems' => $subItems->getServerProperties()]);
    }

    protected function add($fieldProperties)
    {
        if ($this->server) {
            $this->serverProperties[] = $fieldProperties;
        } else {
            $this->properties[] = $fieldProperties;
        }
    }

    protected function allOverviewOptions()
    {
        $this->overview();
        $this->searchable();
        $this->sortable();
    }

    protected function addMultiSelect($property_name, $display_name, $optionsArray)
    {
        $this->add((object) ['type' => 'MultiSelect', 'name' => $property_name, 'display_name' => $display_name, 'options' => $optionsArray]);
    }

    protected function addDropDown($property_name, $display_name, $optionsArray)
    {
        $this->add((object) ['type' => 'Dropdown', 'name' => $property_name, 'display_name' => $display_name, 'options' => $optionsArray]);

        return $this;
    }

    protected function required(): self
    {
        $this->add((object) ['type' => 'Checkbox', 'name' => 'required', 'display_name' => 'Field is required']);

        return $this;
    }

    protected function searchable(): self
    {
        $this->add((object) ['type' => 'Checkbox', 'name' => 'searchable', 'display_name' => 'Searchable in overview']);

        return $this;
    }

    protected function noIndex(): self
    {
        $this->add((object) ['type' => 'Checkbox', 'name' => 'noIndex', 'display_name' => 'Do not search via SOLR']);

        return $this;
    }

    protected function sortable(): self
    {
        $this->add((object) ['type' => 'Checkbox', 'name' => 'sortable', 'display_name' => 'Sortable in overview']);

        return $this;
    }

    protected function overview(): self
    {
        $this->add((object) ['type' => 'Checkbox', 'name' => 'overview', 'display_name' => 'Show in overview']);

        return $this;
    }

    protected function localize(): self
    {
        $this->add((object) ['type' => 'Checkbox', 'name' => 'localize', 'display_name' => 'Field is localized']);

        return $this;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getServerProperties()
    {
        return $this->serverProperties;
    }

    protected function rename(): array
    {
        return [];
    }

    final protected function updatePropertyNames(?stdClass $properties): stdClass
    {
        if (! $properties) {
            return new stdClass;
        }
        foreach ($this->rename() as $old => $new) {
            if (isset($properties->$old) && ! isset($properties->$new)) {
                $properties->$new = $properties->$old;
                unset($properties->$old);
            }
        }

        return $properties;
    }
}
