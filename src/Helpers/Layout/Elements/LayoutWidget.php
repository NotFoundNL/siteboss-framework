<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTable;

class LayoutWidget extends AbstractContainer
{
    public function __construct(string $title, int $cols = 12)
    {
        parent::__construct(type: 'Widget');

        $this->properties->title = $title;
        $this->properties->cols = $cols;
        $this->properties->padding = true;
    }

    public function noPadding(): LayoutWidget
    {
        $this->properties->padding = false;

        return $this;
    }

    public function addMessage(LayoutMessage $message): LayoutWidget
    {
        $this->items->add($message->build());

        return $this;
    }

    public function addTable(LayoutTable $table): LayoutWidget
    {
        $this->items->add($table->build());

        return $this;
    }

    public function addForm(LayoutForm $form): LayoutWidget
    {
        $this->items->add($form->build());

        return $this;
    }

    public function addWidget(LayoutWidget $widget): LayoutWidget
    {
        $this->items->add($widget->build());

        return $this;
    }

    public function addSiteBoss(LayoutSiteBoss $text): LayoutWidget
    {
        $this->items->add($text->build());

        return $this;
    }

    public function addText(LayoutText $text): LayoutWidget
    {
        $this->items->add($text->build());

        return $this;
    }

    public function addTitle(LayoutTitle $title): LayoutWidget
    {
        $this->items->add($title->build());

        return $this;
    }

    public function addRow(LayoutRow $row): LayoutWidget
    {
        $this->items->add($row->build());

        return $this;
    }

    public function addBar(LayoutBar $bar): LayoutWidget
    {
        $this->items->add($bar->build());

        return $this;
    }

    public function addTabs(LayoutTabs $tabs): LayoutWidget
    {
        $this->items->add($tabs->build());

        return $this;
    }
}
