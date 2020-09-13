<?php

namespace Puntodev\Paypal;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Puntodev\Paypal\Skeleton\SkeletonClass
 */
class PaypalFacade extends Facade
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
