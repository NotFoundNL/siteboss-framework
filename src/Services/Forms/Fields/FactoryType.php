<?php

namespace NotFound\Framework\Services\Forms\Fields;

class FactoryType
{
    private $classNamespace = '\\NotFound\\Framework\\Services\\Forms\\Fields\\';

    private $classNamespaceCustom = '\\App\\Services\\Forms\\Fields\\';

    private $classPrefix = 'Type';

    public function getByType($type, $properties, $id): AbstractType
    {
        $classDefault = $this->classNamespace.$this->classPrefix.'Default';
        $className = $this->classNamespace.$this->classPrefix.ucfirst($type);
        $classNameCustom = $this->classNamespaceCustom.$this->classPrefix.ucfirst($type);

        if (class_exists($className)) {
            return new $className($type, $properties, $id);
        } elseif (class_exists($classNameCustom)) {
            return new $classNameCustom($type, $properties, $id);
        }

        return new $classDefault($type, $properties, $id);
    }
}
