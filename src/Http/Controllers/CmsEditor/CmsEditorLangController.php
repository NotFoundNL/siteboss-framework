<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use NotFound\Framework\Http\Controllers\Controller;
use App\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\Lang;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Log;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Elements\Table\LayoutTable;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Layout\Elements\Table\LayoutTableRow;
use NotFound\Layout\Helpers\LayoutWidgetHelper;
use NotFound\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Layout\Inputs\LayoutInputText;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Toast;

class CmsEditorLangController extends Controller
{
    public function index()
    {
        $response = new LayoutResponse();

        $page = new LayoutPage('Language');

        $breadcrumbs = new LayoutBreadcrumb();
        $breadcrumbs->addHome();
        $breadcrumbs->addItem('CMS Editor', '/app/editor/');
        $breadcrumbs->addItem('Language');
        $page->addBreadCrumb($breadcrumbs);

        $widget1 = new LayoutWidget('Language', 6);

        $table = new LayoutTable(delete: false, edit: true, create: true, sort: true);
        $table->addHeader(new LayoutTableHeader('Language', 'language'));
        $table->addHeader(new LayoutTableHeader('Url', 'url'));
        $table->addHeader(new LayoutTableHeader('Flag', 'flag'));
        $table->addHeader(new LayoutTableHeader('Enabled', 'enabled'));
        $table->addHeader(new LayoutTableHeader('Default', 'default'));
        $tables = Lang::orderBy('order')->get();

        foreach ($tables as $langTable) {
            $row = new LayoutTableRow($langTable->id, '/app/editor/lang/'.$langTable->id);
            $row->addColumn(new LayoutTableColumn($langTable->language, 'text'));
            $row->addColumn(new LayoutTableColumn($langTable->url, 'text'));
            $row->addColumn(new LayoutTableColumn($langTable->flag ?? '', 'text'));
            $row->addColumn(new LayoutTableColumn($langTable->enabled, 'checkbox'));
            $row->addColumn(new LayoutTableColumn($langTable->default, 'checkbox'));
            $table->addRow($row);
        }
        $widget1->addTable($table);
        $widget1->noPadding();

        $page->addWidget($widget1);

        $widget2 = new LayoutWidget('Add new language', 6);

        $newFieldForm = new LayoutForm('/app/editor/lang/');

        $newFieldForm->addInput((new LayoutInputText('language', 'Language display name'))->setRequired());
        $newFieldForm->addInput((new LayoutInputText('url', 'Url'))->setRequired());
        $newFieldForm->addInput((new LayoutInputText('flag', 'Flag'))->setRequired());
        $newFieldForm->addButton(new LayoutButton('Add language'));

        $widget2->addForm($newFieldForm);

        $page->addWidget($widget2);

        $response->addUIElement($page);

        return $response->build();
    }

    public function create(FormDataRequest $request)
    {
        $request->validate([
            'language' => 'string|required',
            'url' => 'string|required',
            'flag' => 'string|required',
        ]);

        $response = new LayoutResponse();
        $response->addAction(new Toast('Language added'));

        $max = Lang::max('order') + 1;
        $newLang = Lang::insertGetId([
            'language' => $request->language,
            'url' => $request->url,
            'flag' => $request->flag,
            'enabled' => false,
            'order' => $max,
        ]);
        $response->addAction(new Redirect('/app/editor/lang/'.$newLang));

        return $response->build();
    }

    public function readOne(Lang $lang)
    {
        $widgetPage = new LayoutWidgetHelper(pageTitle: 'Language', widgetTitle: $lang->language);
        $widgetPage->addBreadcrumb('CMS Editor', '/app/editor/');
        $widgetPage->addBreadcrumb('Language', '/app/editor/lang/');

        $form = new LayoutForm('/app/editor/lang/'.$lang->id);

        $input = new LayoutInputText('language', 'Language');
        $input->setValue($lang->language);
        $input->setRequired();
        $form->addInput($input);

        $input = new LayoutInputText('url', 'Url');
        $input->setValue($lang->url);
        $input->setRequired();
        $form->addInput($input);

        $input = new LayoutInputText('flag', 'Flag');
        $input->setValue($lang->flag);
        $input->setRequired();
        $form->addInput($input);

        $input = new LayoutInputCheckbox('enabled', 'Enabled');
        $input->setValue($lang->enabled == 1 ?? false);
        $form->addInput($input);

        $form->addButton(new LayoutButton(__('save')));

        $widgetPage->widget->addForm($form);

        return $widgetPage->response();
    }

    public function update(FormDataRequest $request, Lang $lang)
    {
        $lang->update($request->validate([
            'language' => 'string|required',
            'url' => 'string|required',
            'flag' => 'string|required',
            'enabled' => 'boolean',
        ]), ['timestamps' => false]);
        $response = new LayoutResponse();
        $response->addAction(new Toast('Field properties updated'));

        return $response->build();
    }

    public function updatePosition(HttpRequest $request)
    {
        $request->validate([
            'recordId' => 'required|int',
            'replacedRecordId' => 'required|int',
        ]);

        $response = new LayoutResponse();
        try {
            db_table_items_change_order('lang', $request->recordId, $request->replacedRecordId);
        } catch (\Exception $e) {
            $response->addAction(new Toast($e->getMessage(), 'error'));

            return $response->build();
        }

        return response()->json(['status' => 'ok']);
    }

    public function deleteRecord(int $id)
    {
        // $this->authorize('delete', Lang::class);
        Log::withContext(['record-id' => $id])->notice('Language deleted');

        if (Lang::where('id', $id)->delete()) {
            return response()->json(['status' => 'ok']);
        }

        abort(404, __('response.table.delete'));
    }
}
