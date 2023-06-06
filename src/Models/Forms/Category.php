<?php

namespace NotFound\Framework\Models\Forms;

use NotFound\Framework\Models\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * NotFound\Framework\Models\Forms\Category
 *
 * @property int $id
 * @property string|null $rights
 * @property string $name
 * @property string $slug
 * @property object|null $properties
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereRights($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Category withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Category extends BaseModel
{
    use SoftDeletes;

    protected $table = 'cms_form_categories';

    protected $visible = [
        'name',
        'properties',
        'slug',
    ];

    protected $casts = [
        'properties' => 'object',
    ];

    public function getCategoriesByRights()
    {
        /** @var Collection $categories */
        $categories = $this->get();

        $filteredCategories = $categories->filter(function ($category) {
            return Auth::user()->checkRights($category->rights);
        });

        return $filteredCategories->values();
    }
}
