<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * NotFound\Framework\Models\Strings
 *
 * @property int $id
 * @property string $table
 * @property string $name
 * @property int $lang_id
 * @property int $string_id
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Strings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Strings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Strings query()
 * @method static \Illuminate\Database\Eloquent\Builder|Strings whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strings whereLangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strings whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strings whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strings whereTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strings whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Strings whereValue($value)
 *
 * @mixin \Eloquent
 */
class Strings extends Model
{
    protected $table = 'strings';

    protected $fillable = [
        'string_id',
        'table',
        'name',
        'lang_id',
        'value',
    ];
}
