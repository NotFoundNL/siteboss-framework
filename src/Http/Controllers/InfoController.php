<?php

namespace NotFound\Framework\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use NotFound\Framework\Models\CmsMenu;
use NotFound\Framework\Models\CmsUser;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Services\Legacy\StatusColumn;
use Sb;
use stdClass;

class InfoController extends Controller
{
    public function index(): array
    {
        $languages = $this->getLanguages();

        return [
            'menu' => $this->menu(),
            'locales' => [
                'siteLocales' => $languages->list,
                'defaultLocale' => $languages->default,
                'availableLocales' => $languages->available,
            ],
            'settings' => [
                'documentationUrl' => config('siteboss.branding.docs_url'),
                'clientLogo' => config('siteboss.branding.client_logo'),
                'productName' => config('siteboss.branding.product_name'),
                'productLogo' => config('siteboss.branding.product_logo'),
            ],
            'preferences' => $this->preferences(),
        ];
    }

    public function settings(): stdClass
    {
        $languages = $this->getLanguages();

        $settings = new stdClass;
        $settings->title = config('app.name');
        $settings->productName = config('siteboss.branding.product_name');
        $settings->productLogo = config('siteboss.branding.product_logo');

        $settings->background = (object) [
            'url' => config('siteboss.login.image_url'),
            'credits' => (object) [
                'name' => config('siteboss.login.image_source_name'),
                'url' => config('siteboss.login.image_source_url'),
                'license' => config('siteboss.login.image_source_license'),
            ],
        ];

        $settings->messages = [];
        $settings->defaultLocale = $languages->default;
        $settings->availableLocales = $languages->available;
        $settings->documentationUrl = config('siteboss.branding.docs_url');
        $settings->logo = config('siteboss.branding.client_logo');

        return $settings;
    }

    public function oidc(): array
    {
        $secondsToRemember = 3600;
        $configUri = config('openid.configuration_url');
        $configUriHash = crc32($configUri);

        $configResponse = Cache::remember('openid_configuration_url_'.$configUriHash, $secondsToRemember, function () use ($configUri) {
            $response = Http::get($configUri);
            if (! $response->ok()) {
                throw new \Exception('error');
            }

            return $response->json();
        });
        $configResponse['siteboss_client_id'] = config('openid.client_id');
        $configResponse['siteboss_logout_redirect_uri'] = 'https://'.request()->getHost().'/siteboss/login';

        return $configResponse;
    }

    private function menu(): array
    {
        $menuConfigFile = base_path('resources/siteboss/menu.json');
        if (file_exists($menuConfigFile)) {
            $menuItems = json_decode(file_get_contents($menuConfigFile));
        } else {
            Sb::makeDirectory(base_path(), 'resources/siteboss');
            $menuItems = $this->menuFromDatabase();
            if (file_put_contents($menuConfigFile, json_encode($menuItems, JSON_PRETTY_PRINT))) {
                Schema::rename('cms_menu', 'cms_menu_backup');
            } else {
                throw new \Exception('Could not write menu JSON file');
            }
        }

        return $this->filterRights($menuItems);
    }

    private function filterRights(array $menuItems): array
    {
        $menu = [];
        foreach ($menuItems as $menuitem) {
            if (! isset($menuitem->rights) || auth('openid')->user()->checkRights($menuitem->rights)) {
                unset($menuitem->rights);

                if (isset($menuitem->submenu) && count($menuitem->submenu) > 0) {
                    $submenu = $this->filterRights($menuitem->submenu);
                    if (count($submenu) > 0) {
                        $menuitem->submenu = $submenu;
                        $menu[] = $menuitem;
                    }
                } else {
                    $menu[] = $menuitem;
                }
            }
        }

        return $menu;
    }

    private function menuFromDatabase(): array
    {
        $menu = new CmsMenu;
        $menu = StatusColumn::wherePublished($menu, 'cms_menu');
        $menus = $menu->whereEnabled(true)->whereNot('to', '')->orderBy('order')->get();
        $orderedMenu = [];

        foreach ($menus as $menuitem) {

            if ($menuitem->to == null) {
                if (str_starts_with($menuitem->target, 'table-') || str_starts_with($menuitem->target, '#table-')) {
                    $this->convertToToTable($menuitem);
                } elseif (str_starts_with($menuitem->target, 'menu.php') || str_starts_with($menuitem->target, '#menu.php')) {
                    $this->convertToToMenu($menuitem);
                }
            }

            $menuitem->target = ltrim($menuitem->target, '#');

            $menuObj = (object) [
                'icon' => $menuitem->icon,
                'title' => $menuitem->title,
                'path' => $menuitem->to ?? $menuitem->target,
            ];

            if ($menuitem->to) {
                $menuObj->path = $menuitem->to;
            }

            if (trim($menuitem->rights) !== '') {
                $menuObj->rights = $menuitem->rights;
            }

            if ($menuitem->level !== 0) {
                $lastKey = array_key_last($orderedMenu);
                if ($lastKey !== null) {
                    if (! isset($orderedMenu[$lastKey]->submenu)) {
                        $orderedMenu[$lastKey]->submenu = [];
                    }

                    $orderedMenu[$lastKey]->path = '';
                    $orderedMenu[$lastKey]->submenu[] = $menuObj;
                }
            } else {
                $orderedMenu[] = $menuObj;
            }
        }

        return $orderedMenu;
    }

    private function preferences(): object
    {
        $user = auth('openid')->user();
        $user = CmsUser::find($user->id);
        if (! $user->preferences) {
            return (object) [];
        }

        return $user->preferences;
    }

    /* TODO: Remove or move these conversion methods */

    private function convertToToTable($menuitem): void
    {
        $newStr = str_replace('#', '', $menuitem->target);
        $newStr = str_replace('-', '/', $newStr);
        $newStr = str_replace('.cms', '', $newStr);
        $menuitem->to = '/'.$newStr;
        $menuitem->save();
    }

    private function convertToToMenu($menuitem): void
    {
        $newStr = str_replace('#', '', $menuitem->target);
        $newStr = str_replace('menu.php', 'menu', $newStr);
        $newStr = str_replace('?menu=', '/', $newStr);
        $menuitem->to = '/'.$newStr;
        $menuitem->save();
    }

    private function getLanguages(): object
    {
        $languages = Lang::all();
        if (count($languages) === 1) {
            $defaultLocale = $languages->first()->url;
            $availbleLocales = [$languages->first()->url];
        } else {
            $defaultLocale = config('siteboss.locales.default', 'en');
            $availbleLocales = explode(',', config('siteboss.locales.supported', 'en'));
        }

        return (object) [
            'list' => $languages,
            'default' => $defaultLocale,
            'available' => $availbleLocales,
        ];
    }
}
