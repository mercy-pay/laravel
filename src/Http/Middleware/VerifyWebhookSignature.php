<?php

declare(strict_types=1);

namespace MercyPay\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercyPay\Exception\SignatureVerificationException;
use MercyPay\Webhook;
use Symfony\Component\HttpFoundation\Response;

/**
 * Refuses, with a 400, any request whose MercyPay-Signature does not match the configured
 * secret. Put it on your own route if you set mercy-pay.webhooks.path to null.
 */
final class VerifyWebhookSignature
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('mercy-pay.webhooks.secret');

        if (! is_string($secret) || $secret === '') {
            Log::error('Mercy Pay : webhook refusé, MERCY_PAY_WEBHOOK_SECRET est vide.');

            return response()->json(['error' => 'Secret de webhook non configuré.'], 500);
        }

        try {
            Webhook::verify(
                $request->getContent(),
                $request->header('MercyPay-Signature'),
                $secret,
                (int) config('mercy-pay.webhooks.tolerance', Webhook::DEFAULT_TOLERANCE),
            );
        } catch (SignatureVerificationException $exception) {
            return response()->json(['error' => $exception->getMessage()], 400);
        }

        return $next($request);
    }
}
