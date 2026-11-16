<?php

declare(strict_types=1);

namespace MercyPay\Laravel;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use MercyPay\Laravel\Http\Controllers\WebhookController;
use MercyPay\Laravel\Http\Middleware\VerifyWebhookSignature;
use MercyPay\MercyPayClient;
use RuntimeException;

final class MercyPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mercy-pay.php', 'mercy-pay');

        $this->app->singleton(MercyPayClient::class, function (Application $app): MercyPayClient {
            $config = $app['config']->get('mercy-pay');

            if (! is_string($config['api_key'] ?? null) || $config['api_key'] === '') {
                throw new RuntimeException('Mercy Pay : ajoutez MERCY_PAY_API_KEY à votre fichier .env.');
            }

            return new MercyPayClient($config['api_key'], array_filter([
                'base_url' => $config['base_url'] ?? null,
                'timeout' => $config['timeout'] ?? null,
                'max_retries' => $config['max_retries'] ?? null,
            ], fn (mixed $value): bool => $value !== null && $value !== ''));
        });

        $this->app->alias(MercyPayClient::class, 'mercy-pay');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/mercy-pay.php' => config_path('mercy-pay.php')], 'mercy-pay-config');
        }

        $path = $this->app['config']->get('mercy-pay.webhooks.path');

        if (is_string($path) && $path !== '' && ! $this->app->routesAreCached()) {
            Route::post($path, WebhookController::class)
                ->middleware(VerifyWebhookSignature::class)
                ->name('mercy-pay.webhook');
        }
    }
}
