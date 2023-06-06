<?php

namespace NotFound\Framework\Http\Controllers\Assets;

use App\Events\AfterSaveEvent;
use App\Events\BeforeSaveEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Services\Assets\Components\ComponentEditorLink;
use NotFound\Framework\Services\Assets\TableService;
use NotFound\Layout\Elements\LayoutBar;
use NotFound\Layout\Elements\LayoutBarButton;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutTitle;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Toast;

/**
 * Controller for viewing / updating a table
 */
class TableEditorController extends AssetEditorController
{
    public function index(Table $table, int $recordId, string $langUrl)
    {
        $this->authorize('view', $table);
        $lang = Lang::whereUrl($langUrl)->firstOrFail();

        $tableService = new TableService($table, $lang, $recordId === 0 ? null : $recordId);

        $formUrl = sprintf('/table/%s/%s/%s/', $table->url, $recordId ?? 0, urlencode($langUrl));

        $form = new LayoutForm($formUrl);

        $tableService->getComponents()->each(function ($component) use ($form, $tableService) {
            if (auth()->user()->hasRole('admin') && request()->query('editor') === '1') {
                $editorLink = new ComponentEditorLink($tableService, $component->assetItem);
                $form->addComponent($editorLink);
            }
            $form->addComponent($component);
        });

        $page = new LayoutPage(__('siteboss::ui.page'));
        $page->addTitle(new LayoutTitle($table->name));

        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();
        $breadcrumb->addItem($table->name, '/table/'.$table->url.'/');
        $upsertingText = $recordId === 0 ? __('siteboss::ui.new') : __('siteboss::ui.edit');
        $breadcrumb->addItem($upsertingText); //TODO: Add better title
        $page->addBreadCrumb($breadcrumb);

        $saveButton = new LayoutButton(__('siteboss::ui.save'));

        if (! (isset($table->properties?->disable_sticky_submit) && $table->properties?->disable_sticky_submit == true)) {
            $saveButton->setSticky();
        }

        if ((isset($table->properties->stay_on_page) && $table->properties->stay_on_page == true)) {
            $saveButton->addAlternative('stay_on_page', __('siteboss::ui.save_no_redirect'));
        }

        $form->addButton($saveButton);

        $widget = new LayoutWidget($upsertingText);
        if (auth()->user()->hasRole('admin') && request()->query('editor') !== '1') {
            $bar = new LayoutBar();
            $bar->removePadding();
            $editButton = new LayoutBarButton(__('siteboss::ui.edit'));
            $editButton->setLink('/table/'.$table->url.'/'.$recordId.'/?editor=1');
            $bar->addBarButton($editButton);
            $widget->addBar($bar);
        }

        if ($table->isLocalized() && $recordId !== 0) {
            $url = sprintf('/table/%s/%s/', $table->url, $recordId);
            $this->addLanguageBarToWidget($widget, $url, $lang);
        }

        $widget->addForm($form);

        $page->addWidget($widget);

        return response()->json($page->build());
    }

    public function update(FormDataRequest $request, Table $table, int $recordId, string $langSlug)
    {
        $this->authorize('update', $table);
        Log::withContext(['table-name' => $table->name, 'request' => $request->all()])->info('Table updated');
        $newTableRecord = $recordId === 0;
        $lang = Lang::whereUrl($langSlug)->first();

        if ($recordId === 0) {
            $this->authorize('create', $table);

            $recordId = null;
        }

        $tableService = new TableService($table, $lang, $recordId);

        if (! $tableService->validate($request)) {
            // TODO: better error
            abort(422, 'Error validating');
        }

        event(new BeforeSaveEvent($table));

        $lang = Lang::whereUrl($langSlug)->first();
        if ($newTableRecord) {
            $id = $tableService->create($lang);
        } else {
            $tableService->update($lang);
        }

        event(new AfterSaveEvent($table));

        Cache::clear('table'.$table->id.'_'.$recordId);

        $response = new LayoutResponse();
        $response->addAction(new Toast(__('siteboss::response.table.ok')));

        if (
            isset($request->siteboss_formOptions['send']) &&
            $request->siteboss_formOptions['send'] === 'stay_on_page'
        ) {
            // Stay on page
            if ($newTableRecord) {
                $response->addAction(new Redirect('/table/'.$table->url.'/'.$id.'/'));
            } else {
                // TODO: Refresh page
            }
        } else {
            // Redirect
            $response->addAction(new Redirect('/table/'.$table->url.'/'));
        }

        return $response->build();
    }

    public function deleteRecord(Table $table, int $recordId)
    {
        $this->authorize('delete', $table);
        Log::withContext(['table-name' => $table->name])->notice('Table deleted');

        if ($table->deleteRecord($recordId)) {
            return response()->json(['status' => 'ok']);
        }

        abort(404, __('siteboss::response.table.delete'));
    }
}