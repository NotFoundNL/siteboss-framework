<?php

namespace NotFound\Framework\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use NotFound\Framework\Models\Lang;

class LocalizationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! App::runningInConsole()) {
            $this->setDefaultLocale();
            $this->setSupportedLocales();
        }

        $this->loadTranslationsFrom(siteboss_path('lang'), 's');
    }

    protected function setSupportedLocales()
    {
        $configLocales = LaravelLocalization::getSupportedLocales();
        $siteLocales = Lang::get()->pluck('url')->toArray();

        // load config, but set new array with values from database
        $supportedLocales = array_reduce($siteLocales, function ($supportedLocales, $locale) use ($configLocales) {
            if (array_key_exists($locale, $configLocales)) {
                $supportedLocales[$locale] = $configLocales[$locale];
            }

            return $supportedLocales;
        }, []);

        LaravelLocalization::setSupportedLocales($supportedLocales);
    }

    protected function setDefaultLocale()
    {
        App::setLocale(Lang::default()->url);
        LaravelLocalization::setLocale(Lang::default()->url);
    }
}
