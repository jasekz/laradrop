<?php

namespace Jasekz\Laradrop;

use Illuminate\Support\Facades\Facade;

class LaradropFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laradrop';
    }
}
