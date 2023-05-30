<?php

namespace NotFound\Framework\Http\Controllers\Assets;

use App\Http\Requests\FormDataRequest;
use App\Services\Assets\PageService;
use Illuminate\Support\Collection;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Menu;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Helpers\LayoutWidgetHelper;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Toast;

class PageEditorController extends AssetEditorController
{
    public function index(Menu $menu, string $langSlug)
    {
        $lang = Lang::whereUrl($langSlug)->first();
        $pageService = new PageService($menu, $lang);

        $formUrl = sprintf('/app/page/%s/editor/%s/', $menu->id, urlencode($lang->url));
        $form = new LayoutForm($formUrl);

        $pageService->getStaticInputs()->each(function ($input) use ($form) {
            $form->addInput($input);
        });

        if ($pageService->getProperty('meta') === true) {
            $pageService->getMetaInputs()->each(function ($input) use ($form) {
                $form->addInput($input);
            });
        }

        $pageService->getComponents()->each(function ($component) use ($form) {
            $form->addComponent($component);
        });

        $form->addButton(new LayoutButton(__('ui.save')));

        $helper = new LayoutWidgetHelper(__('page.title'), $lang->language.': '.($menu->getTitle($lang) ?? __('page.edit')));

        $this->setBreadcrumbs($helper, ($menu->parent()->first()));

        $url = sprintf('/app/page/%s/editor/', $menu->id);
        $this->addLanguageBarToWidget($helper->widget, $url, $lang);

        $helper->widget->addForm($form);

        return $helper->response();
    }

    public function update(FormDataRequest $request, Menu $menu, $langSlug)
    {
        $lang = Lang::whereUrl($langSlug)->firstOrFail();

        $pageService = new PageService($menu, $lang);

        if (! $pageService->validate($request)) {
            // TODO: better error
            abort(422, 'Error validating');
        }

        $pageService->update($lang);

        $response = new LayoutResponse();
        $response->addAction(new Toast(__('response.table.ok')));
        $response->addAction(new Redirect('/app/menu/'.$menu->parent_id));

        return $response->build();
    }

    private function setBreadcrumbs(LayoutWidgetHelper $helper, Menu $menu): void
    {
        $collection = new Collection();
        while ($menu) {
            $collection->add($menu);

            if ($menu->parent_id == null || $menu->parent_id == 0) {
                $menu = null;
            } else {
                $menu = Menu::whereId($menu->parent_id)->first();
            }
        }

        for ($i = count($collection) - 1; $i >= 0; $i--) {
            $pageTitle = $collection[$i]->parent_id === 0 ? 'Menu' : $collection[$i]->getTitle(Lang::whereDefault(1)->first()) ?? __('page.noTitleSet');
            $helper->addBreadcrumb($pageTitle, '/app/menu/'.$collection[$i]->id);
        }
    }
}
