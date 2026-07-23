<?php

namespace NotFound\Framework\Helpers\Layout\Elements\Table;

use stdClass;

/**
 * LayoutTableColumn
 *
 * Created a new header UI element for tables. Can only be added to LayoutTableRow.
 */
class LayoutTableColumn
{
    public function __construct(
        public string|bool $value,
        public string $type = 'text',
        public object $properties = new stdClass
    ) {
        return $this;
    }

    public function setToggleEndPoint(string $url): self
    {
        if ($this->type !== 'checkbox') {
            throw new \Exception('Can only set toggle endpoint on checkbox type');
        }
        $this->properties->toggleEndPoint = $url;

        return $this;
    }

    public function makeLinkButton(string $to, $external = false): self
    {
        $this->type = 'InputButton';
        $this->properties->link = $to;
        $this->properties->external = $external;

        return $this;
    }

    public function makeDownloadButton(string $action): self
    {
        $this->type = 'InputButton';
        $this->properties->action = $action;

        return $this;
    }
}
