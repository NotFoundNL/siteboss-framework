<?php

namespace NotFound\Framework\Services\Indexer;

use DateTime;
use NotFound\Framework\Models\CmsSite;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Menu;
use NotFound\Framework\Services\Assets\PageService;

class IndexBuilderService
{
    private $locales;

    private $domain;

    private $sitemapFile;

    private $padding = 140;

    private AbstractIndexService $searchServer;

    public function __construct(private bool $debug = false, private bool $fresh = false)
    {
        $serverType = config('indexer.engine');
        $this->debug = $debug;
        $this->fresh = $fresh;
        $this->locales = Lang::all();

        $this->domain = rtrim(env('APP_URL', ''), '/');
        switch ($serverType) {
            case 'solr':
                $this->searchServer = new SolrIndexService($this->debug, $this->fresh);
                break;
            default:
                exit('Unknown search index type');
        }
    }

    public function run(): void
    {
        if (!$this->searchServer->checkConnection()) {
            $this->writeDebug("\n\n Error connecting to search server! \n\n");

            return;
        }
        if ($this->fresh) {
            $this->searchServer->clean();
        }
        $sites = CmsSite::whereIndex(1)->get();

        if (count($sites) > 0) {
            $startResult = $this->searchServer->startUpdate();
            if (!$startResult) {
                $this->writeDebug("\n\n Error when emptying core! \n\n");
            }

            foreach ($sites as $site) {
                $sitemapFileName = config('indexer.sitemap');
                if ($sitemapFileName) {
                    $this->createFolderIfNotExists($sitemapFileName);
                    $this->sitemapFile = fopen($sitemapFileName, 'w') or exit('Could not open sitemap file for writing');
                } else {
                    $this->sitemapFile = false;
                    $this->writeDebug(" ⏭️ Skipping sitemap\n");
                }

                $siteId = $site->id;
                $this->searchServer->siteId = $siteId;
                $this->searchServer->domain = $site->domain ?? null;

                $this->searchServer->languageId = 1;

                // insert all pages, starting from the root
                $this->writeDebug("┃ INDEXING PAGES\n   ==============\n");
                $this->indexChildPages($site->root);

                if ($this->sitemapFile) {
                    fclose($this->sitemapFile);
                }
            }
        } else {
            $this->writeDebug("No sites to index\n");
            return;
        }
        $finish = $this->searchServer->finishUpdate();

        $this->writeDebug($finish->message);
    }

    private function indexChildPages($parentId)
    {
        $childPages = Menu::whereParent_id($parentId)->whereEnabled(1)->get();
        foreach ($childPages as $page) {
            $this->writeDebug("┃\n");
            $this->writeDebug(sprintf('%s (id: %d)', $page->url, $page->id), true, '┣━┓  📂 Page ');

            if (!isset($page->template->id)) {
                $this->writeDebug(": ❌ Fail, skipping, no template found\n");

                continue;
            }

            $menu = Menu::whereId($page->id)->firstOrFail();

            if (!isset($page->template->properties->searchable) || $page->template->properties->searchable == 0) {
                $this->writeDebug(": ⏭️ Skipping, template excluded from search\n");
            } elseif (isset($page->properties->excludeFromSearch) && $page->properties->excludeFromSearch == true) {
                $this->writeDebug(": ⏭️  Skipping, page excluded from search\n");
            } else {

                foreach ($this->locales as $lang) {
                    $this->updatePage($menu, $lang);
                }
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

        if ($this->searchServer->urlNeedsUpdate($url, $menu->updated_at)) {
            $this->writeDebug(': update needed: ');

            $searchText = '';
            $pageService = new PageService($menu, $lang);
            $title = $menu->getTitle($lang);

            $searchText = $pageService->getContentForIndexer();

            // continue with customValues
            $customValues = [];

            $className = 'App\Http\Controllers\Page\\' . $this->controllerName($menu) . 'Controller';

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
            if (!empty($title) && !empty($searchText)) {

                $searchItem = new SearchItem($url, $title);
                $searchItem->setContent($searchText)
                    ->setLanguage($lang->url)
                    ->setPriority($priority)
                    ->setPublicationDate(new DateTime($menu->updated_at));
                foreach ($customValues as $key => $value) {
                    $searchItem->setCustomValue($key, $value);
                }
                $result = $this->searchServer->upsertItem($searchItem);

                if ($result->errorCode == 0) {
                    $this->writeDebug(": ✅ Success\n");
                } else {
                    $this->writeDebug(": ❌ FAILED\n");
                }
            } else {
                $this->writeDebug(": ❌ Empty page or title\n");
            }
        } else {
            $this->writeDebug(": ✅ Page does not need updating\n");
            $this->searchServer->retainItem($url);
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
        $className = 'App\Http\Controllers\Page\\' . $this->controllerName($menu) . 'Controller';
        $c = null;
        // update subPage if necessary

        if (class_exists($className)) {
            $c = new $className();
            $this->updateSubitems($c, $lang);
        }
    }

    private function controllerName($menu): string
    {
        $class = str_replace('/', '\\', $menu->template->getIdentifier() ?? '');
        $classes = explode('\\', $class);
        foreach ($classes as &$c) {

            $c = ucfirst($c);
        }

        return implode('\\', $classes);
    }

    private function updateSubitems($class, $lang)
    {
        app()->setLocale($lang->url);
        $subPages = $class->searchSubitems();

        // We need to check if the subPages is an array of arrays
        // If not we wrap it in an extra array
        if (count($subPages) > 0 && !is_array($subPages[0])) {
            $subPages = [$subPages];
        }
        foreach ($subPages as $subPage) {

            foreach ($subPage as $searchItem) {
                $success = false;
                if ((new \ReflectionClass($searchItem))->getShortName() == 'SearchItem') {
                    $url = $searchItem->url();
                    $this->writeDebug($url, true, '┃ ┣━ 📄 ');

                    if ($this->searchServer->urlNeedsUpdate($url, $searchItem->lastUpdated())) {

                        $searchItem->setLanguage($lang->url);
                        $success = $this->searchServer->upsertItem($searchItem);
                        if ($this->sitemapFile && $searchItem->sitemap()) {
                            $sitemap = sprintf(
                                "%s%s\r\n",
                                $this->domain,
                                $url
                            );
                            fwrite($this->sitemapFile, $sitemap);
                        }

                        if ($success->errorCode == 0) {
                            $this->writeDebug(": ✅ Success\n");
                        } else {
                            $this->writeDebug($success->message);
                        }
                    } else {
                        $this->writeDebug(": ✅ Item does not need updating\n");
                        $success = $this->searchServer->retainItem($url);
                    }
                } else {
                    dd('Please use the SearchItem class');
                }
            }
        }
    }

    private function createFolderIfNotExists($fullFilePath)
    {
        $path_parts = pathinfo($fullFilePath);
        if (!file_exists($path_parts['dirname'])) {
            if (!mkdir($path_parts['dirname'])) {
                printf("\n\n### Error creating sitemap folder");
            }
        }
    }

    private function writeDebug($text, $padding = false, $prefix = '')
    {
        if ($this->debug) {

            if ($padding) {
                $text = substr($text, 0, $this->padding - strlen($prefix));
                $text = str_pad($text, $this->padding - strlen($prefix), ' ');
            }
            printf("\e[1m" . $prefix . "\e[0m" . $text);
        }
    }

    public function checkConnection(): bool
    {
        return $this->searchServer->checkConnection();
    }
}
