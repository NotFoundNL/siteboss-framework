<?php

namespace NotFound\Framework\Http\Controllers;

use App\Http\Requests\FormDataRequest;
use NotFound\Framework\Services\Legacy\StatusColumn;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Menu;
use NotFound\Framework\Models\Strings;
use NotFound\Framework\Models\Template;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Elements\Table\LayoutTable;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Layout\Elements\Table\LayoutTableRow;
use NotFound\Layout\Inputs\LayoutInputDropdown;
use NotFound\Layout\Inputs\LayoutInputText;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Toast;

/**
 * Creates the data required to display the CMS menu on the frontend.
 * Will read the menu from the [][]cms_menu recursively.
 */
class MenuController extends Controller
{
    public function index($id = 1)
    {
        $currentMenu = Menu::whereId($id)->with(['template'])->firstOrFail();
        $lang = Lang::default();

        $page = new LayoutPage(__('siteboss::page.title'));
        $page->addBreadCrumb($this->getBreadcrumb($currentMenu));
        $page->addWidget($this->getTableWidget($currentMenu, $lang));

        $formWidget = $this->getFormWidget($currentMenu);
        if ($formWidget) {
            $page->addWidget($formWidget);
        }

        $response = new LayoutResponse();
        $response->addUIElement($page);

        return $response->build();
    }

    public function create(FormDataRequest $request, Menu $menu)
    {
        $request->validate([
            'name' => 'required',
            'template_id' => 'int',
        ]);

        $maxOrder = Menu::whereParentId($menu->id)->get()->max('order') + 1;

        $menu = Menu::create([
            'url' => Str::limit(Str::slug($request->name), 33, ''),
            'template_id' => $request->template_id,
            'parent_id' => $menu->id,
            'type' => 0,
            'enabled' => 1,
            'attr' => 0,
            'order' => $maxOrder,
        ]);

        Strings::create([
            'string_id' => $menu->id,
            'table' => 'menu',
            'name' => 'name',
            'lang_id' => 1,
            'value' => $request->name,
        ]);

        Menu::removeRouteCache();

        $response = new LayoutResponse(action: new Redirect('/app/page/'.$menu->id.'/editor/nl/'));

        return $response->build();
    }

    public function delete(Menu $menu)
    {
        StatusColumn::deleteModel($menu);

        Menu::removeRouteCache();

        return $menu;
    }

    public function move(Request $request, Menu $menu)
    {
        $request->validate([
            'recordId' => 'required|int',
            'replacedRecordId' => 'required|int',
        ]);

        $response = new LayoutResponse();
        try {
            db_table_items_change_order($menu->getTable(), $request->recordId, $request->replacedRecordId, "AND `parent_id`={$menu->id}");
        } catch (\Exception $e) {
            $response->addAction(new Toast($e->getMessage(), 'error'));

            return $response->build();
        }

        Menu::removeRouteCache();

        return response()->json(['status' => 'ok']);
    }

    public function toggleEnabled(Menu $menu): array
    {
        return $this->toggleProperty($menu, 'enabled');
    }

    public function toggleMenu(Menu $menu): array
    {
        return $this->toggleProperty($menu, 'menu');
    }

    private function toggleProperty(Menu $menu, string $property): array
    {
        $menu->$property = ! $menu->$property;
        try {
            $menu->save();

            $response = ['value' => $menu->$property, 'message' => __('siteboss::ui.menu.updated')];
        } catch (\Exception $e) {
            $response = ['error' => $e];
        }

        return $response;
    }

