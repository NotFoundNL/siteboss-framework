<?php

namespace NotFound\Framework\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use NotFound\Framework\Models\CmsGroup;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutSiteBoss;
use NotFound\Layout\Elements\LayoutText;
use NotFound\Layout\Elements\LayoutTitle;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\LayoutResponse;

// TODO: Translation
class AboutController extends Controller
{
    public function index()
    {
        Cache::flush();
        Artisan::call('optimize:clear');
        $productName = env('APP_WHITELABEL_NAME', 'SiteBoss');
        $response = new LayoutResponse;
        $page = new LayoutPage($productName.' CMS');

        $breadcrumb = new LayoutBreadcrumb;
        $breadcrumb->addHome();
        $breadcrumb->addItem(__('siteboss::about.title'));
        $page->addBreadCrumb($breadcrumb);

        $widget = new LayoutWidget('About '.$productName.' CMS', 6);

        $processUser = posix_getpwuid(posix_geteuid());
        $processUser = $processUser['name'];

        $widget->addText(new LayoutText(sprintf('<header style="font-size: 22px;font-weight:bold;border:none">PHP %s</header>', phpversion())));
        $widget->addText(new LayoutText(sprintf('<p>%s: %s</p>', __('siteboss::about.configuration'), php_ini_loaded_file())));
        $widget->addText(new LayoutText(sprintf('<p>%s: %s</p>', __('siteboss::about.server'), gethostname())));
        $widget->addText(new LayoutText(sprintf('<p>%s: %s</p>', __('siteboss::about.maxUploadSize'), $this->getMaximumFileUploadSize())));

        $widget->addText(new LayoutText(sprintf('<p>%s: %s</p>', __('siteboss::about.phpUser'), $processUser)));

        $widget->addText(new LayoutText($productName.' server '.__('siteboss::about.version').' '.'1.3.1'));

        $widget->addText(new LayoutText('Error sound CC Attribution 3.0: Mike Koenig on SoundBible'));

        $widget->addSiteBoss(new LayoutSiteBoss('version'));

        $groupC = new CmsGroup;
        $roles = $groupC->getCachedRolesByActiveUser();

        $widget->addText(new LayoutText(__('siteboss::about.rights').': '.implode(', ', $roles->toArray())));

        $widget2 = new LayoutWidget(__('siteboss::about.rights_info'), 6);

        $groups = CmsGroup::where('description', '!=', '')
            ->whereNotNull('description')->get();

        foreach ($groups as $group) {
            $widget2->addTitle(new LayoutTitle($group->name));
            $widget2->addText(new LayoutText($group->description));
            if (auth('openid')->user()->hasRole($group->internal)) {
                $widget2->addText(new LayoutText(__('siteboss::about.rights_assigned')));
            } else {
                $widget2->addText(new LayoutText(__('siteboss::about.rights_not_assigned')));
            }
        }

        $page->addWidget($widget2);
        $page->addWidget($widget);
        $response->addUIElement($page);

        return response()->json($response->build());
    }

    private function getMaximumFileUploadSize()
    {
        if ($this->convertPHPSizeToBytes(ini_get('post_max_size')) < self::convertPHPSizeToBytes(ini_get('upload_max_filesize'))) {
            return ini_get('post_max_size');
        } else {
            return ini_get('upload_max_filesize');
        }
    }

    private function convertPHPSizeToBytes($sSize)
    {
        //
        $sSuffix = strtoupper(substr($sSize, -1));
        if (! in_array($sSuffix, ['P', 'T', 'G', 'M', 'K'])) {
            return (int) $sSize;
        }
        $iValue = substr($sSize, 0, -1);
        switch ($sSuffix) {
            case 'P':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'T':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'G':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'M':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'K':
                $iValue *= 1024;
                break;
        }

        return (int) $iValue;
    }
}
