<?php

namespace NotFound\Framework\Services\Forms;

use NotFound\Framework\Models\Forms\Data;
use NotFound\Framework\Models\Forms\Field;

// TODO: Loose coupling on class
// Should rethink how the filters work, csv/table are bad names
// The data should be supplied instead of queried.

class UserDataTransformer
{
    private $formId;

    /**
     * In case you need extra filters on the query which runs on [][]cms_form_data.
     *
     * @var array
     */
    private ?string $customQueryFilter = null;

    private array $customBodyData = [];

    private array $customHeaderData = [];

    /**
     * this array is an array of field ids. Populating this array will allow the function 'getDataArray' to dynamically fill
     * array and remain the same order everytime. This also makes old data submitted to have the same headers as new data submitted.
     */
    private array  $headerIdArray = [];

    /**
     * sometimes we don't want to return certain types. For example when exporting a csv. We don't want to show
     * the files since a csv can't do anything with them.
     */
    private array  $ignoredTypes = [];

    /**
     * This array can be used to ignore certain ids. For example it is now used to either display deleted items or not.
     */
    private array  $filterIds = [];

    /**
     * Contains all the \nfapi\formbuilder\fields\AbstractType fields based on their id.
     *
     * @var asspctiative array
     */
    private array  $idToTypeClass = [];

    /**
     * This will be set when the class is called. Mainly to chenge how the data is outputted.
     */
    private string $outputFilter = '';

    /**
     * Possible values: 'normal', 'all', 'filled'.
     * 'filled': Only return values that are filled in.
     * 'all': Return all the fields, even the deleted ones.
     * 'normal': Returns only fields that are published
     */
    private string $type = 'normal';

    private $dates = ['endDate' => '', 'startDate' => ''];

    public function __construct($formId, $type = 'normal')
    {
        $this->formId = $formId;
        $this->type = $type;
    }

    /**
     * You can set custom filters on the query that gets the actual data for the table/csv
     * Format like this: ' AND ip = 127.0.0.1 ', and such.
     *
     * @var string
     */
    public function setCustomQueryFilter(string $query): void
    {
        $this->customQueryFilter = $query;
    }

    /**
     * Instead of getting the data from the db you can set the custom data here.
     *
     * @var array
     *
     * @param  mixed  $data
     */
    public function setCustomBodyData($data): void
    {
        $this->customBodyData = $data;
    }

    public function setCustomHeaderData($data): void
    {
        $this->customHeaderData = $data;
    }

    public function getDataCsv()
    {
        if (isset($_GET['exportType']) && $_GET['exportType'] == 'customExport') {
            if ($_GET['showDeleted'] === 'true') {
                $this->type = 'all';
            }

            $this->dates['startDate'] = $_GET['startDate'];
            $this->dates['endDate'] = $_GET['endDate'];
        }

        $this->outputFilter = 'csv';
        $this->ignoredTypes = ['file'];

        return $this->getData();
    }

    public function getDataTable()
    {
        $this->outputFilter = 'table';
        $this->ignoredTypes = [];

        return $this->getData();
    }

