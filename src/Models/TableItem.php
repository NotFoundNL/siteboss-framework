<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * NotFound\Framework\Models\TableItem
 *
 * @property int $id
 * @property string|null $rights
 * @property int|null $table_id
 * @property string|null $type
 * @property string|null $internal
 * @property string|null $name
 * @property string|null $description
 * @property object|null $properties
 * @property object|null $server_properties
 * @property int $enabled
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \NotFound\Framework\Models\Table|null $table
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereServerProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem withoutTrashed()
 *
 * @mixin \Eloquent
 */
class TableItem extends AssetItem
{
    use SoftDeletes;

    protected $table = 'cms_tableitem';

    protected $visible = [
        'id',
        'internal',
        'type',
        'properties',
        'server_properties',
    ];

    protected $fillable = [
        'name',
        'type',
        'internal',
        'order',
        'properties',
        'server_properties',
        'global',
        'rights',
        'description',
        'enabled',
    ];

    protected $casts = [
        'properties' => 'object', 'server_properties' => 'object',
    ];

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
