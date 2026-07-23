<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use NotFound\Framework\Helpers\Layout\Elements\LayoutButton;
use NotFound\Framework\Helpers\Layout\Elements\LayoutForm;
use NotFound\Framework\Helpers\Layout\Helpers\LayoutWidgetHelper;
use NotFound\Framework\Helpers\Layout\LayoutResponse;
use NotFound\Framework\Helpers\Layout\Responses\Redirect;
use NotFound\Framework\Helpers\Layout\Responses\Toast;
use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\TableAction;
use NotFound\Framework\Services\Assets\Enums\TableActionCondition;
use NotFound\Framework\Services\Assets\Enums\TableActionType;
use NotFound\Framework\Services\Editor\TableActionsEditor;

class CmsEditorTableActionController extends Controller
{
    public function create(FormDataRequest $request, Table $table)
    {
        $table->actions()->create($request->validate($this->rules()));

        $table->exportToFile();

        $response = new LayoutResponse;
        $response->addAction(new Toast('Action added'));
        $response->addAction(new Redirect('/app/editor/table/'.$table->id));

        return $response->build();
    }

    public function readOne(Table $table, TableAction $tableAction)
    {
        $this->checkTable($table, $tableAction);

        $widgetPage = new LayoutWidgetHelper(pageTitle: 'CMS Editor', widgetTitle: 'Retention action');
        $widgetPage->addBreadcrumb('CMS Editor', '/app/editor/');
        $widgetPage->addBreadcrumb('Tables', '/app/editor/table/');
        $widgetPage->addBreadcrumb($table->name ?? 'Table', '/app/editor/table/'.$table->id);

        $editor = new TableActionsEditor($table);

        $form = new LayoutForm($editor->baseUrl().'/'.$tableAction->id);
        $editor->addInputs($form, $tableAction);
        $form->addButton(new LayoutButton('Save action'));

        $widgetPage->widget->addForm($form);

        return $widgetPage->response();
    }

    public function update(FormDataRequest $request, Table $table, TableAction $tableAction)
    {
        $this->checkTable($table, $tableAction);

        $tableAction->update($request->validate($this->rules()));

        $table->exportToFile();

        $response = new LayoutResponse;
        $response->addAction(new Toast('Action updated'));
        $response->addAction(new Refresh);
        $response->addAction(new Redirect('/app/editor/table/'.$table->id));

        return $response->build();
    }

    public function deleteRecord(Table $table, TableAction $tableAction)
    {
        $this->checkTable($table, $tableAction);

        Log::withContext(['table-id' => $table->id, 'action-id' => $tableAction->id])->notice('Table action deleted');

        $tableAction->delete();

        $table->exportToFile();

        return response()->json(['status' => 'ok']);
    }

    private function rules(): array
    {
        return [
            'condition' => ['required', Rule::enum(TableActionCondition::class)],
            'days' => ['required', 'integer', 'min:0'],
            'action' => ['required', Rule::enum(TableActionType::class)],
        ];
    }

    /**
     * The action is nested under a table in the URL, make sure it is the
     * table it actually belongs to.
     */
    private function checkTable(Table $table, TableAction $tableAction): void
    {
        if ($tableAction->table_id !== $table->id) {
            abort(404);
        }
    }
}
