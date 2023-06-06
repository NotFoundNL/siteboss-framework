<?php

namespace NotFound\Framework\Services\Forms;

final class ValidatorInfo
{
    private static $instance = null;

    private int $formId = 0;

    private int $dataRecordId = 0;

    private string $primaryEmail = '';

    private array $validatorList = [];

    private array $fileList = [];

    private function __construct()
    {
    }

    public static function getInstance(): ValidatorInfo
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function setFormId($formId)
    {
        $this->formId = intval($formId);
    }

    public function addValidator(Fields\AbstractType $validator): void
    {
        $this->validatorList[] = $validator;
        //@TODO Move this to the actual validator
        if ($validator->getType() == 'email' && $validator->propertyIsTrue('primary')) {
            $this->primaryEmail = $validator->getValue();
        }
    }

    public function validators(): array
    {
        return $this->validatorList;
    }

    public function setPrimaryEmail($email): void
    {
        $this->primaryEmail = $email;
    }

    public function getPrimaryEmail(): string
    {
        return $this->primaryEmail;
    }

    public function addFile($file): void
    {
        $this->fileList[] = $file;
    }

    public function getFiles(): array
    {
        return $this->fileList;
    }

    public function getFormId(): int
    {
        return $this->formId;
    }

    public function setDataRecordId($id): void
    {
        $this->dataRecordId = $id;
    }

    public function getDataRecordId(): int
    {
        return $this->dataRecordId;
    }
}
