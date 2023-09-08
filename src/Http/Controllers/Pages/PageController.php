<?php

namespace NotFound\Framework\Http\Controllers\Pages;

use Illuminate\Support\Facades\View;
use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Menu;
use NotFound\Framework\Services\Assets\AssetValues;
use NotFound\Framework\Services\Assets\GlobalPageService;
use NotFound\Framework\Services\Assets\PageService;

class PageController extends Controller
{
    protected AssetValues $values;

    protected ?Menu $menuItem = null;

    protected bool $currentPage = false;

    protected string $model = Menu::class;

    public function __construct(
        protected ?int $pageId = null
    ) {
        if ($pageId === null) {
            $this->pageId = request()->route('page_id');
            $this->currentPage = true;
        }

        if (! app()->runningInConsole()) {
            $gp = new GlobalPageService();
            $globalPageValues = new AssetValues($gp->getCachedValues());

            $pageValues = $this->getPageValues();
            $pvObj = new AssetValues($pageValues);
            if ($this->currentPage) {
                View::share('title', $this->getFullTitle());
                View::share('p', $pvObj);
                View::share('c', $this);
                View::share('g', $globalPageValues);
            }
            $this->values = $pvObj;
        }
    }

    public function p(): mixed
    {
        return $this->values;
    }

    public function id(): int
    {
        return $this->pageId;
    }

    protected function menuItem(): Menu
    {
        if ($this->menuItem === null) {
            $this->menuItem = $this->model::whereId($this->id())->firstOrFail();
        }

        return $this->menuItem;
    }

    public function getTitle(): ?string
    {
        return $this->menuItem()->getTitle() ?? '';
    }

    public function getFullTitle(): string
    {
        return $this->getTitle().' - '.config('app.name');
    }

    public function slug(): string
    {
        return $this->menuItem()->url;
    }

    public function breadcrumbs(): array
    {
        $crumbs = [];
        $menu = $this->menuItem();
        while ($menu->parent_id !== 0) {
            array_unshift($crumbs, (object) [
                'url' => $menu->url(),
                'title' => $menu->getTitle(),
                'active' => $menu->id == $this->id(),
            ]);
            $menu = $menu->parent;
        }

        return $crumbs;
    }

    public function url(): string
    {
        return $this->menuItem()->url();
    }

    /**
     * parent
     *
     * Get the parent Controller of this page.
     * Will return null if this is the top level page.
     */
    protected function parent(): ?PageController
    {
        $parentId = $this->menuItem()->parent_id;

        if ($parentId == 0) {
            return null;
        }

        $pageController = new PageController($parentId);

        return $pageController;
    }

    protected function children($ignoreMenu = false): array
    {
        $childPages = [];
        $childItems = $this->menuItem()->children;
        foreach ($childItems as $childItem) {
            if ($childItem->enabled && ($ignoreMenu || $childItem->menu)) {
                $pageController = new PageController($childItem->id);
                $childPages[] = $pageController;
            }
        }

        return $childPages;
    }

    protected function siblings($includeSelf = false, $ignoreMenu = false): array
    {
        $siblingPages = [];
        $siblingItems = $this->parent()->menuItem()->children;
        foreach ($siblingItems as $siblingItem) {
            if (! $includeSelf && $siblingItem->id == $this->id()) {
                continue;
            }
            if ($siblingItem->enabled && ($ignoreMenu || $siblingItem->menu)) {
                $pageController = new PageController($siblingItem->id);
                $siblingPages[] = $pageController;
            }
        }

        return $siblingPages;
    }

    protected function getPageValues(): array
    {
        $p = new PageService($this->menuItem(), Lang::current());

        return $p->getCachedValues();
    }

    /**
     * searchSubitems
     *
     * Return the subitems that have to be indexed by the SOLR indexer.
     *
     * @return array an array that was created by searchItemCollection in the SearchItemService
     */
    public function searchSubitems(): array
    {
        return [];
    }

    public function solrDate($pageId)
    {
        $this->pageId = $pageId;
        $date = $this->menuItem()->updated_at;
        if ($date) {
            return $date->toIso8601String();
        } else {
            return '2023-01-01T10:00:00Z';
        }
    }
}
