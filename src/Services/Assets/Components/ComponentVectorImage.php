<?php

// TODO: enable strict_types
// declare(strict_types=1);

namespace NotFound\Framework\Services\Assets\Components;

use DateTime;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use NotFound\Framework\Models\Menu;
use NotFound\Framework\Services\Assets\Enums\AssetType;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Inputs\LayoutInputVectorImage;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Toast;
use enshrined\svgSanitize\Sanitizer;
use stdClass;

class ComponentVectorImage extends AbstractComponent
{
    protected bool $useDefaultStorageMechanism = true;

    private string $subFolderPublic = '/images/';

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutInputVectorImage($this->assetItem->internal, $this->assetItem->name);
    }

    public function validate($newValue): bool
    {
        // BUG: client predicts a file when there is none
        // fix that and then improve this validation

        $fileId = $this->newValue['files'][0] ?? null;

        if ($fileId == null) {
            return true;
        }

        if (request()->file($fileId) == null) {
            return false;
        }

        if (! request()->file($fileId)->isValid()) {
            exit(request()->file($fileId)->getErrorMessage());

            return false;
        }

        return $this->makeParentDirectories();
    }

    /**
     * Saves the file in the storage directory. If the file already exists overwrite it.
     *
     * @return void
     */
    public function save()
    {
        $fileId = $this->newValue['files'][0] ?? null;
        if ($fileId === null) {
            // File not described in the files array
            return;
        }

        if (request()->hasFile($fileId)) {
            $file = request()->file($fileId);
            if (! $file->isValid()) {
                $errorResponse = new LayoutResponse();

                $errorResponse->addAction(new Toast($file->getErrorMessage(), 'error'));

                return $errorResponse->build();
            }
        }

        $sanitizer = new Sanitizer();
        $cleanSVG = $sanitizer->sanitize($file);

        if (! $cleanSVG) {
            $errorResponse = new LayoutResponse();

            $errorResponse->addAction(new Toast('SVG could not be sanitized', 'error'));

            return $errorResponse->build();
        }

        $filename = $this->recordId . '.svg';
        Storage::put('public' . $this->relativePathToPublicDisk() . $filename, $cleanSVG);
    }

    public function getDisplayValue()
    {
        return $this->getCurrentValue();
    }

    /**
     * returns the url for the first image, or an empty object if no image is available.
     *
     * @return '{storage_path}/app/public/images/{asset-table}/{item-name}/'
     * @return void
     */
    public function getCurrentValue(): object
    {
        $value = json_decode($this->currentValue ?? '{}') ?? new stdClass();

        $values = new stdClass();

        if (isset($value->uploaded) && $value->uploaded === true) {

            $prefix = '';
            $updatedAt = $this->updatedAt();

            if (config('app.asset_url') !== null) {
                $prefix = config('app.asset_url');
                if (config('siteboss.cache_prefix') === true) {
                    $prefix .= $updatedAt;
                }
            }
            $prefix .= '/assets/public';

            // Set the default url
            $values->url = $prefix . $this->relativePathToPublicDisk() . $this->recordId . '.svg';
        }

        return $values;
    }

    private function updatedAt(): string
    {
        $updatedAt = '';
        if ($this->assetType === AssetType::TABLE && $this->recordId) {
            $siteTableRow = $this->assetModel->getSiteTableRowByRecordId($this->recordId);
            if (isset($siteTableRow->updated_at)) {
                $date = new DateTime($siteTableRow->updated_at ?? '2020-01-01 00:00:00');
                $updatedAt = $date->getTimestamp();
            }
        } else {
            $menu = Menu::find($this->recordId);
            if ($menu && $menu->updated_at) {
                $updatedAt = $menu->updated_at->getTimestamp();
            }
        }

        return (string) $updatedAt;
    }

    private function deleteFiles(): void
    {
        $fileName = Storage::path('public' . $this->relativePathToPublicDisk() . $this->recordId . '.svg');
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }

    public function getTableOverviewContent(): LayoutTableColumn
    {
        $currentValue = $this->getCurrentValue();
        if (isset($currentValue->url)) {
            return new LayoutTableColumn($currentValue->url, 'Image');
        }
        $currentValue = json_encode($currentValue);

        return new LayoutTableColumn('-', 'Text');
    }

    /**
     * Get the value used in the default storage mechanism.
     * This is always a string. Use JSON or your own logic for other types of values.
     */
    public function getValueForStorage(): ?string
    {
        $result = json_decode($this->currentValue ?? '{}') ?? new stdClass();

        // Check for a current value
        if (isset($result->uploaded) && $result->uploaded === true) {
            // Check if the file needs to be deleted
            // BUG: WHY IS THIS NOT AN OBJECT????
            if (isset($this->newValue['delete']) && $this->newValue['delete'] === true) {
                $this->deleteFiles();

                $result = new stdClass();
            }
        }

        if (count($this->newValue['files']) > 0) {
            // File was uploaded
            $result = ['uploaded' => true];
        }

        // File was added
        return json_encode($result);
    }

    /**
     * Returns the full path of the new image file without the filename.
     */
    private function relativePathToPublicDisk(): string
    {
        $imagePath = Str::lower($this->assetModel->getIdentifier() . '/' . $this->assetItem->internal . '/');

        return $this->subFolderPublic . $imagePath;
    }

    /**
     * makeParentDirectories
     *
     * Creates the parent directories if they don't exist.
     *
     * @return bool True if successful, false if not.
     */
    private function makeParentDirectories(): bool
    {
        $createDirs = make_directories(
            Storage::path('public'),
            $this->subFolderPublic . $this->assetModel->getIdentifier() . '/' . $this->assetItem->internal . '/'
        );

        // if app is running in debug mode, throw an error if the directories could not be created
        if (! $createDirs) {
            if (env('APP_DEBUG') === true) {

                exit('Could not create directory ' . Storage::path('public') . $this->subFolderPublic . $this->assetModel->getIdentifier() . '/' . $this->assetItem->internal . '/');
            } else {
                Log::error('Could not create directory ' . Storage::path('public') . $this->subFolderPublic . $this->assetModel->getIdentifier() . '/' . $this->assetItem->internal . '/');
            }
        }

        return $createDirs;
    }
}
