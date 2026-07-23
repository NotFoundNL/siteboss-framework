<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Http\File;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use NotFound\Framework\Models\EditorSetting;
use NotFound\Framework\Models\FileUpload;
use NotFound\Framework\Services\Assets\Enums\AssetType;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Inputs\LayoutInputText;

class ComponentText extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        $inputText = new LayoutInputText($this->assetItem->internal, $this->assetItem->name);

        if ($this->assetType == AssetType::PAGE) {
            $endPoint = sprintf('app/page%s/%d/editor/nl/%s', $this->assetModel->url, $this->recordId, $this->assetItem->internal);
        } else {
            $endPoint = sprintf('table/%s/%d/nl/%s', $this->assetModel->url, $this->recordId, $this->assetItem->internal);
        }

        $inputText->setEndpoint($endPoint);

        $regEx = $this->getRegEx();
        if (! is_null($regEx)) {
            $inputText->setRegEx($regEx);
        }

        return $inputText;
    }

    private function getRegEx(): ?string
    {
        if (isset($this->assetItem->server_properties->regExTemplate) && $this->assetItem->server_properties->regExTemplate !== '') {
            switch ($this->assetItem->server_properties->regExTemplate) {
                case 'none': return null;
                case 'email': return '^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$';
                case 'custom': return $this->assetItem->server_properties->regEx ?? '';
            }
        }

        // $inputText->setEndpoint($endPoint);
        return null;
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }

    /**
     * Get custom properties for the component
     */
    protected function customProperties(): object
    {
        // BUG: TODO: editModal should just be a property,
        //            not a server property
        $customProperties = [];
        if (isset($this->properties()->type) && $this->properties()->type == 'richtext') {
            if (isset($this->properties()->editorSettings) && trim($this->properties()->editorSettings) !== '') {
                $setting = EditorSetting::where('name', $this->properties()->editorSettings)->first();
            } else {
                $setting = EditorSetting::where('name', 'default')->first();
            }

            if (isset($setting->settings)) {
                $customProperties['editorSettings'] = json_decode($setting->settings);
            }
        }

        return (object) $customProperties;
    }

    public function asyncPostRequest()
    {
        if (! request()->hasFile('file') || request()->file('file')->isValid() === false) {
            return ['error', 'file invalid'];
        }

        // Create folder
        $folder = $this->uploadFolder();
        if (! make_directories(Storage::path('public'), $folder)) {
            return (object) ['result' => 'error'];
        }

        $id = FileUpload::insertGetId([
            'container_id' => $this->recordId,
            'container_type' => $this->assetModel->getIdentifier(),
            'filename' => '.webp',
            'mimetype' => 'image/webp',
        ]);
        $filename = $id.'.webp';
        $width = 1200;

        // create new image instance
        $image = (new ImageManager(
            new Driver
        ))->read(new File(request()->file('file')));
        $image->scaleDown($width, null);

        $image->toJpeg()->save(
            Storage::path('public').$folder.$filename
        );

        return (object) [
            'result' => 'ok',
            'path' => '/assets/public'.$folder.$filename,
        ];
    }

    /**
     * The duplicate uses the same text, so it points to the same images. The
     * files are not copied, the duplicate gets its own row that refers to the
     * upload of the original.
     */
    public function clone(int $newRecordId): bool
    {
        $copies = [];
        foreach ($this->uploads() as $upload) {
            $copies[] = [
                'container_id' => $newRecordId,
                'container_type' => $this->assetModel->getIdentifier(),
                'source_upload_id' => $this->fileIdOf($upload),
                'filename' => $upload->filename,
                'mimetype' => $upload->mimetype,
            ];
        }

        if ($copies === []) {
            return true;
        }

        return FileUpload::insert($copies);
    }

    /**
     * Removes the images that were uploaded through the rich text editor.
     *
     * A file is shared with the records this one was duplicated from or to, so
     * it is only removed once the last record that refers to it is purged.
     */
    public function purge(): bool
    {
        $folder = Storage::path('public').$this->uploadFolder();

        $succeeded = true;
        foreach ($this->uploads() as $upload) {
            $fileId = $this->fileIdOf($upload);
            $path = $folder.$fileId.$upload->filename;

            $upload->forceDelete();

            if ($this->isUsed($fileId) || ! file_exists($path)) {
                continue;
            }

            if (! unlink($path)) {
                $succeeded = false;
            }
        }

        return $succeeded;
    }

    /**
     * The uploads of this record that belong to this field.
     *
     * The rows are stored per record instead of per field, so they are shared
     * with the other text fields of the record. The file tells us which field
     * an upload belongs to, as every field has its own folder.
     */
    private function uploads(): Collection
    {
        $folder = Storage::path('public').$this->uploadFolder();

        // cms_uploads has no deleted_at column while the model does use soft
        // deletes, so the scope is skipped and the rows are handled for real.
        return FileUpload::withoutGlobalScopes()
            ->where('container_id', $this->recordId)
            ->where('container_type', $this->assetModel->getIdentifier())
            ->get()
            ->filter(fn (FileUpload $upload) => file_exists($folder.$this->fileIdOf($upload).$upload->filename));
    }

    /**
     * Whether any record still refers to the given file.
     */
    private function isUsed(int $fileId): bool
    {
        return FileUpload::withoutGlobalScopes()
            ->where(function ($query) use ($fileId) {
                $query->where('id', $fileId)->orWhere('source_upload_id', $fileId);
            })
            ->exists();
    }

    /**
     * The file of an upload is named after the row it was uploaded with, which
     * is the row itself unless the record is a duplicate of another record.
     */
    private function fileIdOf(FileUpload $upload): int
    {
        return $upload->source_upload_id ?? $upload->id;
    }

    private function uploadFolder(): string
    {
        return '/uploads/'.$this->assetModel->getIdentifier().'/'.$this->assetItem->internal.'/';
    }
}
