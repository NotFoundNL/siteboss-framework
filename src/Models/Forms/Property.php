<?php

namespace NotFound\Framework\Models\Forms;

use Illuminate\Database\Eloquent\SoftDeletes;
use NotFound\Framework\Models\BaseModel;
use NotFound\Framework\Services\Forms\Fields\FactoryType;

/**
 * NotFound\Framework\Models\Forms\Property
 *
 * @property int $id
 * @property int $custom
 * @property string $name
 * @property string $type
 * @property object|null $options
 * @property int|null $combinationId
 * @property string|null $has_value
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Property newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Property newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Property onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Property query()
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereCombinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereHasValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Property withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Property extends BaseModel
{
    use SoftDeletes;

    protected $table = 'cms_form_properties';

    protected $visible = [
        'id',
        'custom',
        'name',
        'type',
        'options',
        'has_value',
        'combinationId',
    ];

    protected $casts = [
        'options' => 'object',
    ];

    public function getWithCustomCombinations()
    {
        $typeFactory = new FactoryType();
        $properties = [];
        foreach ($this->get() as $property) {
            $type = $typeFactory->getByType($property->type, $property->options, 4);
            $property->{'options'} = $type->getOptions($property->options);

            $properties[] = $property;
        }

        // Add the combinations
        $customProperties = Form::whereType('Combination')->where('archived', 0)->get(['id', 'name', 'type']);
        foreach ($customProperties as $property) {
            /** @var Form $property */

            // Converted to array otherwise the visible properties won't
            // allow for customer variables
            $customProperty = $property->toArray();
            $customProperty['custom'] = 1;
            $customProperty['type'] = $property->name;
            $customProperty['combinationId'] = $property->id;

            $properties[] = $customProperty;
        }

        return $properties;
    }
}
