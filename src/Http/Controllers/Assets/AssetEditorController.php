<?php

namespace NotFound\Framework\Http\Controllers\Assets;

use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Models\Lang;
use NotFound\Layout\Elements\LayoutBar;
use NotFound\Layout\Elements\LayoutBarButton;
use NotFound\Layout\Elements\LayoutWidget;

abstract class AssetEditorController extends Controller
{
    /**
     * Adds a language bar to the widget
     *
     * @param  LayoutWidget  $widget Widget to add to
     * @param  string  $url the url to go to. NOTE: adds the locale to the end
     * @param  Lang  $currentLang Disables routing to the current lang
     */
    protected function addLanguageBarToWidget(LayoutWidget $widget, string $url, Lang $currentLang): void
    {
        $langs = Lang::all();
        if ($langs->count() <= 1) {
            return;
        }

        $bar = new LayoutBar();
        $bar->removePadding();
        foreach ($langs as $lang) {
            if ($lang == $currentLang) {
                continue;
            }

            $button = new LayoutBarButton($lang->language);
            $languageUrl = $url.urlencode($lang->url).'/';
            $button->setLink($languageUrl);
            $bar->addBarButton($button);
        }

        $widget->addBar($bar);
    }
}
