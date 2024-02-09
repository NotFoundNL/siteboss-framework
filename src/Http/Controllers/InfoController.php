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
    public function index()
    {
        return [
            'menu' => $this->menu(),
            'locales' => [
                // The available language for the website
                'siteLocales' => Lang::all(),
                // The UI language for the CMS
                // TODO: retrieve the selected language from the user settings
                'defaultLocale' => env('SB_LOCALES_DEFAULT', 'en'),
                'availableLocales' => explode(',', env('SB_LOCALES_SUPPORTED', 'nl,en')),
            ],
            'settings' => [
                'documentationUrl' => env('APP_CLIENT_DOCS_URL', 'https://docs.siteboss.nl'),
                'clientLogo' => env('APP_CLIENT_LOGO'),
                'productName' => env('APP_WHITELABEL_NAME', 'SiteBoss'),
                'productLogo' => env('APP_WHITELABEL_LOGO', '/siteboss/images/logo.svg'),
            ],
            'preferences' => $this->preferences(),
        ];
    }

    public function settings()
    {
        $settings = new stdClass();
        $settings->title = env('APP_NAME');
        $settings->productName = env('APP_WHITELABEL_NAME', 'SiteBoss');
        $settings->productLogo = env('APP_WHITELABEL_LOGO', '/siteboss/images/logo.svg');

        $settings->background = (object) [
            'url' => env('APP_LOGIN_IMAGE_URL', '/siteboss/images/back.jpg'),
            'credits' => (object) [
                'name' => env('APP_LOGIN_IMAGE_SOURCE_NAME', 'Dr. Matthias Ripp'),
                'url' => env('APP_LOGIN_IMAGE_SOURCE_URL', 'https://www.flickr.com/photos/56218409@N03/14605956625'),
                'license' => env('APP_LOGIN_IMAGE_SOURCE_LICENSE', 'CC BY 2.0'),
            ],
        ];

        // The UI languages for the Login page (currently no difference from the rest of the CMS)
        $settings->messages = [];
        $settings->defaultLocale = env('SB_LOCALES_DEFAULT', 'en');
        $settings->availableLocales = explode(',', env('SB_LOCALES_SUPPORTED', 'nl,en'));
        $settings->documentationUrl = env('APP_CLIENT_DOCS_URL', 'https://docs.siteboss.nl');
        $settings->logo = env('APP_CLIENT_LOGO');

        return $settings;
    }

    public function oidc()
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
        $configResponse['siteboss_logout_redirect_uri'] = 'https://'.$_SERVER['HTTP_HOST'].'/siteboss/login';

        return $configResponse;
    }

    private function menu()
    {
        $menuConfigFile = base_path('resources/siteboss/menu.json');
        if (file_exists($menuConfigFile)) {
            $menuItems = json_decode(file_get_contents($menuConfigFile));
        } else {
            // Fetch data from database
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

    /**
     * filterRights
     *
     * This function will filter the menu items based on the rights of the user.
     */
    private function filterRights(array $menuItems): array
    {
        $menu = [];
        foreach ($menuItems as $menuitem) {
            // If rights are defined, we'll check them here.
            if (! isset($menuitem->rights) || auth('openid')->user()->checkRights($menuitem->rights)) {
                unset($menuitem->rights);

                // Check if there are subitems and if the user has rights to see them.
                if (isset($menuitem->submenu) && count($menuitem->submenu) > 0) {
                    $submenu = $this->filterRights($menuitem->submenu);
                    if (count($submenu) > 0) {
                        $menuitem->submenu = $submenu;
                        $menu[] = $menuitem;
                    }
                } else {
                    // No submenu, so we'll add it directly to the menu.
                    $menu[] = $menuitem;
                }
            }
        }

        return $menu;
    }

    /**
     * menuFromDatabase
     *
     * This function will convert the menu from the database to the new format
     * and store the file in the resources/siteboss folder.
     *
     * The table
     *
     * @return void
     */
    private function menuFromDatabase()
    {
        $menu = new CmsMenu();
        $menu = StatusColumn::wherePublished($menu, 'cms_menu');
        $menus = $menu->whereEnabled(true)->whereNot('to', '')->orderBy('order')->get();
        $orderedMenu = [];

        foreach ($menus as $menuitem) {
            if ($menuitem->to === null) {
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

    private function preferences()
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
}
