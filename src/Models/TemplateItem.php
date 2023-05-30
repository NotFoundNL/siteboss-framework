<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * NotFound\Framework\Models\TemplateItem
 *
 * @property int $id
 * @property string|null $rights
 * @property string|null $internal
 * @property \NotFound\Framework\Models\Template|null $template
 * @property string|null $type
 * @property string|null $name
 * @property string|null $description
 * @property object|null $properties
 * @property int|null $order
 * @property int|null $enabled
 * @property int|null $global
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property object|null $server_properties
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereGlobal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereServerProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|TemplateItem withoutTrashed()
 *
 * @mixin \Eloquent
 */
class TemplateItem extends AssetItem
{
    use SoftDeletes;

    protected $table = 'cms_templateitem';

    protected $visible = [
        'id',
        'internal',
        'type',
        'properties',
        'enabled',
        'global',
        'string',
    ];

    protected $fillable = [
        'name',
        'type',
        'internal',
        'order',
        'enabled',
        'global',
    ];

    protected $casts = [
        'properties' => 'object',
        'server_properties' => 'object',
        'enabled' => 'boolean',
        'global' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function string()
    {
        return $this->hasOne(Strings::class, 'name', 'internal')->ofMany([], function (Builder $query) {
            $query->where('lang_id', Lang::current()->id);
            $query->orWhere('lang_id', 0);
        });
    }
}
