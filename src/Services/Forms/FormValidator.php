<?php

namespace NotFound\Framework\Services\Forms;

use NotFound\Framework\Models\Forms\Property;

/**
 * The general file to validate a form.
 * The class gets a databaseForm which it iterates.Since we can only trust data which is currently in the database.
 * Cross check each field from the database against the posted form. If it doesn't exist add it anyway so that we can safely loop through the fields later.
 * Each field can have a custom Validator. This can be made in the validation folder if there is a need for more.
 * If it doesn't exists, just use a default one.
 *
 * In order to properly use this class first fire the prepare function, This function will prepare some variable if it is defined in the validator.
 * Afterward validate all the fields.
 * Then fire conclude to finish up if it is defined in the validators.
 */
class FormValidator
{
    // The data that is saved to the database.
    public ValidatorInfo $validatorInfo;

    // An array with all the fields(id) that contain an error.
    private $error;

    private ?\StdClass $hasValueObject = null;

    /**
     * @param  string  $databaseForm The form from the backend
     * TODO: This isn't used. Use laravel locale instead
     * @param  mixed  $langurl
     */
    public function __construct(
        private $databaseForm,
        private string $langurl,
        int $formId
    ) {
        $this->error = [];
        $this->validatorInfo = ValidatorInfo::getInstance();

        $this->validatorInfo->setFormId($formId);
        $this->populateValidatorList($this->databaseForm);
    }

    /**
     * Create objects of the data that needs to be validated.
     * Put these objects into a list so that we can use it when validating.
     *
     * @param  mixed  $form
     */
    private function populateValidatorList($form)
    {
        $factoryType = new Fields\FactoryType();

        foreach ($form as $field) {
            $field = (object) $field;
            // Combo is a new form,
            if ($field->type == 'Combination') {
                //Check if the trigger is valid, otherwise we dont need to check the combo.
                if (isset($field->trigger_field_id) && $field->trigger_field_id !== '') {
                    if (request($field->trigger_field_id) === $field->trigger_value) {
                        $this->populateValidatorList($field->fields);
                    }
                } else {
                    $this->populateValidatorList($field->fields);
                }

                continue;
            }

            if (! $this->propertyHasValue($field->type)) {
                continue;
            }

            $newField = $factoryType->getByType($field->type, $field->properties, $field->id);
            $newField->setValueFromPost();
            $this->validatorInfo->addValidator($newField);
        }
    }

    // Validate the fields. The validations populate the error.
    // You can later use the function 'hasErrors' to check if there were any errors while validating
    public function validate()
    {
        foreach ($this->validatorInfo->validators() as $validator) {
            $validateIsOk = $validator->validate();

            if (! $validateIsOk) {
                // TODO: show appropriate error
                $this->error[] = (object) ['field' => $validator->getFieldId(), 'name' => $validator->getLabel('nl')];
            }
        }
    }

    public function beforeSave()
    {
        foreach ($this->validatorInfo->validators() as $validator) {
            $validator->beforeSave();
        }
    }

    public function getDataForSqlQuery()
    {
        if ($this->hasErrors()) {
            if (env('APP_DEBUG')) {
                exit('Do not call getDataForSqlQuery if the form has errors.');
            }
            exit('Something went wrong. Please try again or contact support.');
        }

        $form = [];
        $recordId = $this->validatorInfo->getDataRecordId();

        foreach ($this->validatorInfo->validators() as $validator) {
            $form[$validator->getFieldId()] = $validator->getDataForSqlQuery($recordId);
        }

        return $form;
    }

    // If the validations are correct we can finish up some code.
    // For example move the downloaded files etc.
    // This only gets called when the query succeeded
    public function afterSave()
    {
        if ($this->hasErrors()) {
            if (env('APP_DEBUG')) {
                exit('Do not call conclude if the form has errors.');
            }
            exit('Something went wrong. Please try again or contact support.');
        }

        foreach ($this->validatorInfo->validators() as $validator) {
            $validator->afterSave();
        }
    }

    public function rollback()
    {
        foreach ($this->validatorInfo->validators() as $validator) {
            $validator->rollback();
        }
    }

    private function propertyHasValue($type)
    {
        if ($this->hasValueObject == null) {
            $this->hasValueObject = (object) [];

            $properties = Property::all();
            foreach ($properties as $property) {
                $this->hasValueObject->{$property->type} = $property->has_value;
            }
        }

        if (isset($this->hasValueObject->{$type}) && $this->hasValueObject->{$type} == '1') {
            return true;
        }

        return false;
    }

    public function hasErrors()
    {
        if (empty($this->error)) {
            return false;
        }

        return true;
    }

    public function jsonErrors()
    {
        return $this->error;
    }

    public function getPrimaryMail(): string
    {
        return $this->validatorInfo->getPrimaryEmail();
    }

    public function getValueForKey($fieldname)
    {
        $validators = $this->validatorInfo->validators();
        foreach ($validators as $validator) {
            if ($validator->getLabel() == $fieldname) {
                return $validator->getValue();
            }
        }

        return false;
    }

    public function setRecordId($recordId)
    {
        $this->validatorInfo->setDataRecordId($recordId);
    }
}
