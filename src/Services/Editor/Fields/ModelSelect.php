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
        $this->overview();
        $this->sortable();
        $this->required();

        $options = $this->getModels();

        $this->addDropDown('selectedModel', 'Select the model', $options);
    }

    public function serverProperties(): void
    {
        $this->addText('foreignDisplay', 'Function', true, default: 'cmsDisplay');
    }

    protected function rename(): array
    {
        return [
            'table' => 'selectedModel',
            'foreigndisplay' => 'foreignDisplay',
        ];
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
        $paths = [app_path('Models'), base_path('vendor/notfoundnl/e-learning/src/Models')];
        $models = collect();
        foreach ($paths as $p) {
            $models = $models->concat(collect(File::allFiles($p))->map(function ($item) use ($p) {
                $path = $item->getRelativePathName();
                $key = strtr(substr($path, 0, strrpos($path, '.')), '/', '\\');

                $class = ($p == base_path('vendor/notfoundnl/e-learning/src/Models')) ? 'NotFound\\ELearning\\Models\\' : 'App\\Models\\';
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