    private function setFilledIds()
    {
        $values = [];
        /* Set locale to Dutch */
        setlocale(LC_ALL, 'nl_NL');
        $dataArray = DB::query_rs("
            SELECT id, data, timestamp
              FROM `[][]cms_form_data`
            WHERE `form_id` = {$this->formId}
            ");

        foreach ($dataArray as $dataObject) {
            $jsonObject = json_decode($dataObject->{'data'});
            foreach ((array) $jsonObject as $key => $value) {
                $values[] = $key;
            }
        }

        $this->filterIds = array_unique($values);
    }

    private function getData()
    {
        if ($this->type == 'filled') {
            $this->setFilledIds();
        }

        $headerLabelList = $this->createHeaderArray();

        return $this->getDataArray($headerLabelList);
    }

    // The header array contains all the information that is possible within an form post.
    // Since it isn't sure if the data contains all the headers the rest needs to be set to an empty string or such.
    private function createHeaderArray(&$headerLabelArray = [], $id = null)
    {
        $fieldsFromDatabase = [];
        if ($this->customHeaderData != []) {
            $fieldsFromDatabase = $this->customHeaderData;
        } else {
            $fieldsFromDatabase = $this->getFieldsFromDatabase($id);
        }

        $typeFactory = new \NotFound\Framework\Services\Forms\Fields\FactoryType();
        foreach ($fieldsFromDatabase as $field) {
            // recursive when it's a combination
            if ($field->type == 'Combination') {
                $this->createHeaderArray($headerLabelArray, $field->id);

                continue;
            }

            if ($this->ignoreField($field)) {
                continue;
            }

            $fieldClass = $typeFactory->getByType($field->type, $field->properties, $field->id);
            $this->headerIdArray[] = $field->id;
            $this->idToTypeClass[$field->id] = $fieldClass;

            $fieldClass->createHeadersForDataViewer($headerLabelArray, $this->outputFilter);
        }

        if ($id == null) {
            $headerLabelArray[] = 'Invoerdatum';
        }

        return $headerLabelArray;
    }

    private function getFieldsFromDatabase($id)
    {
        $fieldQuery = Field::where('form_id', $this->formId);

        if ($this->type != 'normal') {
            $fieldQuery = $fieldQuery->withTrashed();
        }

        if ($id == null) {
            $fieldQuery->where('parent_id', null);
        } else {
            $fieldQuery
                ->with(['property'])
                ->whereHas('property', function ($q) {
                    $q->where('has_value', 1);
                })
                ->where('parent_id', $id);
        }

        return $fieldQuery
            ->orderBy('order')
            ->get();
    }

    // Some fields should be ignored, such as when a field hasn't been filled in or certain types.
    private function ignoreField($field)
    {
        // Filter fields
        if (in_array($field->type, $this->ignoredTypes)) {
            return true;
        }

        if (count($this->filterIds) > 0) {
            if (in_array($field->id, $this->filterIds)) {
                return true;
            }
        }

        if (! $field->properties) {
            return true;
        }

        return false;
    }

    private function getDataArray($headerArray)
    {
        $dataArray[0] = $headerArray;
        setlocale(LC_ALL, 'nl_NL');

        $formSubmittedByUsers = $this->getDataToParse();

        // For each form filled in
        foreach ($formSubmittedByUsers as $formSubmitted) {
            $formDataSubmitted = $formSubmitted->{'data'};
            $tableRow = [];

            // Loop through the header array and get the values out of the $fieldsData
            foreach ($this->headerIdArray as $fieldId) {
                $field = $this->getFieldClass($fieldId);

                $value = '';
                if (isset($formDataSubmitted->{$fieldId})) {
                    $value = $formDataSubmitted->{$fieldId}->value;
                }

                $field->setValueFromDb($value);

                $field->fillTableForDataViewer($tableRow, $this->outputFilter);
            }

            $tableRow[] = $this->getTimestampBasedOnFilter($formSubmitted->timestamp);

            if ($this->outputFilter == 'csv') {
                $dataArray[] = $tableRow;
            } elseif ($this->outputFilter == 'table') {
                $dataArray[] = ['id' => $formSubmitted->id, 'value' => $tableRow];
            }
        }

        return $dataArray;
    }

    private function getDataToParse()
    {
        if ($this->customBodyData !== []) {
            return $this->customBodyData;
        }

        $query = Data::where('form_id', $this->formId);
        if (! empty($this->dates['startDate']) && ! empty($this->dates['endDate'])) {
            $query
                ->where('timestamp', '>=', $this->dates['startDate'])
                ->where('timestamp', '<=', $this->dates['endDate']);
        }

        return $query->get();
    }

    private function getTimestampBasedOnFilter($timestamp)
    {
        if ($this->outputFilter == 'csv') {
            return strftime('%A %d %B %Y', strtotime($timestamp));
        }

        if ($this->outputFilter == 'table') {
            return (object) ['type' => 'text', 'fieldId' => 0, 'value' => strftime('%A %d %B %Y', strtotime($timestamp))];
        }

        return null;
    }

    private function getFieldClass($id)
    {
        return $this->idToTypeClass[$id];
    }
}
