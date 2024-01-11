<?php

namespace NotFound\Framework\Http\Controllers\Assets;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Services\Assets\Components\AbstractComponent;
use NotFound\Framework\Services\Assets\TableQueryService;
use NotFound\Framework\Services\Assets\TableService;
use NotFound\Layout\Elements\LayoutBar;
use NotFound\Layout\Elements\LayoutBarButton;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutPager;
use NotFound\Layout\Elements\LayoutSearchBox;
use NotFound\Layout\Elements\LayoutText;
use NotFound\Layout\Elements\LayoutTitle;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Elements\Table\LayoutTable;
use NotFound\Layout\Elements\Table\LayoutTableRow;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Toast;
use NotFound\Framework\Models\Editor\DefaultEditor;

class TableOverviewController extends Controller
{
    /**
     * This endpoint returns information about input type that is created data over tg_cms_table and tg_cms_tableitem
     * Input types are used on front-end especially Cms form builder
     *
     * @param  string  $customer This is an value of tg_cms_table->url column.
     * @return  array   ['headers' => [{'name' => 'name3','properties' => 'sourcename']], 'rows' => [
     */
    public function index(Request $request, Table $table)
    {
        $tableService = new TableService($table, Lang::default());
        $components = $tableService->getFieldComponentsOverview();

        $tableQueryService = new TableQueryService($table, $components);
        $siteTableRowsPaginator = $tableQueryService->getSiteTableRows();

        $layoutTable = new LayoutTable(create: $table->allow_create, delete: $table->allow_delete, sort: $table->allow_sort);
        $layoutTable->setTotalItems($siteTableRowsPaginator->total());

        $editorClass = substr_replace($table->model, '\\Editor', strrpos($table->model,'\\'),0).'Editor';

        $editor = (class_exists($editorClass)) ? new $editorClass(request()->query('filter'), $tableService) : new DefaultEditor(request()->query('filter'), $tableService);

        $filterParams = '';         
        if (request()->query('filter')) {
            foreach (request()->query('filter') as $key => $value) {
                $filterParams .= '&filter['.$key.']='.$value;
            }
        }

        foreach ($siteTableRowsPaginator as $row) {
            $link = sprintf('/table/%s/%d/?page=%d&sort=%s&asc=%s', $table->url, $row->id, $request->page ?? 1, $request->sort ?? '', $request->asc ?? '').$filterParams;
            $layoutRow = new LayoutTableRow($row->id, link: $link);

            foreach ($components as $component) {
                /** @var AbstractComponent $component */
                $component->setRecordId($row->id);

                if (isset($row->{$component->assetItem->internal})) {
                    $component->setValueFromStorage($row->{$component->assetItem->internal});
                } else {
                    $component->setValueFromStorage('');
                }

                $layoutRow->addColumn($component->getTableOverviewContent());
            }

            $layoutTable->addRow($layoutRow);
        }

        foreach ($components as $component) {
            /** @var AbstractComponent $component */
            $layoutTable->addHeader($component->getOverviewHeaderContent());
        }

        $page = new LayoutPage($table->name);
        $page->addTitle(new LayoutTitle($table->name));

        $page->addBreadCrumb($editor->getBreadCrumbs());

        $bar = new LayoutBar();
        $bottomBar = new LayoutBar();
        $bottomBar->noBackground();

        if ($table->allow_create) {
            $addNew = new LayoutBarButton('Nieuw');
            $addNew->setIcon('plus');
            $url = '/table/'.$table->url.'/0';
            if($filterParams != '')
            {
                $url .= '?' . ltrim($filterParams,'&');
            }
            $addNew->setLink($url);
            $bar->addBarButton($addNew);
            $bottomBar->addBarButton($addNew);
        }

        $pager = new LayoutPager(totalItems: $siteTableRowsPaginator->total(), itemsPerPage: request()->query('pitems') ?? $table->properties->itemsPerPage ?? 25);
        $bar->addPager($pager);

        $bar->addSearchBox(new LayoutSearchBox(''));

        $widget = new LayoutWidget(__('siteboss::ui.overview'));
        $widget->noPadding();
        $widget->addBar($bar);
        $widget->addTable($layoutTable);
        $widget->addBar($bottomBar);

        if ($siteTableRowsPaginator->total() == 0) {
            $messageBar = new LayoutBar();

            if (request('search') !== null && request('search') !== '') {
                $messageBar->addText(new LayoutText('Geen resultaten gevonden, probeer een andere zoekterm.'));
            } else {
                $messageBar->addText(new LayoutText('Er zijn geen items, voeg een eerste item toe.'));
            }
            $widget->addBar($messageBar);
        }

        $page->addWidget($widget);

        $response = new LayoutResponse($page);

        return $page->build();
    }

    public function updateField(Request $request, Table $table)
    {
        $request->validate([
            'recordid' => 'required',
            'internal' => 'required',
            'value' => 'required',
        ]);

        $tableName = $table->getSiteTableName();
        $dbTable = DB::table($tableName);

        return $dbTable->where('id', $request->recordid)->update([$request->internal => $request->value]);
    }

    /**
     * Updates the order column of said table. Moves the appropriate record and
     * changes all the previous records to the appropriate order
     *
     * @param  int  $recordId
     * @param  int  $newPosition
     * @param  int  $oldPosition
     * @return  jsonResponse
     */
    public function updatePosition(Request $request, Table $table)
    {
        if (! $table->allow_sort) {
            abort(403);
        }

        $request->validate([
            'recordId' => 'required|int',
            'replacedRecordId' => 'required|int',
        ]);

        $response = new LayoutResponse();
        try {
            db_table_items_change_order($table->getSiteTableName(), $request->recordId, $request->replacedRecordId);
        } catch (\Exception $e) {
            $response->addAction(new Toast($e->getMessage(), 'error'));

            return $response->build();
        }

        return response()->json(['status' => 'ok']);
    }

    public function create(Request $request, Table $table)
    {
        // TODO: This call does not make sense, and should be removed asap
        if (! $table->allow_create) {
            abort(403);
        }

        return ['id' => '0'];
    }
}
