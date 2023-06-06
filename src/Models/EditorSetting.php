<?php

namespace NotFound\Framework\Models;

/**
 * NotFound\Framework\Models\EditorSetting
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $settings
 * @property int|null $enabled
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EditorSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EditorSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EditorSetting onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EditorSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|EditorSetting whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EditorSetting whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EditorSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EditorSetting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EditorSetting whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EditorSetting withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EditorSetting withoutTrashed()
 *
 * @mixin \Eloquent
 */
class EditorSetting extends BaseModel
{
    protected $table = 'cms_editor_settings';

    protected $visible = ['name', 'settings'];
}
