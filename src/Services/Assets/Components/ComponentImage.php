<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Inputs\LayoutInputImage;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Toast;
use stdClass;

class ComponentImage extends AbstractComponent
{
    protected bool $useDefaultStorageMechanism = true;

    private string $subFolderPublic = '/images/';

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutInputImage($this->assetItem->internal, $this->assetItem->name);
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

        foreach ($this->properties()->sizes as $dimensions) {
            $width = $dimensions->width;
            $height = $dimensions->height;

            $filename = $this->recordId.'_'.$dimensions->filename.'.jpg';

            // create new image instance
            $image = (new ImageManager(['driver' => 'imagick']))->make(new File(request()->file($fileId)->path()));

            if ($dimensions->height === '0') {
                $height = intval($image->height() / $image->width() * $width);
            }
            if ($dimensions->width === '0') {
                $width = intval($image->width() / $image->height() * $height);
            }

            if (isset($dimensions->cropType) && $dimensions->cropType === 'fitWithin') {
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            } else {
                $image->fit($width, $height);
            }

            $image->save(
                Storage::path('public').$this->relativePathToPublicDisk().$filename
            );
            $image->save(
                Storage::path('public').$this->relativePathToPublicDisk().$filename.'.webp'
            );
        }
    }

    public function getDisplayValue()
    {
        return $this->getCurrentValue();
    }

    /**
     * returns the url for the first image, or an empty object if no image is available.
     *
     * @return  '{storage_path}/app/public/images/{asset-table}/{item-name}/'
     * @return void
     */
    public function getCurrentValue(): object
    {
        $value = json_decode($this->currentValue) ?? new stdClass();

        $values = new stdClass();

        if (isset($value->uploaded) && $value->uploaded === true && isset($this->properties()->sizes[0])) {
            // Set the default url

            $prefix = '';
            if (config('siteboss.cache_prefix') === true && isset($this->assetItem->updated_at)) {
                $prefix = '/'.$this->assetItem->updated_at->timestamp;
            }
            $name = $this->properties()->sizes[0]->filename;
            $filename = $this->recordId.'_'.$name.'.jpg';
            $values->url = $prefix.'/assets/public'.$this->relativePathToPublicDisk().$filename;

            // Set the url for each size
            foreach ($this->properties()->sizes as $size) {
                $name = $size->filename;
                $filename = $this->recordId.'_'.$name.'.jpg';
                $values->sizes[$name] = (object) [
                    'url' => $prefix.'/assets/public'.$this->relativePathToPublicDisk().$filename,
                    'width' => $size->width,
                    'height' => $size->height,
                    'cropType' => (isset($size->cropType)) ? $size->cropType : 'constrain',
                ];
            }
        }

        return $values;
    }

    private function deleteFiles()
    {
        foreach ($this->properties()->sizes as $dimensions) {
            $filename = $this->recordId.'_'.$dimensions->filename.'.jpg';
            if (file_exists(Storage::path('public').$this->relativePathToPublicDisk().$filename)) {
                unlink(
                    Storage::path('public').$this->relativePathToPublicDisk().$filename
                );
            }

            if (file_exists(Storage::path('public').$this->relativePathToPublicDisk().$filename.'.webp')) {
                unlink(
                    Storage::path('public').$this->relativePathToPublicDisk().$filename.'.webp'
                );
            }
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
        $result = json_decode($this->currentValue) ?? new stdClass();

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

    private function getStorageJSON()
    {
        $prefix = '';
        if (config('siteboss.cache_prefix') === true && isset($this->assetItem->updated_at)) {
            $prefix = '/'.$this->assetItem->updated_at->timestamp;
        }

        $values = new stdClass();

        foreach ($this->properties()->sizes as $size) {
            $name = $size->filename;
            $filename = $this->recordId.'_'.$name.'.jpg';
            $values->$name = (object) [
                'url' => $prefix.'/assets/public'.$this->relativePathToPublicDisk().$filename,
                'width' => $size->width,
                'height' => $size->height,
            ];
        }

        return $values;
    }

    /**
     * Returns the full path of the new image file without the filename.
     */
    private function relativePathToPublicDisk(): string
    {
        $imagePath = Str::lower($this->assetModel->getIdentifier().'/'.$this->assetItem->internal.'/');

        return $this->subFolderPublic.$imagePath;
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
            $this->subFolderPublic.$this->assetModel->getIdentifier().'/'.$this->assetItem->internal.'/'
        );

        // if app is running in debug mode, throw an error if the directories could not be created
        if (! $createDirs) {
            if (env('APP_DEBUG') === true) {

                exit('Could not create directory '.Storage::path('public').$this->subFolderPublic.$this->assetModel->getIdentifier().'/'.$this->assetItem->internal.'/');
            } else {
                Log::error('Could not create directory '.Storage::path('public').$this->subFolderPublic.$this->assetModel->getIdentifier().'/'.$this->assetItem->internal.'/');
            }
        }

        return $createDirs;

    }
}
