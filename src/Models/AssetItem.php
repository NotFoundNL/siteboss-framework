<?php

namespace NotFound\Framework\Models;

/**
 * NotFound\Framework\Models\TableItem
 *
 * @property int $id
 * @property string $rights
 * @property int|null $table_id
 * @property string|null $type
 * @property string|null $internal
 * @property string|null $name
 * @property string|null $description
 * @property object $properties
 * @property int|null $enabled
 * @property int|null $order
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereInternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TableItem whereType($value)
 *
 * @mixin \Eloquent
 */
class AssetItem extends BaseModel
{
    protected $casts = [
        'properties' => 'object',
        'server_properties' => 'object',
    ];

    public function isInOverview(): bool
    {
        if (isset($this->properties->overview)) {
            return $this->properties->overview;
        }

        return false;
    }

    public function isSearchable(): bool
    {
        if (isset($this->properties?->searchable)) {
            return $this->properties->searchable;
        }

        return false;
    }
}
