<?php

declare(strict_types=1);

namespace MercyPay\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use MercyPay\MercyPayObject;

/**
 * A verified webhook. Listen to it and look at $event->type:
 *
 *     Event::listen(function (WebhookReceived $webhook) {
 *         if ($webhook->type === 'payment.succeeded') { … }
 *     });
 *
 * The same event id can arrive more than once: make the handling idempotent with $webhook->id.
 */
final class WebhookReceived
{
    use Dispatchable;

    public readonly string $id;

    public readonly string $type;

    public function __construct(public readonly MercyPayObject $event)
    {
        $this->id = (string) $event->id;
        $this->type = (string) $event->type;
    }

    /** The object the event is about: a payment, a payout, a subscription… */
    public function data(): ?MercyPayObject
    {
        $data = $this->event->data;

        return $data instanceof MercyPayObject ? $data : null;
    }
}
