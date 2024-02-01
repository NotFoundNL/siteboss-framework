<?php

namespace NotFound\Framework\Http\Controllers\Assets;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use NotFound\Framework\Events\AfterSaveEvent;
use NotFound\Framework\Events\BeforeSaveEvent;
use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Services\Assets\Components\ComponentEditorLink;
use NotFound\Framework\Services\Assets\TableService;
use NotFound\Layout\Elements\LayoutBar;
use NotFound\Layout\Elements\LayoutBarButton;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutTitle;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Reload;
use NotFound\Layout\Responses\Toast;

/**
 * Controller for viewing / updating a table
 */
class TableEditorController extends AssetEditorController
{
    public function index(Request $request, Table $table, int $recordId, string $langUrl)
    {
        $this->authorize('view', $table);
        $lang = Lang::whereUrl($langUrl)->firstOrFail();

        $tableService = new TableService($table, $lang, $recordId === 0 ? null : $recordId);

        $editor = $this->customEditor($table, $tableService);

        $params = sprintf('?page=%d&sort=%s&asc=%s', $request->page ?? 1, $request->sort ?? '', $request->asc ?? '');

        $formUrl = sprintf('/table/%s/%s/%s/%s', $table->url, $recordId ?? 0, urlencode($langUrl), $params.$editor->filterToParams());

        $form = new LayoutForm($formUrl);

        $tableService->getComponents()->each(function ($component) use ($form, $tableService) {
            if (auth('openid')->user()->hasRole('admin') && request()->query('editor') === '1') {
                $editorLink = new ComponentEditorLink($tableService, $component->assetItem);
                $form->addComponent($editorLink);
            }
            $form->addComponent($component);
        });

        $page = new LayoutPage(__('siteboss::ui.page'));
        $page->addTitle(new LayoutTitle($table->name));
        $page->addBreadCrumb($editor->getBreadCrumbs(true));

        $upsertingText = $recordId === 0 ? __('siteboss::ui.new') : __('siteboss::ui.edit');

        $saveButton = new LayoutButton(__('siteboss::ui.save'));

        if (! (isset($table->properties?->disable_sticky_submit) && $table->properties?->disable_sticky_submit == true)) {
            $saveButton->setSticky();
        }

        if ((isset($table->properties->stay_on_page) && $table->properties->stay_on_page == true)) {
            $saveButton->addAlternative('stay_on_page', __('siteboss::ui.save_no_redirect'));
        }

        $form->addButton($saveButton);

        $widget = new LayoutWidget($upsertingText);
        if (auth('openid')->user()->hasRole('admin') && request()->query('editor') !== '1') {
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

        $editor = $this->customEditor($table, $tableService);

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
                $url = '/table/'.$table->url.'/'.$id;
                if ($params = $editor->filterToParams()) {
                    $url .= '?'.ltrim($params, '&');
                }
                $response->addAction(new Redirect($url));
            } else {
                $response->addAction(new Reload());
            }
        } else {
            // Redirect

            $params = sprintf('?page=%d&sort=%s&asc=%s', $request->page ?? 1, $request->sort ?? '', $request->asc ?? '');
            $params .= $editor->filterToParams();
            $response->addAction(new Redirect('/table/'.$table->url.'/?'.$params));
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
