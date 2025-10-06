<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Support\Facades\App;
use Nette\NotImplementedException;
use NotFound\Framework\Models\AssetItem;
use NotFound\Framework\Models\AssetModel;
use NotFound\Framework\Services\Assets\AbstractAssetService;
use NotFound\Framework\Services\Assets\Enums\AssetType;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Layout\Inputs\AbstractInput;
use stdClass;

abstract class AbstractComponent
{
    /*
     * Some fields aren't using the default storage mechanism. These component's are disabled for the
     * Query update. beforeSave() save() afterSave() will still be called.
     */
    protected bool $useDefaultStorageMechanism = true;

    public readonly ?AssetModel $assetModel;

    public readonly ?AssetType $assetType;

    protected mixed $currentValue = null;

    protected mixed $newValue = null;

    protected ?int $recordId = null;

    /*
     * The type as a string. This will be the string used to show this component in the
     * frontend.
     */
    protected string $type;

    public function __construct(
        public readonly ?AbstractAssetService $assetService,
        public readonly ?AssetItem $assetItem,
    ) {
        $this->assetModel = $assetService->getAssetModel();
        $this->assetType = $assetService->getType();
        $this->type = 'Input'.ucfirst($assetItem->type);
    }

    abstract protected function getAutoLayoutClass(): ?AbstractLayout;

    abstract public function validate($newValue): bool;

    /**
     * Gets the content for the header of the overview table.
     */
    public function getOverviewHeaderContent(): LayoutTableHeader
    {
        return new LayoutTableHeader(
            $this->assetItem->name,
            $this->assetItem->internal,
            $this->type,
            $this->properties()
        );
    }

    /**
     * Gets the content for the table overview, this is usually a string.
     */
    public function getTableOverviewContent(): LayoutTableColumn
    {
        return new LayoutTableColumn($this->getCurrentValue(), $this->type);
    }

    /**
     * Get custom properties for the component
     */
    protected function customProperties(): object
    {
        return new stdClass;
    }

    public function buildAutoLayoutClass()
    {
        $layoutClass = $this->getAutoLayoutClass();
        if (! $layoutClass) {
            return [];
        }

        if ($layoutClass instanceof AbstractInput && $this->getCurrentValue() != null) {
            $layoutClass->setValue($this->getCurrentValue());
        }

        $properties = $this->assetItem->properties;
        if ($this->isLocalized()) {
            $properties->localize == 1;
        }
        $layoutClass->properties =
            (object) array_merge(
                (array) $properties,
                (array) $layoutClass->properties,
                (array) $this->customProperties(),
                ['description' => $this->assetItem->description]
            );

        return $layoutClass->build();
    }

    /**
     * This function is called before the save function. This is the place to do some
     * processing on the value before it is saved.
     *
     * @return void
     */
    public function beforeSave() {}

    /**
     * This function is for doing additional actions while saving, or - when not using the
     * default storage mechanism - doing custom stuff.
     *
     * @return void
     */
    public function save() {}

    /**
     * This function is called after the save function. This is the place to do some
     * processing on the value after it is saved.
     */
    public function afterSave(): void {}

    public function properties(): stdClass
    {
        $properties = new \stdClass;

        if ($this->assetItem->properties) {
            $properties = $this->assetItem->properties;
        }

        if ($this->assetItem->server_properties) {
            $properties = (object) array_merge((array) $properties, (array) $this->assetItem->server_properties);
        }

        return $properties;
    }

    /**
     * Checks if the field is required. This will be used
     * for frontend and backend validation.
     */
    public function isRequired(): bool
    {
        return $this->properties()->required ?? false;
    }

    /**
     * getFieldType returns the type of the asset item
     */
    public function getFieldType(): string
    {
        return $this->assetItem->type;
    }

    /**
     * Returns wether the field is localized or not.
     */
    public function isLocalized(): bool
    {
        return $this->properties()->localize ?? false;
    }

    /**
     * Marks the field disabled in the form.
     */
    public function isDisabled(): bool
    {
        return $this->properties()->disabled ?? false;
    }

    /**
     * Marks the field disabled in the form.
     */
    public function isGlobal(): bool
    {
        return $this->assetItem->global ?? false;
    }

    /**
     * @deprecated Use setNewValue or setValueFromStorage instead.
     *
     * Sets the value for the component
     *
     * @param  mixed  $value
     * @return void
     */
    public function setValue($value)
    {
        $this->currentValue = $value;
    }

    /**
     * @deprecated Use setValueFromStorage instead.
     *
     * @param  string  $value
     * @return void
     */
    public function setCurrentValue($value)
    {
        // TODO: Remove this function, use setValueFromStorage as that's where
        //       the value is coming from.
        trigger_error('Method '.__METHOD__.' is deprecated', E_USER_DEPRECATED);
        $this->currentValue = $value;
    }

    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    public function getDisplayValue()
    {
        return $this->currentValue;
    }

    /**
     * Sets the value of the component to a new value.
     * The stored value is not updated, unless you save the
     * component after this action.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setNewValue($value)
    {
        $this->newValue = $value;
    }

    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * Get the value used in the default storage mechanism.
     * This is always a string. Use JSON or your own logic for other types of values.
     */
    public function getValueForStorage(): ?string
    {
        return $this->newValue;
    }

    /**
     * Set the value using the string from the default storage mechanism.
     * This is always a string. Use JSON or your own logic for other types of values.
     *
     * string: $value the new value
     *
     * @return bool true if the value is set, false if the value is not set.
     */
    public function setValueFromStorage(string $value): bool
    {
        $this->currentValue = $value;

        return true;
    }

    /**
     * Sets the record id for this component.
     */
    public function setRecordId(?int $recordId)
    {
        $this->recordId = $recordId;
    }

    /**
     * Will return the record ID or 0 if the component is global
     */
    public function getRecordId(): ?int
    {
        if ($this->isGlobal()) {
            return 0;
        }

        return $this->recordId;
    }

    /**
     * This will return true if the component value uses the
     * internal storage mechanism.
     */
    public function usesDefaultStorageMechanism(): bool
    {
        return $this->useDefaultStorageMechanism;
    }

    public function asyncPostRequest()
    {
        if (App::hasDebugModeEnabled()) {
            throw new NotImplementedException('Async request not implemented for: '.$this);
        } else {
            abort(404);
        }
    }

    public function asyncGetRequest()
    {
        $this->asyncPostRequest();
    }

    public function asyncPutRequest()
    {
        $this->asyncPostRequest();
    }

    /**
     * Converts [][] to the DB prefix set in the config
     * Also accepts tablename without [][]
     *
     * @param  $tableName  for example: '[][]user' or 'user'
     * @return string 'cc_user'
     */
    protected function removeDatabasePrefix(string $tableName)
    {
        $prefix = config('database.prefix');
        $tableName = str_replace('[][]', $prefix, $tableName);
        if (strpos($tableName, $prefix) === 0) {
            $tableName = substr($tableName, strlen($prefix));
        }

        return $tableName;
    }
}
