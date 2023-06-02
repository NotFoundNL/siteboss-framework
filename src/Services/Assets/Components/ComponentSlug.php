<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Support\Facades\DB;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Inputs\LayoutInputSlug;

class ComponentSlug extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutInputSlug($this->assetItem->internal, $this->assetItem->name);
    }

    /**
     * validate
     *
     * Check if the slug is not empty.
     *
     * @param  mixed  $newValue
     */
    public function validate($newValue): bool
    {
        // It's not possible to check at this stage
        return true;
    }

    /**
     * beforeSave
     *
     * Update the slug if currently empty.
     * The updated value will be based on the source field
     * set in the server properties.
     *
     * @return void
     */
    public function beforeSave()
    {
        if (trim($this->newValue) == '') {
            $sourceInput = $this->assetItem->server_properties->source ?? '';
            $sourceValue = $this->assetService->getComponents()->get($sourceInput)->newValue;
            $newValue = preg_replace('/ /', '-', preg_replace('/[ ]{2,100}/', ' ', trim(preg_replace('/[^a-z0-9]/', ' ', strtolower($sourceValue)))));
        } else {
            $newValue = $this->newValue;
        }

        // No need to check current value
        if ($newValue == $this->currentValue) {
            $this->newValue = $this->currentValue;

            return;
        }

        // Prevent duplicate slugs
        $columnName = $this->assetItem->internal;
        $highestSlug = DB::table($this->assetModel->table)->where($columnName, '=', $newValue)->orWhere($columnName, 'regexp', $newValue.'\-[0-9]+')->orderBy($columnName, 'DESC')->limit(1)->get();

        if (count($highestSlug) > 0) {
            $highestSlug = $highestSlug[0]->$columnName;
            $highestSlug = explode('-', $highestSlug);
            $highestSlug = end($highestSlug);

            if (is_numeric($highestSlug)) {
                $newValue .= '-'.($highestSlug + 1);
            } else {
                $newValue .= '-1';
            }
        }
        $this->newValue = $newValue;
    }
}
