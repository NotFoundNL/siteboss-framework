<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Framework\Services\Assets\Enums\AssetType;
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
            $newAction = preg_replace_callback('/{([^}]+)}/', [$this, 'replaceValue'], $this->properties()->action);
            $payLoad = (object) ['action' => $newAction, 'name' => $this->assetItem->name];
        } else {
            $newLink = preg_replace_callback('/{([^}]+)}/', [$this, 'replaceValue'], $this->properties()->link);
            $payLoad = (object) ['link' => $newLink, 'name' => $this->assetItem->name];
        }
        $payLoad->external = $this->properties()->external ?? false;

        return new LayoutTableColumn('export', $this->type, $payLoad);
    }

    private function replaceValue($matches): string
    {
        $value = $matches[1];
        // Prevent database calls for ID only
        if ($value === 'id') {
            return $this->recordId;
        }
        if ($this->assetType === AssetType::TABLE) {
            $siteTableRow = $this->assetModel->getSiteTableRowByRecordId($this->recordId);
            if (isset($siteTableRow->$value)) {
                return urlencode($siteTableRow->$value);
            }
        }

        return $value;
    }

    public function validate($newValue): bool
    {
        // TODO: This is not an input and should
        //       probably not be bases on AbstractComponent.
        return true;
    }
}
