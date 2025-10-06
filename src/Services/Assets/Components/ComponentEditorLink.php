<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\LayoutBar;
use NotFound\Layout\Elements\LayoutBarButton;

class ComponentEditorLink extends AbstractComponent
{
    protected bool $useDefaultStorageMechanism = false;

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        $bar = new LayoutBar;
        $button = new LayoutBarButton('Edit '.$this->assetItem->name);
        $button->setLink('/app/editor/table/'.$this->assetModel->id.'/'.$this->assetItem->id);
        $bar->addBarButton($button);

        return $bar;
    }

    public function validate($newValue): bool
    {
        return true;
    }
}
