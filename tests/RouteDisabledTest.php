<?php

declare(strict_types=1);

namespace MercyPay\Laravel\Tests;

use MercyPay\Laravel\MercyPayServiceProvider;
use Orchestra\Testbench\TestCase;

final class RouteDisabledTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [MercyPayServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('mercy-pay.webhooks.path', null);
    }

    public function test_no_route_when_the_path_is_null(): void
    {
        $this->post('/webhooks/mercy-pay')->assertNotFound();
    }
}
