# Mercy Pay pour Laravel

[Mercy Pay](https://mercy-pay.com) dans une app Laravel 12 ou 13 : la configuration, une façade, et une route qui reçoit les webhooks, vérifie leur signature et les transforme en événements Laravel.

```sh
composer require mercy-pay/laravel
```

Dans `.env` :

```dotenv
MERCY_PAY_API_KEY=mp_sandbox_...
MERCY_PAY_WEBHOOK_SECRET=whsec_...
```

## Encaisser un paiement

```php
use MercyPay\Laravel\Facades\MercyPay;

$session = MercyPay::checkoutSessions()->create([
    'amount' => 15000,
    'currency' => 'XAF',
    'description' => "Commande {$order->id}",
    'success_url' => route('orders.show', $order),
    'metadata' => ['order_id' => (string) $order->id],
], ['idempotency_key' => "order-{$order->id}"]);

return redirect()->away($session->checkout_url);
```

Vous pouvez aussi injecter `MercyPay\MercyPayClient` : `$mercyPay->payments->retrieve($id)`. Toutes les méthodes sont décrites dans [`mercy-pay/mercy-pay-php`](https://github.com/mercy-pay/mercy-pay-php).

## Recevoir les webhooks

Le paquet déclare `POST /webhooks/mercy-pay`. Donnez cette adresse à votre endpoint dans Développeurs > Webhooks. Chaque webhook dont la signature est bonne déclenche `MercyPay\Laravel\Events\WebhookReceived` ; les autres reçoivent un `400`.

```php
use Illuminate\Support\Facades\Event;
use MercyPay\Laravel\Events\WebhookReceived;

Event::listen(function (WebhookReceived $webhook) {
    match ($webhook->type) {
        'payment.succeeded' => Order::find($webhook->data()->metadata['order_id'])?->markAsPaid(),
        'payout.failed' => Notification::send(...),
        default => null,
    };
});
```

- Un même événement peut arriver deux fois : servez-vous de `$webhook->id` pour ne le traiter qu'une fois.
- La route répond `200` tout de suite. Mettez un travail long dans un listener en file d'attente (`ShouldQueue`).
- La route n'a pas le middleware `web` : pas de jeton CSRF à exclure.

Pour une autre adresse, `MERCY_PAY_WEBHOOK_PATH=paiements/webhook`. Pour déclarer la route vous-même, mettez `null` dans la config et ajoutez le middleware `MercyPay\Laravel\Http\Middleware\VerifyWebhookSignature`.

## En local

```sh
mercypay listen --forward-to localhost:8000/webhooks/mercy-pay
mercypay trigger payment.succeeded
```

La [CLI](https://docs.mercy-pay.com/developers/cli) affiche son secret de signature au démarrage : mettez-le dans `MERCY_PAY_WEBHOOK_SECRET` en local.

## Configuration

```sh
php artisan vendor:publish --tag=mercy-pay-config
```

`config/mercy-pay.php` règle aussi le délai d'attente, le nombre de nouvelles tentatives et la tolérance de signature.
