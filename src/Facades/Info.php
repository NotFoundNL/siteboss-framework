<?php

namespace NotFound\Framework\Facades;

use Illuminate\Support\Facades\Facade;

class Info extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pageinfo';
    }
}
