<?php

namespace NotFound\Framework\Models;

/**
 * NotFound\Framework\Models\FileUpload
 *
 * @property int $id
 * @property int|null $container_id
 * @property string|null $container_type
 * @property string|null $filename
 * @property string|null $mimetype
 * @property object|null $properties
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload query()
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload whereContainerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload whereContainerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload whereMimetype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|FileUpload withoutTrashed()
 *
 * @mixin \Eloquent
 */
class FileUpload extends BaseModel
{
    protected $table = 'cms_uploads';

    protected $fillable = ['container_id', 'container_type', 'filename', 'mimetype', 'properties'];

    protected $casts = [
        'properties' => 'object',
    ];
}
