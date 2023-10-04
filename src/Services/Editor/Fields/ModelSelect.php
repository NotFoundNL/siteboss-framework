<?php

namespace NotFound\Framework\Services\Editor\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use NotFound\Framework\Services\Editor\Properties;

class ModelSelect extends Properties
{
    public function description(): string
    {
        return 'Model dropdown';
    }

    public function properties(): void
    {
        $this->allOverviewOptions();

        $options = $this->getModels();

        $this->addText('selectedModel', 'Custom model path', true, default: 'App\\Models\\');
    }

    public function serverProperties(): void
    {
        $this->addText('methodName', 'Method', true);
    }

    public function checkColumnType(?\Doctrine\DBAL\Types\Type $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }
        if (! in_array($type->getName(), ['string', 'integer'])) {
            return 'TYPE ERROR: '.$type->getName().' is not a valid type for a table select field';
        }

        return '';
    }

    public function getModels(): array
    {
        $paths = [app_path('Models')];
        $models = collect();
        foreach ($paths as $p) {
            $models = $models->concat(collect(File::allFiles($p))->map(function ($item) {
                $path = $item->getRelativePathName();
                $key = strtr(substr($path, 0, strrpos($path, '.')), '/', '\\');

                $class = 'App\\Models\\';
                $class .= $key;

                return (object) ['value' => $class, 'label' => $key];
            })->filter(function ($object) {
                $valid = false;

                if (class_exists($object->value)) {
                    $reflection = new \ReflectionClass($object->value);
                    $valid = $reflection->isSubclassOf(Model::class) &&
                        ! $reflection->isAbstract();
                }

                return $valid;
            }));
        }

        return $models->values()->toArray();
    }
}
