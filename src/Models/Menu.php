<?php

namespace NotFound\Framework\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use NotFound\Framework\Services\Assets\Enums\TemplateType;
use NotFound\Framework\Services\Legacy\StatusColumn;

/**
 * NotFound\Framework\Models\Menu
 *
 * @property int $id
 * @property string $rights
 * @property object|null $properties
 * @property mixed $status
 * @property int $parent_id
 * @property string $url
 * @property int $template_id
 * @property int $type
 * @property int $language
 * @property int $enabled
 * @property int $order
 * @property string|null $link
 * @property int $attr
 * @property int|null $site_rights
 * @property string $updated
 * @property int $menu
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Menu> $children
 * @property-read int|null $children_count
 * @property-read Menu $parent
 * @property-read \NotFound\Framework\Models\Template|null $template
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu siteRoutes($site = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereAttr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereMenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereSiteRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUrl($value)
 *
 * @mixin \Eloquent
 */
class Menu extends LegacyModel
{
    protected $table = 'menu';

    protected $casts = [
        'properties' => 'object',
        'enabled' => 'boolean',
        'menu' => 'boolean',
    ];

    protected $fillable = [
        'id',
        'properties',
        'url',
        'enabled',
        'menu',
        'template_id',
        'title',
        'parent_id',
        'type',
        'attr',
        'order',
    ];

    protected $visible = [
        'id',
        'properties',
        'url',
        'enabled',
        'menu',
        'url',
        'template_id',
        'title',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->where('enabled', 1)->orderBy('order');
    }

    public function getParamsUrl(): string
    {
        $template = $this->template;
        if (! $template || ! isset($template->params)) {
            return '';
        }

        if (str_contains($template->params, ' ')) {
            Log::error("params: {$template->params} contains space");
        }

        $paramsArray = explode(',', $template->params);

        $paramsUrl = '';
        foreach ($paramsArray as $param) {
            if ($param !== '') {
                $paramsUrl .= '/{'.$param.'}';
            }
        }

        return $paramsUrl;
    }

    public function getTitle(Lang $lang = null): ?string
    {
        if (! $lang) {
            $lang = Lang::current();
        }

        $title = Strings::whereStringId($this->id)
            ->whereLangId($lang->id)
            ->whereName('name')
            ->whereTable('menu')
            ->first();

        if (isset($title)) {
            return $title->value;
        } else {
            return null;
        }
    }

    public function strings(int $langId)
    {
        return Strings::whereStringId($this->id)
            ->whereLangId($langId)
            ->whereTable(TemplateType::TEMPLATE);
    }

    public static function removeRouteCache()
    {
        // TODO: less brute force
        Cache::clear();
    }

    // Get all site routes
    public function scopeSiteRoutes($query, $site = null)
    {
        $builder = $query->select(['id', 'parent_id', 'url', 'template_id'])
            ->where('parent_id', 0)
            ->whereNot('template_id', 0)
            ->whereNotNull('template_id')
            ->where('enabled', 1)
            ->orderby('order');
        // ->when($site, function ($q) use ($site) {
        //     return $q->where('url', $site->url);
        // });

        return StatusColumn::wherePublished($builder, $this->getTable());
    }

    /**
     * getPath
     *
     * Get the relative URL from the root of the site
     *
     * @return void
     */
    public function getPath(): string
    {
        $res = explode('/', $this->url(), 4);

        return '/'.$res[3];
    }

    /**
     * url
     *
     * Get the full URL including scheme and domain
     *
     * @return void
     */
    public function url(): string
    {
        $url = '/';
        $menu = $this;
        while ($menu->parent_id !== 0) {
            $url = '/'.$menu->url.$url;
            $menu = $menu->parent;
        }

        return LaravelLocalization::localizeUrl($url);
    }

    /**
     * getSlug
     *
     * Returns the slug for the current page without the rest of the URL
     */
    public function getSlug(): string
    {
        return $this->url;
    }

    /**
     * getLocalizedPath
     *
     * Get the relative URL from the root of the site with explicit localization
     *
     * @return void
     */
    public function getLocalizedPath(): string
    {
        $res = explode('/', $this->url(), 4);

        return app()->getLocale().'/'.$res[3];
    }
}
