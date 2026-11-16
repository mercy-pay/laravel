<?php

declare(strict_types=1);

namespace MercyPay\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MercyPay\Laravel\Events\WebhookReceived;
use MercyPay\MercyPayObject;

/**
 * Receives a webhook already verified by VerifyWebhookSignature, fires WebhookReceived, and
 * answers 200 so Mercy Pay does not send it again. Do slow work in a queued listener.
 */
final class WebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (! is_array($payload) || ! isset($payload['type'])) {
            return response()->json(['error' => 'Événement illisible.'], 400);
        }

        WebhookReceived::dispatch(MercyPayObject::from($payload));

        return response()->json(['received' => true]);
    }
}
