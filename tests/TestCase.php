<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Puntodev\Payments\PayPalServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PayPalServiceProvider::class
        ];
    }
}
