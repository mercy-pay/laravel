# Journal des changements

## 0.2.0 (18 novembre 2026)

- S'appuie sur `mercy-pay/mercy-pay-php` 0.2 : paramètres décrits pour PhpStorm et PHPStan, catalogue `MercyPay\Catalog`, `waitForFinalStatus()` et numéros de test. **À vérifier** : les méthodes qui ont des champs obligatoires attendent maintenant leur tableau.

## 0.1.1 (16 novembre 2026)

- Laravel 12 et 13 seulement : Laravel 11 n'est plus maintenu et ses failles connues ne sont plus corrigées.

## 0.1.0 (16 novembre 2026)

- Première version : configuration par `.env`, façade `MercyPay`, route `POST /webhooks/mercy-pay` et événement `WebhookReceived`.
