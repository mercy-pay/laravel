<?php

return [

    /*
    | Votre clé API : mp_sandbox_… pour tester, mp_live_… pour l'argent réel.
    | Développeurs > Clés API dans le tableau de bord Mercy Pay.
    */
    'api_key' => env('MERCY_PAY_API_KEY'),

    'webhooks' => [

        /*
        | Le secret de signature de votre endpoint (whsec_…), ou celui affiché par
        | `mercypay listen` en local.
        */
        'secret' => env('MERCY_PAY_WEBHOOK_SECRET'),

        /*
        | L'adresse où Mercy Pay envoie les webhooks. null pour ne pas déclarer de route et
        | brancher vous-même le middleware VerifyWebhookSignature.
        */
        'path' => env('MERCY_PAY_WEBHOOK_PATH', 'webhooks/mercy-pay'),

        /* Écart accepté, en secondes, entre la signature et maintenant. */
        'tolerance' => 300,
    ],

    'timeout' => 30,

    'max_retries' => 2,

    /* Seulement pour viser un autre serveur que https://mercy-pay.com. */
    'base_url' => env('MERCY_PAY_BASE_URL'),
];
