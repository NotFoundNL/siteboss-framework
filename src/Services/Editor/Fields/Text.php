<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Models\EditorSetting;
use NotFound\Framework\Services\Editor\Properties;

class Text extends Properties
{
    public function description(): string
    {
        return 'Text';
    }

    public function properties(): void
    {
        $this->allOverviewOptions();
        $this->localize();
        $this->required();
        $this->addCheckbox('disabled', 'Disable editing');
        $this->addDropDown('type', 'Type', [
            (object) ['value' => 'text', 'label' => 'Text'],
            (object) ['value' => 'multiline', 'label' => 'Multiline text'],
            (object) ['value' => 'richtext', 'label' => 'Rich Text'],
        ]);
        $this->addNumber('maxlength', 'Maximum length');
    }

    public function serverProperties(): void
    {
        $tables = EditorSetting::all();
        $options = [];
        foreach ($tables as $table) {
            $options[] = (object) ['value' => $table->name, 'label' => $table->name];
        }
        $this->addDropDown('editorSettings', 'Editor settings (for rich text editor)', $options);
        // BUG: TODO: editModel should just be a property, 
        //            not a server property
        $this->addCheckbox('editModal', 'Edit texts in popup (required for ContentBlock/Childtable)');
        $this->addTitle('Validation');

        $this->addDropDown('regExTemplate', 'Built in validations', [
            (object) ['value' => '', 'label' => 'None'],
            (object) ['value' => 'email', 'label' => 'E-mail'],
            (object) ['value' => 'custom', 'label' => 'Custom (define below)'],
        ]);

        $this->addText('regEx', 'Regular expression (choose custom above)');
        $this->noIndex();
    }

    public function rename(): array
    {
        return ['texttype' => 'type'];
    }

    public function checkColumnType(?\Doctrine\DBAL\Types\Type $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }
        if (! in_array($type->getName(), ['string', 'text'])) {
            return 'TYPE ERROR: '.$type->getName().' is not a valid type for a text field';
        }

        return '';
    }
}
