<?php

namespace Surges\Jobpanel\Facades;

use Illuminate\Support\Facades\Facade;

class Supervisor extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Jobpanel.supervisor';
    }
}
