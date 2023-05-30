<?php

namespace NotFound\Framework\Models;

/**
 * NotFound\Framework\Models\CmsContentBlocks
 *
 * @property int $id
 * @property string $asset_type
 * @property int $source_asset_item_id
 * @property int|null $source_record_id
 * @property int $target_table_id
 * @property int $target_record_id
 * @property int $lang_id
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \NotFound\Framework\Models\Lang|null $lang
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks query()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks whereAssetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks whereLangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks whereSourceAssetItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks whereSourceRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks whereTargetRecordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks whereTargetTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsContentBlocks withoutTrashed()
 *
 * @mixin \Eloquent
 */
class CmsContentBlocks extends BaseModel
{
    protected $table = 'cms_content_blocks';

    protected $fillable = [
        'asset_type',
        'source_asset_item_id',
        'source_record_id',
        'target_table_id',
        'target_record_id',
        'lang_id',
        'order',
    ];

    public function lang()
    {
        return $this->hasOne(Lang::class);
    }
}
