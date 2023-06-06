<?php

namespace NotFound\Framework\Models\Forms;

use NotFound\Framework\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

//TODO: Think of a better name
/**
 * NotFound\Framework\Models\Forms\Data
 *
 * @property int $id
 * @property string $ip_address
 * @property string $timestamp
 * @property object|null $data
 * @property string $form_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Data newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Data newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Data onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Data query()
 * @method static \Illuminate\Database\Eloquent\Builder|Data whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Data whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Data whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Data whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Data whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Data whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Data whereTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Data whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Data withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Data withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Data extends BaseModel
{
    use SoftDeletes;

    protected $table = 'cms_form_data';

    protected $visible = [
        'id',
        'data',
        'timestamp',
        'form_id',
        // BUG: This seems site specific
        'company_profile',
    ];

    protected $casts = [
        'data' => 'object',
        'company_profile' => 'object',
    ];

    protected $fillable = ['form_id', 'ip_address', 'timestamp'];
}
