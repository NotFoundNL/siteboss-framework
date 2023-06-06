<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * NotFound\Framework\Models\CmsMenu
 *
 * @property int $id
 * @property string|null $properties
 * @property int $level
 * @property string|null $icon
 * @property string|null $title
 * @property string|null $target
 * @property string $to
 * @property string $rights
 * @property bool $enabled
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsMenu withoutTrashed()
 *
 * @mixin \Eloquent
 */
class CmsMenu extends BaseModel
{
    use SoftDeletes;

    protected $fillable = ['title', 'to', 'icon', 'level', 'rights', 'enabled', 'rights', 'order'];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    protected $table = 'cms_menu';
}
