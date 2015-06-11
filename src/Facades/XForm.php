<?php

namespace Axn\LaravelXform;

use Illuminate\Support\Facades\Facade;

class XForm extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'xform';
    }
}
