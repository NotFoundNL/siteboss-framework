<?php

namespace NotFound\Framework\Models;

/**
 * NotFound\Framework\Models\CmsConfig
 *
 * @property int $id
 * @property int $type
 * @property string $visible
 * @property string $rights
 * @property int $site_id
 * @property string $name
 * @property string $code
 * @property string|null $value
 * @property string|null $description
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereSiteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig whereVisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsConfig withoutTrashed()
 *
 * @mixin \Eloquent
 */
class CmsConfig extends BaseModel
{
    protected $table = 'cms_config';

    public function hasJsonValue()
    {
        return $this->attributes['type'] === 2;
    }
}
