<?php

namespace Puntodev\Paypal\Tests;

use Orchestra\Testbench\TestCase;
use Puntodev\Paypal\PaypalServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [PaypalServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
