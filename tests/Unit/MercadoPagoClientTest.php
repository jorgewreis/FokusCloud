<?php

namespace Tests\Unit;

use App\Services\MercadoPagoClient;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MercadoPagoClientTest extends TestCase
{
    public function test_client_sends_token_and_idempotency_key_and_sanitizes_payload(): void
    {
        Config::set('services.mercado_pago.access_token', 'sandbox-token');
        Http::fake(['https://api.mercadopago.com/v1/payments/*' => Http::response(['id' => 'pay-1'], 200)]);

        $client = app(MercadoPagoClient::class);
        $this->assertSame('pay-1', $client->getPayment('pay-1')['id']);
        $client->createRefund('pay-1', 10.50, 'refund-key');

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer sandbox-token') && $request->hasHeader('X-Idempotency-Key', 'refund-key'));
        $this->assertSame('[REDACTED]', $client->sanitizePayload(['access_token' => 'secret'])['access_token']);
    }
}
