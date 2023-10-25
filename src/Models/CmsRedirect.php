<?php

namespace NotFound\Framework\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class CmsRedirect extends BaseModel
{
    use SoftDeletes;

    protected $fillable = ['title', 'to', 'icon', 'level', 'rights', 'enabled', 'rights', 'order'];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    protected $table = 'cms_redirect';
}
