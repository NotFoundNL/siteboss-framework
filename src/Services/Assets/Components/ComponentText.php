<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Http\File;
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

        //$inputText->setEndpoint($endPoint);
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
        if (isset($this->properties()->type) && $this->properties()->type == 'richtext') {
            if (isset($this->properties()->editorSettings) && trim($this->properties()->editorSettings) !== '') {
                $setting = EditorSetting::where('name', $this->properties()->editorSettings)->first();
            } else {
                $setting = EditorSetting::where('name', 'default')->first();
            }

            if (isset($setting->settings)) {
                return (object) ['editorSettings' => json_decode($setting->settings)];
            }
        }

        return (object) [];
    }

    public function asyncPostRequest()
    {
        if (! request()->hasFile('file') || request()->file('file')->isValid() === false) {
            return ['error', 'file invalid'];
        }

        // Create folder
        $folder = '/uploads/'.$this->assetModel->getIdentifier().'/'.$this->assetItem->internal.'/';
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

            new Driver()

        ))->read(new File(request()->file('file')));
        $image->scaleDown($width, null);

        $image->toJpeg()->save(
            Storage::path('public').$folder.$filename
        );

        return (object) [
            'result' => 'ok',
            'path' => '/assets/public/uploads/'.$this->assetModel->getIdentifier().'/'.$this->assetItem->internal.'/'.$filename,
        ];
    }
}
