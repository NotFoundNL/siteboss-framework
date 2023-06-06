<?php

namespace NotFound\Framework\Models\Forms;

use Illuminate\Database\Eloquent\SoftDeletes;
use NotFound\Framework\Models\BaseModel;

/**
 * NotFound\Framework\Models\Forms\Filetype
 *
 * @property int $id
 * @property string $name
 * @property string|null $display_name
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Filetype newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Filetype newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Filetype onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Filetype query()
 * @method static \Illuminate\Database\Eloquent\Builder|Filetype whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filetype whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filetype whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filetype whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filetype withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Filetype withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Filetype extends BaseModel
{
    use SoftDeletes;

    protected $table = 'cms_form_filetypes';

    protected $visible = [
        'id',
        'name',
        'display_name',
    ];
}
