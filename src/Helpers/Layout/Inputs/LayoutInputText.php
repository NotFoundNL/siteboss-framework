<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

class LayoutInputText extends AbstractInput
{
    protected string $type = 'InputText';

    protected string $value = '';

    public function setRichtext(): self
    {
        $this->properties->type = 'richtext';

        return $this;
    }

    public function setEndpoint(string $endPoint): self
    {
        $this->properties->endPoint = $endPoint;

        return $this;
    }

    public function setRegEx(string $regEx): self
    {
        $this->properties->regEx = $regEx;

        return $this;
    }

    public function getAjaxDataPost(): array
    {
        return [
            'location' => 'test.jpg',
        ];
    }
}
