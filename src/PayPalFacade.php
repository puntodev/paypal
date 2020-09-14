<?php

namespace Puntodev\Payments;

use Illuminate\Support\Facades\Facade;

class PayPalFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'paypal';
    }
}
