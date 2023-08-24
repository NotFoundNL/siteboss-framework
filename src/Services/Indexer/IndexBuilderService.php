<?php

namespace NotFound\Framework\Services\Indexer;

use NotFound\Framework\Models\CmsSite;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Menu;
use NotFound\Framework\Services\Assets\PageService;

class IndexBuilderService
{
    private Bool $debug;

    private $locales;

    private $domain;

    private $sitemapFile;

    private AbstractIndexService $searchServer;

    public function __construct($debug = false)
    {
        $serverType = config('indexer.engine');
        $this->debug = $debug;
        $this->locales = Lang::all();

        $this->domain = rtrim(env('APP_URL', ''), '/');
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
                $sitemapFileName = config('indexer.sitemap');
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
        $childPages = Menu::whereParent_id($parentId)->whereEnabled(1)->get();
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

            foreach ($this->locales as $lang) {
                $this->updatePage($menu, $lang);
            }

            // index subitems for page
            foreach ($this->locales as $lang) {
                $this->updateSubPages($menu, $lang);
            }

            $this->indexChildPages($page->id);
        }
    }

    private function updatePage($menu, $lang)
    {
        app()->setLocale($lang->url);
        if (count($this->locales) == 1) {
            $url = $menu->getPath();
        } else {
            $url = $menu->getLocalizedPath();
        }

        if ($this->searchServer->urlNeedsUpdate($url, strtotime($menu->updated_at))) {
            $this->writeDebug(': update needed: ');

            $searchText = '';
            $pageService = new PageService($menu, $lang);
            $title = $menu->getTitle($lang);

            $searchText = $pageService->getContentForIndexer();

            // continue with customValues
            $customValues = [];

            $class = $menu->template->filename ?? '';
            $className = 'App\Http\Controllers\Page\\'.$class.'Controller';
            $c = null;
            $priority = 1;
            if (class_exists($className)) {
                $c = new $className();
                if (method_exists($className, 'customSearchValues')) {
                    $customValues = $c->customSearchValues($menu->id);
                }
                if (method_exists($className, 'searchPriority')) {
                    $priority = $c->searchPriority();
                }
            }

            $searchText = rtrim($searchText, ', ');
            if (! empty($title) && ! empty($searchText)) {

                $searchItem = new SearchItem($url, $title);
                $searchItem->setContent($searchText)->setType('page')->setLanguage($lang->url)->setPriority($priority);
                foreach ($customValues as $key => $value) {
                    $searchItem->setCustomValue($key, $value);
                }
                $result = $this->searchServer->upsertItem($searchItem);

                if ($result->errorCode == 0) {
                    $this->writeDebug(" success\n");
                } else {
                    $this->writeDebug(" FAILED\n");
                }
            } else {
                $this->writeDebug(" empty page or title\n");
            }
        } else {
            $this->writeDebug(": Does not need updating\n");
        }

        if ($this->sitemapFile) {
            // update sitemap
            $sitemap = sprintf(
                "%s/%s\r\n",
                $this->domain,
                $url
            );
            fwrite($this->sitemapFile, $sitemap);
        }
    }

    private function updateSubPages($menu, $lang)
    {
        $class = $menu->template->filename ?? '';
        $className = 'App\Http\Controllers\Page\\'.$class.'Controller';
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

                    $indexItem = new SearchItem($url, $searchItem['title']);
                    if ($searchItem['isFile']) {
                        $indexItem->setType('file');
                        $indexItem->setContent($searchItem['file']);
                    } else {
                        $indexItem->setType($searchItem['type']);
                        $indexItem->setContent($searchItem['content']);
                    }
                    $indexItem->setLanguage($lang->url);
                    foreach ($searchItem['customValues'] as $key => $value) {
                        $indexItem->setCustomValue($key, $value);
                    }
                    $indexItem->setPriority($searchItem['priority']);

                    $success = $this->searchServer->upsertItem($indexItem);

                    if ($this->sitemapFile && $searchItem['sitemap']) {
                        $sitemap = sprintf(
                            "%s%s\r\n",
                            $this->domain,
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

                if ($this->sitemapFile) {
                    $sitemap = sprintf(
                        "%s%s\r\n",
                        $this->domain,
                        $url
                    );
                    fwrite($this->sitemapFile, $sitemap);
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
