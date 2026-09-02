<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MercadoPagoClient
{
    private function request(): PendingRequest
    {
        $token = (string) config('services.mercado_pago.access_token');
        if ($token === '') {
            throw new RuntimeException('Mercado Pago não configurado.');
        }

        $request = Http::withToken($token)
            ->acceptJson()
            ->timeout((int) config('services.mercado_pago.timeout', 10));

        $scope = (string) config('services.mercado_pago.scope');
        if ($scope !== '') {
            $request = $request->withHeaders(['X-scope' => $scope]);
        }

        return $request;
    }

    public function createPreapproval(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->send('post', '/preapproval', $payload, $idempotencyKey);
    }

    public function getPreapproval(string $providerId): array
    {
        return $this->send('get', '/preapproval/'.rawurlencode($providerId));
    }

    public function updatePreapproval(string $providerId, array $payload, ?string $idempotencyKey = null): array
    {
        return $this->send('put', '/preapproval/'.rawurlencode($providerId), $payload, $idempotencyKey);
    }

    public function getPayment(string $providerId): array
    {
        return $this->send('get', '/v1/payments/'.rawurlencode($providerId));
    }

    public function getAuthorizedPayment(string $providerId): array
    {
        return $this->send('get', '/authorized_payments/'.rawurlencode($providerId));
    }

    public function searchAuthorizedPayments(string $providerSubscriptionId, int $limit = 50): array
    {
        return $this->send('get', '/authorized_payments/search', query: [
            'preapproval_id' => $providerSubscriptionId,
            'limit' => min(max($limit, 1), 100),
        ]);
    }

    public function createRefund(string $providerPaymentId, ?float $amount, string $idempotencyKey): array
    {
        $payload = $amount === null ? [] : ['amount' => round($amount, 2)];

        return $this->send(
            'post',
            '/v1/payments/'.rawurlencode($providerPaymentId).'/refunds',
            $payload,
            $idempotencyKey,
        );
    }

    public function getRefund(string $providerPaymentId, string $providerRefundId): array
    {
        return $this->send('get', '/v1/payments/'.rawurlencode($providerPaymentId).'/refunds/'.rawurlencode($providerRefundId));
    }

    public function listRefunds(string $providerPaymentId): array
    {
        return $this->send('get', '/v1/payments/'.rawurlencode($providerPaymentId).'/refunds');
    }

    public function sanitizePayload(mixed $payload): mixed
    {
        if (is_array($payload)) {
            $sanitized = [];
            foreach ($payload as $key => $value) {
                $normalizedKey = strtolower((string) $key);
                if (preg_match('/token|secret|password|card|security|cvv|cvc|raw_payload|authorization|signature/', $normalizedKey)) {
                    $sanitized[$key] = '[REDACTED]';
                    continue;
                }
                $sanitized[$key] = $this->sanitizePayload($value);
            }

            return $sanitized;
        }

        if (is_object($payload)) {
            return $this->sanitizePayload((array) $payload);
        }

        return $payload;
    }

    private function send(string $method, string $path, array $payload = [], ?string $idempotencyKey = null, array $query = []): array
    {
        $url = rtrim((string) config('services.mercado_pago.api_base_url', 'https://api.mercadopago.com'), '/').$path;
        $request = $this->request();
        if ($idempotencyKey !== null && $idempotencyKey !== '') {
            $request = $request->withHeaders(['X-Idempotency-Key' => $idempotencyKey]);
        }

        $response = $method === 'get'
            ? $request->get($url, $query ?: $payload)
            : $request->{$method}($url, $payload);

        return $response->throw()->json();
    }
}
