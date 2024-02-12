<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use NotFound\Framework\Traits\Exchangeable;

/**
 * NotFound\Framework\Models\Template
 *
 * @property int $id
 * @property string $rights
 * @property object|null $properties
 * @property int|null $order
 * @property string $name
 * @property string|null $desc
 * @property string|null $filename
 * @property string|null $allowedchildren
 * @property int|null $attr
 * @property string $params
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property int $enabled
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \NotFound\Framework\Models\TemplateItem> $items
 * @property-read int|null $items_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Template newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Template newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Template onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Template query()
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereAllowedchildren($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereAttr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereParams($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Template withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Template extends AssetModel
{
    use Exchangeable;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cms_template';

    protected $casts = [
        'properties' => 'object',
        'enabled' => 'boolean',
        'global' => 'boolean',
    ];

    protected $visible = [
        'id',
        'properties',
        'name',
        'filename',
    ];

    protected $fillable = [
        'name',
        'filename',
        'enabled',
        'params',
        'allow_children',
        'properties',
    ];

    public function items()
    {
        return $this->hasMany(TemplateItem::class, 'template');
    }

    public function getIdentifier()
    {
        return strtolower($this->attributes['filename']);
    }

    private function getSiteTableName()
    {
        return strtolower($this->filename);
    }
}
