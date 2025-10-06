<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\Model;
use NotFound\Framework\Models\Scopes\EnabledScope;

/**
 * NotFound\Framework\Models\CmsSite
 *
 * @property int $id
 * @property int|null $index
 * @property string|null $name
 * @property int $root
 * @property string|null $properties
 * @property int $enabled
 * @property int|null $order
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite query()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite whereIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite whereRoot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsSite withoutTrashed()
 *
 * @mixin \Eloquent
 */
class CmsSite extends Model
{
    protected $table = 'cms_site';

    protected static function booted()
    {
        static::addGlobalScope(new EnabledScope);
    }
}
