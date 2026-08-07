<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PrefixedUlid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    private const USAGE = [
        'oficios' => ['options' => [2500, 5000, 10000, 20000, 50000], 'step' => 2],
        'partes' => ['options' => [5000, 10000, 20000, 50000, 100000], 'step' => 4],
        'pessoas' => ['options' => [50, 250, 1000, 5000, 10000], 'step' => 4],
        'empreendimentos' => ['options' => [20, 50, 100, 500, 1000], 'step' => 4],
        'imoveis' => ['options' => [200, 500, 1000, 5000, 10000], 'step' => 4],
        'relatorios' => ['options' => [500, 1000, 2000, 5000, 10000], 'step' => 4],
    ];

    private const PLANS = [
        'law' => [
            'Inicial' => ['oficios', 'partes', 'processos'],
            'Profissional' => ['oficios', 'partes', 'processos', 'cartas-exp', 'cartas-rec', 'editais', 'guias'],
            'Premium' => ['oficios', 'partes', 'processos', 'cartas-exp', 'cartas-rec', 'editais', 'guias', 'audiencias', 'monitoramento', 'medidas'],
        ],
        'lead' => [
            'Start' => ['pessoas', 'imoveis'],
            'Growth' => ['pessoas', 'imoveis', 'funil', 'empreendimentos', 'website'],
            'Scale' => ['pessoas', 'empreendimentos', 'imoveis', 'funil', 'relatorios', 'whatsapp', 'website'],
        ],
    ];

    public function index(Request $request)
    {
        $companyId = $request->attributes->get('active_company_id');
        $subscriptions = DB::table('subscriptions as subscription')->join('products as product', 'product.id', '=', 'subscription.product_id')
            ->where('subscription.company_id', $companyId)
            ->select('subscription.id', 'subscription.status', 'subscription.created_at', 'product.code as product_code', 'product.name as product_name')
            ->orderByDesc('subscription.created_at')->get();
        foreach ($subscriptions as $subscription) {
            $subscription->items = DB::table('subscription_items')->where('company_id', $companyId)->where('subscription_id', $subscription->id)
                ->select('name_snapshot as name', 'quantity', 'unit_price_snapshot as unit_price', 'conditions_snapshot')->get();
            $subscription->payment = DB::table('payments')->where('company_id', $companyId)->where('subscription_id', $subscription->id)
                ->latest('created_at')->select('status', 'amount', 'currency', 'created_at')->first();
        }
        return response()->json($subscriptions);
    }

    public function checkout(Request $request)
    {
        abort_unless($request->user()->email_verified_at, 403, 'Confirme o e-mail antes de assinar.');
        $accessToken = config('services.mercado_pago.access_token');
        abort_unless($accessToken, 503, 'O checkout ainda não foi configurado.');
        $data = $request->validate([
            'product_code' => ['required', Rule::in(['law', 'lead'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.module_code' => ['required', 'string', 'max:64'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.usage_limit' => ['nullable', 'integer', 'min:1'],
            'cycle' => ['required', Rule::in(['monthly', 'annual'])],
            'selection_mode' => ['required', Rule::in(['modules', 'plan'])],
            'plan_code' => ['nullable', 'string', 'max:64'],
        ]);
        $companyId = $request->attributes->get('active_company_id');
        $product = DB::table('products')->where('code', $data['product_code'])->where('active', true)->first();
        abort_unless($product, 404, 'Produto não encontrado.');
        $quoted = $this->quote($product, $data);
        $subscriptionId = PrefixedUlid::make('ASS');
        $paymentId = PrefixedUlid::make('PAG');

        try {
            $response = Http::withToken($accessToken)->timeout(15)->post('https://api.mercadopago.com/checkout/preferences', [
                'external_reference' => $paymentId,
                'items' => [['title' => "Assinatura {$product->name}", 'quantity' => 1, 'currency_id' => 'BRL', 'unit_price' => $quoted['amount']]],
                'back_urls' => [
                    'success' => rtrim(config('app.url'), '/').'/admin/assinaturas?status=success',
                    'failure' => rtrim(config('app.url'), '/').'/admin/assinaturas?status=failure',
                    'pending' => rtrim(config('app.url'), '/').'/admin/assinaturas?status=pending',
                ],
                'auto_return' => 'approved',
                'notification_url' => rtrim(config('app.url'), '/').'/api/webhooks/mercado-pago',
            ])->throw()->json();
        } catch (\Throwable) {
            return response()->json(['message' => 'Não foi possível iniciar o checkout. Nenhuma assinatura foi criada; tente novamente.'], 502);
        }

        DB::transaction(function () use ($companyId, $product, $quoted, $request, $subscriptionId, $paymentId, $response) {
            $existing = DB::table('subscriptions')->where('company_id', $companyId)->where('product_id', $product->id)
                ->whereIn('status', ['pendente', 'ativa', 'suspensa'])->lockForUpdate()->first();
            abort_if($existing, 409, 'Já existe uma assinatura não encerrada para este produto.');
            DB::table('subscriptions')->insert([
                'id' => $subscriptionId, 'company_id' => $companyId, 'product_id' => $product->id, 'status' => 'pendente',
                'open_company_product' => $companyId.'-'.$product->id, 'version' => 1,
                'created_by' => $request->user()->id, 'updated_by' => $request->user()->id, 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($quoted['items'] as $item) {
                DB::table('subscription_items')->insert([
                    'id' => PrefixedUlid::make('ITM'), 'company_id' => $companyId, 'subscription_id' => $subscriptionId,
                    'module_id' => $item['module']->id, 'name_snapshot' => $item['module']->name, 'quantity' => $item['quantity'],
                    'unit_price_snapshot' => $item['unit_price'], 'conditions_snapshot' => json_encode($item['conditions']),
                    'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            DB::table('payments')->insert([
                'id' => $paymentId, 'company_id' => $companyId, 'subscription_id' => $subscriptionId, 'amount' => $quoted['amount'],
                'currency' => 'BRL', 'status' => 'pendente', 'provider_payload' => json_encode(['preference_id' => $response['id'] ?? null]),
                'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        });

        return response()->json(['checkout_url' => $response['init_point'], 'subscription_id' => $subscriptionId, 'amount' => $quoted['amount']], 201);
    }

    public function webhook(Request $request)
    {
        $this->assertWebhookSignature($request);
        $providerPaymentId = data_get($request->all(), 'data.id');
        if (! $providerPaymentId) {
            return response()->noContent();
        }
        $accessToken = config('services.mercado_pago.access_token');
        abort_unless($accessToken, 503, 'Webhook não configurado.');
        $remote = Http::withToken($accessToken)->timeout(15)->get("https://api.mercadopago.com/v1/payments/{$providerPaymentId}")->throw()->json();
        $localId = $remote['external_reference'] ?? null;
        $status = match ($remote['status'] ?? '') {
            'approved' => 'aprovado',
            'rejected' => 'recusado',
            'cancelled' => 'cancelado',
            default => 'pendente',
        };
        DB::transaction(function () use ($localId, $providerPaymentId, $status, $remote) {
            $payment = DB::table('payments')->where('id', $localId)->lockForUpdate()->first();
            if (! $payment || ($payment->provider_payment_id === (string) $providerPaymentId && $payment->status === $status)) {
                return;
            }
            DB::table('payments')->where('id', $payment->id)->update([
                'provider_payment_id' => (string) $providerPaymentId, 'status' => $status,
                'provider_payload' => json_encode($remote), 'updated_at' => now(), 'version' => DB::raw('version + 1'),
            ]);
            if ($status === 'aprovado') {
                DB::table('subscriptions')->where('id', $payment->subscription_id)->where('company_id', $payment->company_id)
                    ->update(['status' => 'ativa', 'updated_at' => now(), 'version' => DB::raw('version + 1')]);
            }
        });
        return response()->noContent();
    }

    private function quote(object $product, array $data): array
    {
        $codes = array_column($data['items'], 'module_code');
        abort_if(count($codes) !== count(array_unique($codes)), 422, 'Um módulo só pode ser informado uma vez.');
        if ($data['selection_mode'] === 'plan') {
            $plan = self::PLANS[$product->code][$data['plan_code'] ?? ''] ?? null;
            abort_unless($plan && $this->sameModules($codes, $plan), 422, 'Os módulos não correspondem ao plano informado.');
        }
        $modules = DB::table('modules')->where('product_id', $product->id)->whereIn('code', $codes)->get()->keyBy('code');
        abort_unless($modules->count() === count($codes), 422, 'Módulo inválido para este produto.');
        $monthly = 0.0;
        $items = [];
        foreach ($data['items'] as $requested) {
            $module = $modules[$requested['module_code']];
            $usage = self::USAGE[$requested['module_code']] ?? null;
            $usageLimit = $requested['usage_limit'] ?? ($usage['options'][0] ?? null);
            if ($usage) {
                abort_unless(in_array($usageLimit, $usage['options'], true), 422, 'Limite de utilização inválido.');
            } else {
                abort_if(isset($requested['usage_limit']), 422, 'Este módulo não possui limite configurável.');
            }
            $unit = (float) $module->monthly_price + ($usage ? array_search($usageLimit, $usage['options'], true) * $usage['step'] : 0);
            $monthly += $unit * $requested['quantity'];
            $items[] = ['module' => $module, 'quantity' => $requested['quantity'], 'unit_price' => $data['cycle'] === 'annual' ? $unit * 9 : $unit, 'conditions' => ['cycle' => $data['cycle'], 'usage_limit' => $usageLimit, 'selection_mode' => $data['selection_mode'], 'plan_code' => $data['plan_code'] ?? null]];
        }
        if ($data['selection_mode'] === 'plan') {
            $monthly *= 0.9;
            foreach ($items as &$item) {
                $item['unit_price'] *= 0.9;
            }
        }
        $amount = $data['cycle'] === 'annual' ? $monthly * 9 : $monthly;
        return ['items' => $items, 'amount' => round($amount, 2)];
    }

    private function sameModules(array $actual, array $expected): bool
    {
        sort($actual);
        sort($expected);
        return $actual === $expected;
    }

    private function assertWebhookSignature(Request $request): void
    {
        $secret = config('services.mercado_pago.webhook_secret');
        abort_unless($secret, 503, 'Assinatura do webhook não configurada.');
        $signature = (string) $request->header('x-signature');
        $requestId = (string) $request->header('x-request-id');
        preg_match('/(?:^|,)\s*ts=([^,]+)/', $signature, $timestamp);
        preg_match('/(?:^|,)\s*v1=([^,]+)/', $signature, $digest);
        $dataId = (string) data_get($request->all(), 'data.id');
        abort_unless($dataId && ! empty($timestamp[1]) && ! empty($digest[1]), 401, 'Assinatura de webhook inválida.');
        $manifest = "id:{$dataId};request-id:{$requestId};ts:{$timestamp[1]};";
        $expected = hash_hmac('sha256', $manifest, $secret);
        abort_unless(hash_equals($expected, $digest[1]), 401, 'Assinatura de webhook inválida.');
    }
}
