<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformAdmin;
use App\Services\PlatformAudit;
use App\Services\PrefixedUlid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PlatformAuthController extends Controller
{
    public function login(Request $request, PlatformAudit $audit)
    {
        $data = $request->validate(['email' => ['required', 'email:rfc'], 'password' => ['required', 'string']]);
        $admin = PlatformAdmin::where('email', strtolower($data['email']))->first();
        if (! $admin || $admin->status !== 'ativo' || ! Hash::check($data['password'], $admin->password)) {
            $audit->record($admin?->id, 'backoffice.login_failed', request: $request);
            return response()->json(['message' => 'Credenciais internas inválidas.'], 422);
        }
        $code = (string) random_int(100000, 999999);
        DB::table('platform_login_challenges')->where('platform_admin_id', $admin->id)->whereNull('used_at')->update(['used_at' => now(), 'updated_at' => now()]);
        DB::table('platform_login_challenges')->insert([
            'id' => PrefixedUlid::make('MFA'), 'platform_admin_id' => $admin->id, 'code_hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(10), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $request->session()->put('platform_pending_admin_id', $admin->id);
        Mail::raw("Seu código de acesso ao backoffice Fokus Cloud é: {$code}. Ele expira em 10 minutos.", fn ($mail) => $mail->to($admin->email)->subject('Fokus Cloud: código de acesso interno'));
        $audit->record($admin->id, 'backoffice.mfa_requested', request: $request);
        return response()->json(['mfa_required' => true, 'message' => 'Enviamos um código de acesso ao seu e-mail.']);
    }

    public function verifyMfa(Request $request, PlatformAudit $audit)
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $adminId = $request->session()->get('platform_pending_admin_id');
        abort_unless($adminId, 401, 'Inicie o acesso interno novamente.');
        $challenge = DB::table('platform_login_challenges')->where('platform_admin_id', $adminId)->whereNull('used_at')->where('expires_at', '>', now())->latest('created_at')->first();
        if (! $challenge || ! hash_equals($challenge->code_hash, hash('sha256', $data['code']))) {
            $audit->record($adminId, 'backoffice.mfa_failed', request: $request);
            return response()->json(['message' => 'Código inválido ou expirado.'], 422);
        }
        DB::table('platform_login_challenges')->where('id', $challenge->id)->update(['used_at' => now(), 'updated_at' => now()]);
        $admin = PlatformAdmin::findOrFail($adminId);
        Auth::guard('platform')->login($admin);
        $request->session()->forget('platform_pending_admin_id');
        $request->session()->regenerate();
        $admin->forceFill(['last_login_at' => now()])->save();
        $audit->record($admin->id, 'backoffice.login_succeeded', request: $request);
        return response()->json(['admin' => ['id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email]]);
    }

    public function me(Request $request)
    {
        $admin = Auth::guard('platform')->user();
        abort_unless($admin, 401, 'Acesso interno não autenticado.');
        return response()->json(['admin' => ['id' => $admin->id, 'name' => $admin->name, 'email' => $admin->email]]);
    }

    public function logout(Request $request, PlatformAudit $audit)
    {
        $audit->record(Auth::guard('platform')->id(), 'backoffice.logout', request: $request);
        Auth::guard('platform')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->noContent();
    }
}
