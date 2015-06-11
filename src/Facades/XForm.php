<?php

namespace Axn\LaravelXForm\Facades;

use Illuminate\Support\Facades\Facade;

class XForm extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'xform';
    }
}
