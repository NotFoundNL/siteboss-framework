<?php

namespace NotFound\Framework\Services\Forms\Fields;

abstract class AbstractType
{
    protected $id = null;

    protected $properties = null;

    protected $type = null;

    protected \NotFound\Framework\Services\Forms\ValidatorInfo $validatorInfo;

    private $value = null;

    public function __construct($type, $fieldProperties, $fieldId)
    {
        $this->id = (int) $fieldId;
        $this->properties = $fieldProperties;
        $this->type = $type;
        $this->validatorInfo = \NotFound\Framework\Services\Forms\ValidatorInfo::getInstance();
    }

    /**
     * An protected function so that the value can't be set from anywhere.
     *
     * @param    $value  The value that needs to be set
     */
    protected function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * Whenever an form is submitted by the end user, this function is called. You can then either use the default
     * functionallity or you can override it. This can then be used to save multiple data.
     */
    public function setValueFromPost(): void
    {
        if (request($this->id) !== null) {
            $this->setValue(request($this->id) ?? null);
        }
    }

    /**
     * If you used 'setValueFromPost()' you need to override this method too. Since the data saved to the database won't be the same
     * for every field.
     *
     * @param  mixed  $value
     */
    public function setValueFromDb($value): void
    {
        $this->setValue($value);
    }

    /**
     * Not every field has a string saved into the database, however we need a string returned so the user can see what is saved into
     * the database.
     *
     * @param  mixed  $langurl
     */
    abstract public function getReadableValue($langurl): string;

    /**
     * Validate what the user has submitted to the form.
     */
    abstract public function validate(): bool;

    /**
     * All the fields are valid. Prepare the data before the field is saved.
     */
    public function beforeSave(): void
    {
    }

    /**
     * We need the data that is saved to the database.
     *
     * @param  mixed  $recordId
     * @return array must return assoc array with "type" and "value"
     */
    public function getDataForSqlQuery($recordId): array
    {
        return [
            'type' => $this->type,
            'value' => $this->getValue(),
        ];
    }

    /**
     * Everything is called correctly and the data is saved to the database. We are able to finish it up here.
     */
    public function afterSave()
    {
    }

    /**
     * The query failed. If a field has made changes to the field system etc we can clean it up here.
     */
    public function rollback()
    {
    }

    /**
     * The dataviewer requires to know what the headers are for the columns. If the data saved by this field is different than
     * normal we might need more columns. Since the param is a reference we can change how we want it.
     *
     * NOTE: If you make more headers, be sure to override 'fillTableForDataViewer'.
     *
     * @param  array  $headerArray  array that will create the table header
     * @param  mixed  $outputFilter
     */
    public function createHeadersForDataViewer(&$headerArray, $outputFilter)
    {
        //TODO: lang
        //$headerArray[] = $this->properties->label->{\nfapi\formbuilder\utils\LanguageManager::getDefaultLanguage()};
        $headerArray[] = $this->properties->label->{'nl'};
    }

    /**
     * When you've overwritten 'createHeadersForDataViewer' also override this function. make sure to add the same amount of columns.
     *
     * NOTE: If you make more columns, be sure to also override 'createHeadersForDataViewer'.
     *
     * @param  array  $tableRow  array that will fill the table for CSV export etc
     * @param  string  $outputFilter  every output is different, filter it
     */
    public function fillTableForDataViewer(&$tableRow, $outputFilter)
    {
        if ($outputFilter == 'csv') {
            $tableRow[] = $this->getReadableValue('nl');
        }

        if ($outputFilter == 'table') {
            $tableRow[] = [
                'type' => $this->getType(),
                'fieldId' => $this->getFieldId(),
                'value' => $this->getReadableValue('nl'),
                'editable' => $this->isEditable(),
            ];
        }
    }

    /**
     * Used in the frontend to make a field editable.
     * Suppored types: *empty*, 'string', 'checkbox', 'radio'
     */
    public function isEditable(): object
    {
        return (object) [
            'edit' => false,
            /* 'type' => 'string', */
            /*"
            * "options" => "etc"
            */
        ];
    }

    /**
     * Some options need dynamic data for the frontend to display.
     * options: in the formbuilder where you can set what width the field has for example
     * You can do that here.
     *
     * @param  mixed  $options
     */
    public function getOptions(array $options): array
    {
        return $options;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue()
    {
        if ($this->value === null) {
            return '';
        }

        return $this->value;
    }

    public function getFieldId(): int
    {
        return $this->id;
    }

    public function getLabel(string $language = 'nl'): string
    {
        //TODO: lang
        if (! isset($this->properties->label)) {
            return '';
        }

        if (! isset($this->properties->label->{$language})) {
            return '';
        }

        return $this->properties->label->{$language};
    }

    public function getEmailHtml(): string
    {
        $label = $this->getLabel();
        //TODO: lang
        $value = $this->getReadableValue('nl');

        return "<p><strong>{$label}:</strong> {$value}</p>";
    }

    protected function isRequired(): bool
    {
        if (! isset($this->properties)) {
            return false;
        }

        if (! isset($this->properties->required)) {
            return false;
        }

        return $this->properties->required;
    }

    public function propertyIsTrue($propertyName): bool
    {
        if (! isset($this->properties)) {
            return false;
        }

        if (! isset($this->properties->{$propertyName})) {
            return false;
        }

        if ($this->properties->{$propertyName} == '1') {
            return true;
        }

        return false;
    }

    protected function emptyValue(): bool
    {
        if ($this->value == '' || $this->value == null || $this->value == []) {
            return true;
        }

        return false;
    }
}
