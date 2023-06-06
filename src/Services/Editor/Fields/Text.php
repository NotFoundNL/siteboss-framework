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

        $this->addTitle('Validation');

        $this->addDropDown('regExTemplate', 'Built in validations', [
            (object) ['value' => '', 'label' => 'None'],
            (object) ['value' => 'email', 'label' => 'E-mail'],
            (object) ['value' => 'custom', 'label' => 'Custom (define below)'],
        ]);

        $this->addText('regEx', 'Regular expression (choose custom above)');

    }

    public function rename(): array
    {
        return ['texttype' => 'type'];
    }
}
