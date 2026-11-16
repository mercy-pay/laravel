<?php

declare(strict_types=1);

namespace MercyPay\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use MercyPay\MercyPayClient;

/**
 * MercyPay::payments()->create([...]), MercyPay::checkoutSessions()->retrieve($id)…
 *
 * @method static \MercyPay\Service\AccountService account()
 * @method static \MercyPay\Service\BillingPortalService billingPortal()
 * @method static \MercyPay\Service\CheckoutSessionService checkoutSessions()
 * @method static \MercyPay\Service\CustomerService customers()
 * @method static \MercyPay\Service\EventService events()
 * @method static \MercyPay\Service\FeeService fees()
 * @method static \MercyPay\Service\InvoiceService invoices()
 * @method static \MercyPay\Service\PaymentLinkService paymentLinks()
 * @method static \MercyPay\Service\PaymentService payments()
 * @method static \MercyPay\Service\PayoutService payouts()
 * @method static \MercyPay\Service\PlanService plans()
 * @method static \MercyPay\Service\ProviderStatusService providerStatus()
 * @method static \MercyPay\Service\RefundService refunds()
 * @method static \MercyPay\Service\RequestLogService requestLogs()
 * @method static \MercyPay\Service\SubscriptionService subscriptions()
 * @method static \MercyPay\Service\TestHelperService testHelpers()
 * @method static \MercyPay\Service\WalletService wallets()
 * @method static \MercyPay\Service\WalletTransferService walletTransfers()
 * @method static \MercyPay\Service\WebhookEndpointService webhookEndpoints()
 *
 * @see MercyPayClient
 */
final class MercyPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MercyPayClient::class;
    }

    /** The client itself. */
    public static function client(): MercyPayClient
    {
        return static::getFacadeRoot();
    }
}
