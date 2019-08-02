<?php

namespace Jobpanel\Facades;

use Illuminate\Support\Facades\Facade;

class JobpanelFailed extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Jobpanel.failed';
    }
}
