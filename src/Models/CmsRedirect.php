<?php

declare(strict_types=1);

namespace NotFound\Framework\Models;

class CmsRedirect extends BaseModel
{
    protected $casts = [
        'enabled' => 'boolean',
        'recursive' => 'boolean',
        'rewrite' => 'boolean',
    ];
}
