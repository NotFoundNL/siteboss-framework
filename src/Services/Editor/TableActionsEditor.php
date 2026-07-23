<?php

namespace NotFound\Framework\Services\Editor;

use NotFound\Framework\Helpers\Layout\Elements\LayoutButton;
use NotFound\Framework\Helpers\Layout\Elements\LayoutForm;
use NotFound\Framework\Helpers\Layout\Elements\LayoutText;
use NotFound\Framework\Helpers\Layout\Elements\LayoutTitle;
use NotFound\Framework\Helpers\Layout\Elements\LayoutWidget;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTable;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableRow;
use NotFound\Framework\Helpers\Layout\Inputs\LayoutInputDropdown;
use NotFound\Framework\Helpers\Layout\Inputs\LayoutInputNumber;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\TableAction;
use NotFound\Framework\Services\Assets\Enums\TableActionCondition;
use NotFound\Framework\Services\Assets\Enums\TableActionType;

/**
 * Builds the CMS editor UI for the retention actions of a table.
 *
 * Used by the table editor page, which lists the actions and adds new
 * ones, and by the action editor page, which changes a single action.
 */
class TableActionsEditor
{
    public function __construct(private Table $table) {}

    public function baseUrl(): string
    {
        return '/app/editor/table/'.$this->table->id.'/action';
    }

    /**
     * The widget holding the list of actions
     */
    public function overviewWidget(): LayoutWidget
    {
        $widget = new LayoutWidget('Retention actions', 6)->noPadding();

        $widget->addText(new LayoutText(
            '<p class="p-2">These run once a day. Records of which the chosen moment is more than the given number of days ago are archived, deleted or purged.</p>'
        ));

        $widget->addTable($this->overviewTable());

        return $widget;
    }

    /**
     * The widget holding the form to add one.
     */
    public function addWidget(): LayoutWidget
    {
        $widget = new LayoutWidget('Retention actions', 6);
        $form = new LayoutForm($this->baseUrl());
        $this->addInputs($form);
        $form->addButton(new LayoutButton('Add action'));

        $widget->addTitle((new LayoutTitle('Add new action'))->setSize(4));
        $widget->addForm($form);

        return $widget;
    }

    private function overviewTable(): LayoutTable
    {
        $table = new LayoutTable(sort: false, delete: true, edit: true, create: false);
        $table->setDeleteEndpoint($this->baseUrl().'/');

        $table->addHeader(new LayoutTableHeader('When', 'condition'));
        $table->addHeader(new LayoutTableHeader('More than', 'days'));
        $table->addHeader(new LayoutTableHeader('Then', 'action'));

        foreach ($this->table->actions()->orderBy('id', 'asc')->get() as $action) {
            $row = new LayoutTableRow($action->id, $this->baseUrl().'/'.$action->id);
            $row->addColumn(new LayoutTableColumn($action->condition->label(), 'text'));
            $row->addColumn(new LayoutTableColumn($action->days.' days ago', 'text'));
            $row->addColumn(new LayoutTableColumn($action->action->label(), 'text'));
            $table->addRow($row);
        }

        return $table;
    }

    /**
     * The three inputs an action consists of, filled with the values of an
     * existing action when one is given.
     */
    public function addInputs(LayoutForm $form, ?TableAction $action = null): void
    {
        $condition = new LayoutInputDropdown('condition', 'When the record was');
        $condition->setDescription('The moment that is compared, the record is skipped when it is not set.');
        foreach (TableActionCondition::cases() as $case) {
            $condition->addOption($case->value, $case->label());
        }
        $condition->setValue($action->condition->value ?? TableActionCondition::EDIT->value);
        $condition->setRequired();
        $form->addInput($condition);

        $days = new LayoutInputNumber('days', 'More than (days ago)');
        $days->setMin(0);
        $days->setValue((string) ($action->days ?? 30));
        $days->setRequired();
        $form->addInput($days);

        $type = new LayoutInputDropdown('action', 'Then');
        foreach (TableActionType::cases() as $case) {
            $type->addOption($case->value, $case->label());
        }
        $type->setValue($action->action->value ?? TableActionType::ARCHIVE->value);
        $type->setRequired();
        $form->addInput($type);
    }
}
