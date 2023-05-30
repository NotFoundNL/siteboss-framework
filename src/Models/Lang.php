<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * NotFound\Framework\Models\Lang
 *
 * @property int $id
 * @property string $language
 * @property string $url
 * @property int $enabled
 * @property string|null $flag
 * @property int $order
 * @property int|null $default
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Lang newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lang newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lang query()
 * @method static \Illuminate\Database\Eloquent\Builder|Lang whereDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lang whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lang whereFlag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lang whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lang whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lang whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Lang whereUrl($value)
 *
 * @mixin \Eloquent
 */
class Lang extends Model
{
    protected $table = 'lang';

    public static $default = null;

    public static $current = null;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'language',
        'url',
        'flag',
        'enabled',
    ];

    protected $visible = [
        'id',
        'language',
        'url',
        'flag',
        'enabled',
        'default',
    ];

    public static function default()
    {
        return Cache::rememberForever('default_language', function () {
            return self::query()->whereDefault(1)->firstOrFail();
        });
    }

    public static function current()
    {
        if (is_null(self::$current)) {
            $locale = app()->getLocale();
            self::$current = self::query()->whereUrl($locale)->firstOrFail();
        }

        return self::$current;
    }

    public static function getSupportedLanguages()
    {
        return self::query()->whereEnabled(1)->whereIn('url', array_keys(LaravelLocalization::getSupportedLocales()))->get();
    }
}
