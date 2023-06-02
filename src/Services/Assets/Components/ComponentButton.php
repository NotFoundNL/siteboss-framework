<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;

class ComponentButton extends AbstractComponent
{
    protected bool $useDefaultStorageMechanism = false;

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return null;
    }

    public function getTableOverviewContent(): LayoutTableColumn
    {
        if (isset($this->properties()->action)) {
            $newAction = str_replace('{id}', $this->recordId, $this->properties()->action);
            $payLoad = (object) ['action' => $newAction, 'name' => $this->assetItem->name];
        } else {
            $link = str_replace('{id}', $this->recordId, $this->properties()->link);
            $payLoad = (object) ['link' => $link, 'name' => $this->assetItem->name];
        }

        $payLoad->external = $this->properties()->external ?? false;

        return new LayoutTableColumn('export', $this->type, $payLoad);
    }

    public function validate($newValue): bool
    {
        // TODO: This is not an input and should
        //       probably not be bases on AbstractComponent.
        return true;
    }
}
