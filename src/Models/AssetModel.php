<?php

namespace NotFound\Framework\Models;

/**
 * NotFound\Framework\Models\Table
 *
 * @property int $id
 * @property string|null $comments
 * @property string $rights
 * @property int|null $position
 * @property mixed $status
 * @property string|null $url
 * @property string|null $table
 * @property string|null $name
 * @property object $properties
 * @property int $enabled
 * @property-read \Illuminate\Database\Eloquent\Collection|\NotFound\Framework\Models\TableItem[] $tableItems
 * @property-read int|null $table_items_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Table newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Table newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Table query()
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Table whereUrl($value)
 *
 * @mixin \Eloquent
 */
abstract class AssetModel extends BaseModel
{
    protected $casts = [
        'properties' => 'object',
    ];

    abstract public function items();

    abstract public function getIdentifier();
}
