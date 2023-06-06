<?php

namespace NotFound\Framework\Models\Forms;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use NotFound\Framework\Models\BaseModel;

/**
 * NotFound\Framework\Models\Forms\Form
 *
 * @property int $id
 * @property int|null $endpoint
 * @property int $category_id
 * @property string $name
 * @property string $type
 * @property object|null $success_text
 * @property string|null $notification_address
 * @property string|null $success_action
 * @property object|null $confirmation_mail
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $archived
 * @property int $draft
 * @property-read \NotFound\Framework\Models\Forms\Category|null $category
 *
 * @method static Builder|Form newModelQuery()
 * @method static Builder|Form newQuery()
 * @method static Builder|Form onlyTrashed()
 * @method static Builder|Form query()
 * @method static Builder|Form whereArchived($value)
 * @method static Builder|Form whereCategoryId($value)
 * @method static Builder|Form whereConfirmationMail($value)
 * @method static Builder|Form whereCreatedAt($value)
 * @method static Builder|Form whereDeletedAt($value)
 * @method static Builder|Form whereDraft($value)
 * @method static Builder|Form whereEndpoint($value)
 * @method static Builder|Form whereId($value)
 * @method static Builder|Form whereName($value)
 * @method static Builder|Form whereNotificationAddress($value)
 * @method static Builder|Form whereSuccessAction($value)
 * @method static Builder|Form whereSuccessText($value)
 * @method static Builder|Form whereType($value)
 * @method static Builder|Form whereUpdatedAt($value)
 * @method static Builder|Form withTrashed()
 * @method static Builder|Form withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Form extends BaseModel
{
    use SoftDeletes;

    protected $table = 'cms_form';

    protected $visible = [
        'id',
        'name',
        'type',
        'success_text',
        'notification_address',
        'confirmation_mail',
        'archived',
    ];

    protected $casts = [
        'success_text' => 'object',
        'confirmation_mail' => 'object',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function fields()
    {
        return $this->hasMany(Field::class, 'form_id', 'id');
    }

    public function whereTypeForm(): Builder
    {
        return $this->where('type', 'form');
    }

    public function whereTypeCombination(): Builder
    {
        return $this->where('type', 'combination');
    }

    public function getByCategory($categorySlug)
    {
        return $this->whereTypeForm()
            ->with(['category'])
            ->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            })
            ->where('archived', 0)
            ->orWhere('archived', null)
            ->orderBy('name', 'ASC')
            ->get(['id', 'name', 'type', 'notification_address', 'archived']);
    }

    public function getNotificationAddresses(): array
    {
        if (trim($this->attributes['notification_address']) == '') {
            return [];
        }

        return explode(',', $this->attributes['notification_address']);
    }
}
