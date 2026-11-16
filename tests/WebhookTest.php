<?php

declare(strict_types=1);

namespace MercyPay\Laravel\Tests;

use Illuminate\Support\Facades\Event;
use MercyPay\Laravel\Events\WebhookReceived;
use MercyPay\Laravel\Facades\MercyPay;
use MercyPay\Laravel\MercyPayServiceProvider;
use MercyPay\MercyPayClient;
use Orchestra\Testbench\TestCase;
use RuntimeException;

final class WebhookTest extends TestCase
{
    private const SECRET = 'whsec_test';

    protected function getPackageProviders($app): array
    {
        return [MercyPayServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['MercyPay' => MercyPay::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('mercy-pay.api_key', 'mp_sandbox_test');
        $app['config']->set('mercy-pay.webhooks.secret', self::SECRET);
    }

    /**
     * @return array<string, string>
     */
    private function signed(string $payload, ?int $timestamp = null): array
    {
        $timestamp ??= time();

        return [
            'MercyPay-Signature' => "t={$timestamp},v1=".hash_hmac('sha256', "{$timestamp}.{$payload}", self::SECRET),
            'Content-Type' => 'application/json',
        ];
    }

    public function test_a_signed_webhook_fires_the_event(): void
    {
        Event::fake([WebhookReceived::class]);
        $payload = '{"id":"evt_1","object":"event","type":"payment.succeeded","data":{"id":"pay_1","metadata":{"order_id":"4821"}}}';

        $this->call('POST', '/webhooks/mercy-pay', [], [], [], $this->transformHeadersToServerVars($this->signed($payload)), $payload)
            ->assertOk()
            ->assertJson(['received' => true]);

        Event::assertDispatched(WebhookReceived::class, fn (WebhookReceived $webhook): bool => $webhook->id === 'evt_1'
            && $webhook->type === 'payment.succeeded'
            && $webhook->data()?->metadata['order_id'] === '4821');
    }

    public function test_a_wrong_or_old_signature_is_refused(): void
    {
        Event::fake([WebhookReceived::class]);
        $payload = '{"id":"evt_1","type":"payment.succeeded"}';

        $this->call('POST', '/webhooks/mercy-pay', [], [], [], $this->transformHeadersToServerVars($this->signed($payload.' ')), $payload)
            ->assertStatus(400);
        $this->call('POST', '/webhooks/mercy-pay', [], [], [], $this->transformHeadersToServerVars($this->signed($payload, time() - 600)), $payload)
            ->assertStatus(400);
        $this->postJson('/webhooks/mercy-pay', ['id' => 'evt_1'])->assertStatus(400);

        Event::assertNotDispatched(WebhookReceived::class);
    }

    public function test_the_client_comes_from_the_config(): void
    {
        self::assertInstanceOf(MercyPayClient::class, $this->app->make(MercyPayClient::class));
        self::assertSame($this->app->make(MercyPayClient::class)->payments, MercyPay::payments());
    }

    public function test_a_missing_key_says_what_to_add(): void
    {
        $this->app['config']->set('mercy-pay.api_key', null);
        $this->app->forgetInstance(MercyPayClient::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('MERCY_PAY_API_KEY');
        $this->app->make(MercyPayClient::class);
    }
}
