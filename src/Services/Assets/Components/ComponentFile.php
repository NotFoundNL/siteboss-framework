<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use NotFound\Framework\Services\ClamAV\ClamAV;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Inputs\LayoutInputFile;

class ComponentFile extends AbstractComponent
{
    protected bool $useDefaultStorageMechanism = true;

    private string $subFolderPrivate = '/files/';

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutInputFile($this->assetItem->internal, $this->assetItem->name);
    }

    protected function customProperties(): object
    {
        $url = $this->assetItem->properties->downloadUrl ?? '';

        return (object) ['downloadUrl' => str_replace('[id]', $this->recordId, $url)];
    }

    /**
     * Saves the file in the storage directory. If the file already exists overwrite it.
     *
     * @return void
     */
    public function save()
    {
        if (isset($this->newValue->delete) && $this->newValue->delete == true) {
            $this->delete();

            return;
        }

        $fileId = $this->newValue['files'][0] ?? null;
        if ($fileId === null) {
            // File not described in the files array
            return;
        }

        if (! request()->hasFile($fileId)) {
            return;
        }

        $this->makeParentDirectories();

        $filename = $this->recordId;

        $file = new File(request()->file($fileId));

        // THIS MUST BE OUTSIDE THE WEBROOT
        $file->move(Storage::path('private').'/'.$this->subFolderPrivate.$this->assetModel->getIdentifier().'/'.$this->assetItem->internal.'/', $filename);
    }

    private function delete()
    {
        // TODO: Implement delete() method.
    }

    public function validate($newValue): bool
    {
        $fileId = $this->newValue['files'][0] ?? null;
        if ($fileId === null) {
            if (isset($this->getCurrentValue()->name)) {
                return true;
            } else {
                return ! (isset($this->properties()->required) && $this->properties()->required == true);
            }
        }

        $path = request()->file($fileId);

        return ClamAV::uploadIsClean($path);
    }

    public function getTableOverviewContent(): LayoutTableColumn
    {
        $file = $this->getCurrentValue();

        return new LayoutTableColumn(isset($file->name) ? ($file->name) : '-', $this->type);
    }

    /**
     * returns the value saved to the database.
     *
     * @return  '{storage_path}/app/public/images/{asset-table}/{item-name}/'
     * @return void
     */
    public function getCurrentValue(): object
    {
        $value = json_decode($this->currentValue) ?? new \stdClass();

        if (isset($value->name)) {
            return (object) ['name' => $value->name, 'size' => $this->formatSize($value->size ?? 0)];
        }

        return (object) ['empty' => true];
    }

    private function formatSize($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = ['', 'K', 'M', 'G', 'T'];

        return round(pow(1024, $base - floor($base)), $precision).$suffixes[floor($base)];
    }

    /**
     * Get the value used in the default storage mechanism.
     * This is always a string. Use JSON or your own logic for other types of values.
     *
     * @return string
     */
    public function getValueForStorage(): ?string
    {
        // TODO:  Move this over from AutoLayout for reference
        $result = json_decode($this->currentValue ?? '{}') ?? new \stdClass();

        // Check if the file needs to be deleted
        if (isset($this->newValue['delete']) && $this->newValue['delete'] === true) {
            // TODO: Delete the file
            $result = [];
        } else {
            $fileId = $this->newValue['files'][0] ?? null;
            // No file was uploaded
            if (request()->file($fileId)) {
                // Update name
                $result = [
                    'name' => request()->file($fileId)->getClientOriginalName(),
                    'size' => request()->file($fileId)->getSize(),
                ];
            }
        }

        // File was added
        return json_encode((object) $result);
    }

    private function makeParentDirectories()
    {
        make_directories(
            Storage::path('private'),
            $this->subFolderPrivate.$this->assetModel->getIdentifier().'/'.$this->assetItem->internal.'/'
        );
    }
}
