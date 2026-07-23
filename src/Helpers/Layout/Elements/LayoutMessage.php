<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

class LayoutMessage extends AbstractLayout
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(string $text, string $type = 'info')
    {
        parent::__construct(type: 'Message');
        $this->properties->type = $type;
        $this->properties->intro = $this->setIntro($type);

        $this->properties->text = $text;

        return $this;
    }

    public function setIntroText(string $text): self
    {
        $this->properties->intro = $text;

        return $this;
    }

    private function setIntro(string $type): string
    {
        switch ($type) {
            case 'success':
                return 'Gelukt!';
            case 'error':
                return 'Fout!';
            case 'warning':
                return 'Waarschuwing!';
            case 'info':
                return 'Info!';
            default:
                return 'Info!';
        }
    }
}