    private function getTableWidget(Menu $currentMenu, Lang $lang): LayoutWidget
    {
        $menuItems = Menu::whereParentId($currentMenu->id)
            ->with(['template'])
            ->leftJoin('strings', function ($join) use ($lang) {
                $join->on('strings.string_id', '=', 'menu.id');
                $join->where('strings.lang_id', '=', $lang->id);
                $join->where('strings.table', '=', 'menu');
            })
            ->select('menu.*', 'strings.value')
            ->orderBy('order')
            ->get();

        $table = new LayoutTable(edit: true, sort: true, create: true);
        $table->setDeleteEndpoint('/app/menu/');
        $table->addHeader(new LayoutTableHeader(__('siteboss::page.active'), 'active', 'checkbox'));
        $table->addHeader(new LayoutTableHeader(__('siteboss::page.inMenuShort'), 'menu', 'checkbox'));
        $table->addHeader(new LayoutTableHeader(__('siteboss::page.pageTitle'), 'title'));
        $table->addHeader(new LayoutTableHeader(__('siteboss::page.template'), 'template'));

        foreach ($menuItems as $menuItem) {
            //TODO: localization
            $row = new LayoutTableRow($menuItem->id, '/app/page/'.$menuItem->id.'/editor/nl/');
            $enabled = new LayoutTableColumn($menuItem->enabled ?? 0, 'checkbox');
            $enabled->setToggleEndPoint('/app/menu/'.$menuItem->id.'/toggle/enabled');
            $row->addColumn($enabled);
            $menu = new LayoutTableColumn($menuItem->menu ?? 0, 'checkbox');
            $menu->setToggleEndPoint('/app/menu/'.$menuItem->id.'/toggle/menu');
            $row->addColumn($menu);

            $menuTitle = new LayoutTableColumn($menuItem->value ?? __('siteboss::ui.menu.untitled'));
            if (isset($menuItem->template?->allow_children) && $menuItem->template?->allow_children !== '') {
                $menuTitle->makeLinkButton('/app/menu/'.$menuItem->id);
            }

            $row->addColumn($menuTitle);

            $row->addColumn(new LayoutTableColumn($menuItem->template->name ?? ''));

            $table->addRow($row);
        }

        $tableWidget = new LayoutWidget(__('siteboss::page.overview'));
        $tableWidget->noPadding();
        $tableWidget->addTable($table);

        return $tableWidget;
    }

    private function getFormWidget(Menu $currentMenu): ?LayoutWidget
    {
        if (! isset($currentMenu->template?->allow_children) || $currentMenu->template?->allow_children == '') {
            return null;
        }

        $form = new LayoutForm('/app/page/create/'.$currentMenu->id);

        $text = new LayoutInputText('name', __('siteboss::ui.menu.name'));
        $text->setRequired();
        $form->addInput($text);

        $dropdown = new LayoutInputDropdown('template_id', __('siteboss::ui.menu.template'));
        $dropdown->setRequired();

        $childrenString = $currentMenu->template?->allow_children;
        $allowedTemplates = explode(',', $childrenString);
        foreach (Template::whereIn('id', $allowedTemplates)->get() as $template) {
            $dropdown->addOption($template->id, $template->name);
        }
        $form->addInput($dropdown);

        $form->addButton(new LayoutButton(__('siteboss::ui.save')));

        $formWidget = new LayoutWidget(__('siteboss::ui.menu.new'));
        $formWidget->addForm($form);

        return $formWidget;
    }

    private function getBreadcrumb(Menu $menu): LayoutBreadcrumb
    {
        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();

        $collection = new Collection();
        while ($menu !== null) {
            $collection->add($menu);
            if ($menu->parent_id == null || $menu->parent_id == 0) {
                $menu = null;
            } else {
                $menu = Menu::whereId($menu->parent_id)->first();
            }
        }

        for ($i = count($collection) - 1; $i >= 0; $i--) {
            $pageTitle = $collection[$i]->parent_id === 0 ? 'Menu' : $collection[$i]->getTitle(Lang::default()) ?? __('siteboss::page.noTitleSet');
            if ($i !== 0) {
                $breadcrumb->addItem($pageTitle, '/app/menu/'.$collection[$i]->id);
            } else {
                $breadcrumb->addItem($pageTitle);
            }
        }

        return $breadcrumb;
    }
}
