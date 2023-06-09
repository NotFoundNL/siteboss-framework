<?php

namespace NotFound\Framework\Services\Forms\Fields;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use NotFound\Framework\Models\Forms\Filetype;
use NotFound\Framework\Services\ClamAV\ClamAV;

class TypeFile extends AbstractType
{
    // The files that are parsed and validated. Saved for later use so that if all the data from the posted form is okay we can save this.
    private $files = [];

    public function setValueFromPost(): void
    {
        $postValue = request()->file($this->getFieldId());
        $this->files = $postValue ?? [];

        if (! is_array($this->files)) {
            $this->files = [$this->files];
        }
    }

    public function setValueFromDb($value): void
    {
        $this->files = is_array($value) ? $value : [];
    }

    public function getReadableValue($langurl): string
    {
        return json_encode($this->files);
    }

    public function getOptions($options): array
    {
        foreach ($options as $jsonField) {
            if (isset($jsonField->internal) && $jsonField->internal == 'filetypes') {
                $jsonField->options = Filetype::getQuery()->get(['name AS value', 'display_name AS label'])->toArray();
            }
        }

        return $options;
    }

    public function getEmailHtml(): string
    {
        $label = $this->getLabel();



        // $baseUrl = sprintf('https://%s/admin/cms/api', $_SERVER['HTTP_HOST']);
        $value = '';

        foreach ($this->files as $file) {

            $value .= $file->getClientOriginalName();
        }
        // foreach ($this->validatorInfo->getFiles() as $file) {
        //     $value .= sprintf(
        //         "<a href='%s/download/%d/%d/%s' target='_blank'> %s </a>
        //     ",
        //         $baseUrl,
        //         $this->validatorInfo->getDataRecordId(),
        //         $this->id,
        //         $file->uuid,
        //         $file->filename
        //     );
        // }

        return "<p><strong>{$label}:</strong> {$value}</p>";
    }

    public function validate(): bool
    {
        if ($this->isRequired() && $this->files == []) {
            return false;
        }

        foreach ($this->files as $file) {
            if (! $this->filetypeIsOk($file->extension())) {
                return false;
            }

            if (! $this->filenameIsOk($file->getClientOriginalName())) {
                return false;
            }
        }

        return true;
    }

    public function getDataForSqlQuery($recordId): array
    {
        $index = 1;
        $filesToSave = [];

        $parentFolder = sprintf(
            '%d/%d/',
            $this->validatorInfo->getFormId(),
            $recordId,
        );

        if (! empty($this->files)) {
            $this->makeFolderInSaveLocation(Storage::disk('formbuilder')->path($parentFolder));
        }

        foreach ($this->files as $file) {
            /** @var UploadedFile $file */
            $loc = sprintf(
                '%d/%d/%d-%d',
                $this->validatorInfo->getFormId(),
                $recordId,
                $this->getFieldId(),
                $index
            );

            $url = sprintf(
                config('siteboss.api_prefix').'/forms/%d/fields/%d/records/%d/files/%d/',
                $this->validatorInfo->getFormId(),
                $this->getFieldId(),
                $recordId,
                $index
            );

            if (! ClamAV::moveFile($file->path(), $loc, 'formbuilder')) {
                abort(422);
            }

            $fileToSave = [
                'mime' => $file->getClientMimeType(),
                'filename' => $file->getClientOriginalName(),
                'url' => $url,
                'loc' => $loc,
                'uuid' => uniqid(),
            ];

            $filesToSave[] = $fileToSave;
            $this->validatorInfo->addFile((object) $fileToSave);

            $index++;
        }

        return [
            'type' => $this->type,
            'value' => $filesToSave,
        ];
    }

    public function rollback()
    {
        //TODO: Implement
    }

    private function filenameIsOk($filename): bool
    {
        // filenames may not contain more than 225 characters.
        if (mb_strlen($filename, 'UTF-8') <= 225) {
            return true;
        }

        return false;
    }

    private function filetypeIsOk($ext): bool
    {
        if (isset($this->properties) && isset($this->properties->filetypes)) {
            $filetype = $this->properties->{'filetypes'};

            $extArray = \NotFound\Framework\Services\Forms\MimetypeConverter::getExtension($filetype);
            if (! is_array($extArray)) {
                return true;
            }

            return in_array($ext, $extArray);
        }

        return true;
    }

    // Checks the size of the file
    private function FileIsSmallEnough()
    {
    }

    private function makeFolderInSaveLocation($folderName): void
    {
        // TODO: Deny access to the folder /data/forms/uploads/
        if (! is_dir($folderName)) {
            if (! mkdir($folderName, 0755, true)) {
                $mkdirErrorArray = error_get_last();
                exit('cant create directory \''.$folderName.'\': '.$mkdirErrorArray['message']);
            }
        }
    }
}
