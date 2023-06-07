<?php

namespace NotFound\Framework\Services\Forms\Fields;

function startsWithAndStrip(&$className, $needle, $stripClassName = true): bool
{
    $needleLength = strlen($needle);

    if (substr($className, 0, $needleLength) === $needle) {
        if ($stripClassName) {
            $className = substr($className, $needleLength);
        }

        return true;
    }

    return false;
}

/*
 * The fields are saved in a custom path, not compliying to psr-4
 * If the class name is something like: "App/TypeText"
 * The class then resides in "app/text/text.php"
 */
function customclassPath(string $className): string
{
    $shortClassName = strtolower(str_replace('Type', '', $className));

    return $shortClassName.'/'.$shortClassName.'.php';
}

spl_autoload_register(function ($className) {
    $defaultFieldsPath = app_path().'/Services/Forms/Fields/';

    // TODO: BUG: This is not working
    // Quick fix for now, but we need to understand what this does
    // and preferably have it adhere to PSR-4.
    $sitebossFieldsPath = app_path().'app/Services/Forms/Fields/';


    if (startsWithAndStrip($className, 'NotFound\\Framework\\Services\\Forms\\Fields\\') || startsWithAndStrip($className, 'App\\Services\\Forms\\Fields\\')) {
        // Check if siteboss has a field type
        $completeClassName = $sitebossFieldsPath.customclassPath($className);
        if (file_exists($completeClassName)) {
            require_once $completeClassName;

            return;
        }

        // If none found get the default one.
        $classFile = $defaultFieldsPath.customclassPath($className);

        if (file_exists($classFile)) {
            require_once $classFile;
        }
    }
});

class FactoryType
{
    private $classNamespace = '\\NotFound\\Framework\\Services\\Forms\\Fields\\';

    private $classPrefix = 'Type';

    public function getByType($type, $properties, $id): AbstractType
    {
        $className = $this->classNamespace.$this->classPrefix.ucfirst($type);

        if (class_exists($className) === false) {
            $className = $this->classNamespace.$this->classPrefix.'Default';
        }

        return new $className($type, $properties, $id);
    }
}