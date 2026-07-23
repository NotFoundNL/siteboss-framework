<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Support\Collection;
use NotFound\Framework\Helpers\Layout\Elements\AbstractLayout;
use NotFound\Framework\Helpers\Layout\Elements\LayoutTitle;

class ComponentHeader extends AbstractComponent
{
    protected bool $useDefaultStorageMechanism = false;

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutTitle($this->assetItem->name);
    }

    public function validate($newValue): bool
    {
        return true;
    }

    public function getCloneValue(Collection $components): mixed
    {
        return null;
    }

    public function purge(): bool
    {
        // Only shows a header in the form, there is nothing to remove.
        return true;
    }
}
