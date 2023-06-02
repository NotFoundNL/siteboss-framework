<?php

namespace NotFound\Framework\Services\Indexer;

use NotFound\Framework\Services\Assets\PageService;
use NotFound\Framework\Models\CmsSite;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Menu;

class IndexBuilderService
{
    private Bool $debug;

    private $locales;

    private $defaultLocale;

    private $domainName;

    private $sitemapFile;

    private AbstractIndexService $searchServer;

    private array $indexableTypes = ['Text'];

    public function __construct(string $serverType, $debug = false)
    {
        $this->debug = $debug;
        $this->locales = Lang::all();

        $locale = env('SB_LOCALES_DEFAULT', 'nl');
        $this->defaultLocale = Lang::where('url', $locale)->get();
        $this->domainName = env('APP_NAME');
        switch ($serverType) {
            case 'solr':
                $this->searchServer = new SolrIndexService($this->debug);
                break;
            default:
                exit('Unknown search index type');
        }
    }

    public function run()
    {
        $sites = CmsSite::whereIndex(1)->get();

        if (count($sites) > 0) {
            $startResult = $this->searchServer->startUpdate();
            if (! $startResult) {
                $this->writeDebug("\n\n Error when emptying core! \n\n");
            }

            foreach ($sites as $site) {
                $siteName = $site->name;
                $sitemapFileName = env('APP_SITEMAP');
                if ($sitemapFileName) {
                    $this->createFolderIfNotExists($sitemapFileName);
                    $this->sitemapFile = fopen($sitemapFileName, 'w') or exit('Could not open sitemap file for writing');
                } else {
                    $this->sitemapFile = false;
                    $this->writeDebug("   skipping sitemap\n");
                }

                $siteId = $site->id;
                $this->searchServer->siteId = $siteId;
                $this->searchServer->languageId = 1;

                // insert all pages, starting from the root
                $this->writeDebug("   INDEXING PAGES\n   ==============\n");
                $this->indexChildPages($site->root);

                if ($this->sitemapFile) {
                    fclose($this->sitemapFile);
                }
                $finish = $this->searchServer->finishUpdate();

                $this->writeDebug($finish->message);
            }
        } else {
            $this->writeDebug("No sites to index\n");
        }
    }

    private function indexChildPages($parentId)
    {
        $childPages = Menu::whereParent_id($parentId)->get();
        foreach ($childPages as $page) {
            $this->writeDebug(sprintf("    * Page \e[1m%s\e[0m (id: %d)", $page->url, $page->id));

            if (! isset($page->template->id)) {
                $this->writeDebug("   skipping, no template found\n");

                continue;
            }

            if (! isset($page->template->properties->searchable) || $page->template->properties->searchable == 0) {
                $this->writeDebug("   skipping, template not searchable\n");

                continue;
            }
            if (isset($page->properties->exludeFromSearch) && $page->properties->exludeFromSearch == true) {
                $this->writeDebug("  skipping, page not searchable\n");

                continue;
            }

            $menu = Menu::whereId($page->id)->firstOrFail();

            $languages = Lang::get();
            if ($this->searchServer->urlNeedsUpdate($menu->getPath(), strtotime($menu->updated_at))) {
                $this->writeDebug(': update needed: ');

                foreach ($languages as $lang) {
                    $this->updatePage($menu, $lang);
                }
            } else {
                $this->writeDebug(": Does not need updating\n");
            }
            // index subitems for page
            foreach ($languages as $lang) {
                $this->updateSubPages($menu, $lang);
            }

            $this->indexChildPages($page->id);
        }
    }

    private function updatePage($menu, $lang)
    {
        $success = true;

        if ($this->sitemapFile) {
            $sitemap = '';
        }
        $searchText = '';
        $pageService = new PageService($menu, $lang);

        $url = $menu->getPath();

        $values = $pageService->getCachedValues();
        foreach ($values as $internal => $value) {
            if (in_array($value->type, $this->indexableTypes)) {
                if ($value->val !== null) {
                    $searchText .= strip_tags($value->val).', ';
                }
            }
        }

        // continue with customValues
        $customValues = [];

        $class = $menu->template->filename ?? '';
        $className = 'Siteboss\App\Http\Controllers\Page\\'.$class.'Controller';
        $c = null;
        $priority = 1;
        if (class_exists($className)) {
            $c = new $className();
            if (method_exists($className, 'customSearchValues')) {
                $customValues = $c->customSearchValues();
            }
            if (method_exists($className, 'searchPriority')) {
                $priority = $c->searchPriority();
            }
        }

        $searchText = rtrim($searchText, ', ');

        $result = $this->searchServer->upsertUrl($url, $values['title']->val, $searchText, 'page', $lang->id, $customValues, $priority);

        if ($result->errorCode == 0) {
            $this->writeDebug(" success\n");
        } else {
            $this->writeDebug(" FAILED\n");
        }

        if ($this->sitemapFile) {
            // update sitemap
            $sitemap .= sprintf(
                "%s%s\r\n",
                $this->domainName,
                $url
            );
        }
    }

    private function updateSubPages($menu, $lang)
    {
        $class = $menu->template->filename ?? '';
        $className = 'Siteboss\App\Http\Controllers\Page\\'.$class.'Controller';
        $c = null;

        // update subPage if necessary
        if (class_exists($className)) {
            $c = new $className();

            $this->updateSubitems($c, $lang);
        }
    }

    private function updateSubitems($class, $lang)
    {
        app()->setLocale($lang->url);

        $subPages = $class->searchSubitems();
        foreach ($subPages as $subPage) {
            foreach ($subPage as $searchItem) {
                $url = $searchItem['url'];
                $this->writeDebug($url);

                if ($this->searchServer->urlNeedsUpdate($url, strtotime($searchItem['updated']))) {
                    $this->writeDebug(': update needed: ');
                    $success = true;

                    if ($searchItem['isFile']) {
                        $success = $this->searchServer->upsertFile($url, $searchItem['title'], $searchItem['file'], $searchItem['type'], $lang->id, $searchItem['customValues'], $searchItem['priority']);
                    } else { // subitem is table row
                        $success = $this->searchServer->upsertUrl($url, $searchItem['title'], $searchItem['content'], $searchItem['type'], $lang->id, $searchItem['customValues'], $searchItem['priority']);
                    }

                    if ($this->sitemapFile && $searchItem['sitemap']) {
                        $sitemap = sprintf(
                            "%s%s\r\n",
                            $this->domainName,
                            $url
                        );
                    }

                    if ($success->errorCode == 0) {
                        $this->writeDebug(" success\n");
                    } else {
                        $this->writeDebug($success->message);
                    }
                } else {
                    $this->writeDebug(": Does not need updating\n");
                }
            }
        }
    }

    private function createFolderIfNotExists($fullFilePath)
    {
        $path_parts = pathinfo($fullFilePath);
        if (! file_exists($path_parts['dirname'])) {
            if (! mkdir($path_parts['dirname'])) {
                printf("\n\n### Error creating sitemap folder");
            }
        }
    }

    private function writeDebug($text)
    {
        if ($this->debug) {
            printf($text);
        }
    }
}
