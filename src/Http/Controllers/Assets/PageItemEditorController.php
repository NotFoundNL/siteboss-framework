<?php

namespace NotFound\Framework\Http\Controllers\Assets;

use NotFound\Framework\Http\Controllers\Controller;
use App\Services\Assets\Components\AbstractComponent;
use App\Services\Assets\Components\FactoryComponent;
use App\Services\Assets\PageService;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Menu;
use NotFound\Framework\Models\TemplateItem;

class PageItemEditorController extends Controller
{
    public function ajaxGet(Menu $menu, string $langUrl, string $fieldInternal)
    {
        $component = $this->getComponent($menu, $langUrl, $fieldInternal);

        return $component->asyncGetRequest();
    }

    public function ajaxPut(Menu $menu, string $langUrl, string $fieldInternal)
    {
        $component = $this->getComponent($menu, $langUrl, $fieldInternal);

        return $component->asyncPutRequest();
    }

    public function ajaxPost(Menu $menu, string $langUrl, string $fieldInternal)
    {
        $component = $this->getComponent($menu, $langUrl, $fieldInternal);

        return $component->asyncPostRequest();
    }

    private function getComponent(Menu $menu, string $langUrl, string $fieldInternal): AbstractComponent
    {
        $lang = Lang::whereUrl($langUrl)->firstOrFail();
        $templateItem = TemplateItem::whereInternal($fieldInternal)->firstOrFail();

        $factory = new FactoryComponent(new PageService($menu, $lang));

        $component = $factory->getByType($templateItem);
        if (! $component) {
            abort(404, 'No component');
        }
        $component->setRecordId($menu->id);

        return $component;
    }
}
