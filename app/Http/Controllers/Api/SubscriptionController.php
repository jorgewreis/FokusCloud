<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PrefixedUlid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SubscriptionController extends Controller
{
    public function checkout(Request $request)
    {
        abort_unless($request->user()->email_verified_at, 403, 'Confirme o e-mail antes de assinar.');
        $accessToken = config('services.mercado_pago.access_token');
        abort_unless($accessToken, 503, 'O checkout ainda não foi configurado.');
        $data = $request->validate([
            'product_code' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.module_code' => ['required', 'string'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'cycle' => ['required', 'in:monthly,annual'],
        ]);
        $companyId = $request->session()->get('active_company_id');
        $product = DB::table('products')->where('code', $data['product_code'])->where('active', true)->first();
        abort_unless($product, 404, 'Produto não encontrado.');

        $subscription = DB::transaction(function () use ($companyId, $product, $data, $request) {
            $existing = DB::table('subscriptions')->where('company_id', $companyId)->where('product_id', $product->id)->whereIn('status', ['pendente', 'ativa', 'suspensa'])->lockForUpdate()->first();
            abort_if($existing, 409, 'Já existe uma assinatura não encerrada para este produto.');
            $id = PrefixedUlid::make('ASS');
            DB::table('subscriptions')->insert([
                'id' => $id, 'company_id' => $companyId, 'product_id' => $product->id, 'status' => 'pendente',
                'open_company_product' => $companyId.'-'.$product->id, 'version' => 1,
                'created_by' => $request->user()->id, 'updated_by' => $request->user()->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $amount = 0;
            foreach ($data['items'] as $requested) {
                $module = DB::table('modules')->where('product_id', $product->id)->where('code', $requested['module_code'])->first();
                abort_unless($module, 422, 'Módulo inválido para este produto.');
                $quantity = $requested['quantity'];
                $unit = (float) $module->monthly_price * ($data['cycle'] === 'annual' ? 9 : 1);
                $amount += $unit * $quantity;
                DB::table('subscription_items')->insert([
                    'id' => PrefixedUlid::make('ITM'), 'company_id' => $companyId, 'subscription_id' => $id,
                    'module_id' => $module->id, 'name_snapshot' => $module->name, 'quantity' => $quantity,
                    'unit_price_snapshot' => $unit, 'conditions_snapshot' => json_encode(['cycle' => $data['cycle']]),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $paymentId = PrefixedUlid::make('PAG');
            DB::table('payments')->insert([
                'id' => $paymentId, 'company_id' => $companyId, 'subscription_id' => $id, 'amount' => $amount,
                'currency' => 'BRL', 'status' => 'pendente', 'created_at' => now(), 'updated_at' => now(),
            ]);
            return compact('id', 'paymentId', 'amount');
        });

        $response = Http::withToken($accessToken)->post('https://api.mercadopago.com/checkout/preferences', [
            'external_reference' => $subscription['paymentId'],
            'items' => [['title' => 'Assinatura Fokus Cloud', 'quantity' => 1, 'currency_id' => 'BRL', 'unit_price' => $subscription['amount']]],
            'back_urls' => ['success' => config('app.url').'/admin/assinaturas?status=success', 'failure' => config('app.url').'/admin/assinaturas?status=failure'],
            'auto_return' => 'approved',
            'notification_url' => config('app.url').'/api/webhooks/mercado-pago',
        ])->throw()->json();
        DB::table('payments')->where('id', $subscription['paymentId'])->update(['provider_payload' => json_encode(['preference_id' => $response['id']]), 'updated_at' => now()]);
        return response()->json(['checkout_url' => $response['init_point'], 'subscription_id' => $subscription['id']], 201);
    }

    public function webhook(Request $request)
    {
        $paymentId = data_get($request->all(), 'data.id');
        if (! $paymentId) return response()->noContent();
        $accessToken = config('services.mercado_pago.access_token');
        $remote = Http::withToken($accessToken)->get("https://api.mercadopago.com/v1/payments/{$paymentId}")->throw()->json();
        $localId = $remote['external_reference'] ?? null;
        $status = match ($remote['status'] ?? '') { 'approved' => 'aprovado', 'rejected' => 'recusado', 'cancelled' => 'cancelado', default => 'pendente' };
        DB::transaction(function () use ($localId, $paymentId, $status, $remote) {
            $payment = DB::table('payments')->where('id', $localId)->lockForUpdate()->first();
            if (! $payment) return;
            if ($payment->provider_payment_id === (string) $paymentId && $payment->status === $status) return;
            DB::table('payments')->where('id', $payment->id)->update(['provider_payment_id' => (string) $paymentId, 'status' => $status, 'provider_payload' => json_encode($remote), 'updated_at' => now()]);
            if ($status === 'aprovado') DB::table('subscriptions')->where('id', $payment->subscription_id)->update(['status' => 'ativa', 'updated_at' => now()]);
        });
        return response()->noContent();
    }
}
