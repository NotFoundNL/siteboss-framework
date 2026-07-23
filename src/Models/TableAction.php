<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use NotFound\Framework\Services\Assets\Enums\TableActionCondition;
use NotFound\Framework\Services\Assets\Enums\TableActionType;

/**
 * NotFound\Framework\Models\TableAction
 *
 * @property int $id
 * @property int $table_id
 * @property TableActionCondition $condition
 * @property int $days
 * @property TableActionType $action
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Table $cmsTable
 *
 * @mixin \Eloquent
 */
class TableAction extends Model
{
    protected $table = 'cms_table_actions';

    protected $fillable = ['table_id', 'condition', 'days', 'action'];

    protected $casts = [
        'condition' => TableActionCondition::class,
        'action' => TableActionType::class,
        'days' => 'integer',
    ];

    /**
     * Not named table(), as $table is the Eloquent table name property.
     */
    public function cmsTable(): BelongsTo
    {
        return $this->belongsTo(Table::class, 'table_id');
    }
}
