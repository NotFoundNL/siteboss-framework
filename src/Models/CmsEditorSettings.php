<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * NotFound\Framework\Models\CmsEditorSettings
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CmsEditorSettings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsEditorSettings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsEditorSettings onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsEditorSettings query()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsEditorSettings withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CmsEditorSettings withoutTrashed()
 *
 * @mixin \Eloquent
 */
class CmsEditorSettings extends BaseModel
{
    use SoftDeletes;

    protected $table = 'cms_editor_settings';
}
