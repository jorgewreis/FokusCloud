<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PrefixedUlid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function registerCompany(Request $request)
    {
        $data = $request->validate([
            'document_type' => ['required', Rule::in(['cpf', 'cnpj'])],
            'document_number' => ['required', 'string'],
            'legal_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string', 'min:12'],
            'terms_version' => ['required', 'string', 'max:64'],
            'privacy_version' => ['required', 'string', 'max:64'],
        ]);

        $document = preg_replace('/\D/', '', $data['document_number']);
        $cpf = preg_replace('/\D/', '', $data['cpf']);
        abort_unless(($data['document_type'] === 'cpf' && strlen($document) === 11) || ($data['document_type'] === 'cnpj' && strlen($document) === 14), 422, 'Documento inválido.');
        abort_unless(strlen($cpf) === 11, 422, 'CPF inválido.');

        if (DB::table('companies')->where('document_type', $data['document_type'])->where('document_number', $document)->exists()) {
            return response()->json(['message' => 'Esta empresa já possui cadastro. Entre com sua conta para continuar.'], 409);
        }

        if (User::where('cpf', $cpf)->exists()) {
            return response()->json(['message' => 'Este CPF já possui conta. Entre para vinculá-la à nova empresa.', 'requires_login' => true], 409);
        }

        [$user, $company] = DB::transaction(function () use ($data, $cpf, $document) {
            $user = User::create([
                'id' => PrefixedUlid::make('USR'), 'name' => $data['name'], 'cpf' => $cpf,
                'email' => Str::lower($data['email']), 'password' => $data['password'], 'status' => 'pendente',
            ]);
            $companyId = PrefixedUlid::make('EMP');
            DB::table('companies')->insert([
                'id' => $companyId, 'document_type' => $data['document_type'], 'document_number' => $document,
                'legal_name' => $data['legal_name'], 'status' => 'pendente', 'version' => 1,
                'created_by' => $user->id, 'updated_by' => $user->id, 'created_at' => now(), 'updated_at' => now(),
            ]);
            $adminRole = DB::table('roles')->where('code', 'admin')->value('id');
            DB::table('company_memberships')->insert([
                'id' => PrefixedUlid::make('VNC'), 'company_id' => $companyId, 'user_id' => $user->id,
                'role_id' => $adminRole, 'status' => 'ativo', 'active_admin_company_id' => $companyId,
                'version' => 1, 'created_by' => $user->id, 'updated_by' => $user->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach (['terms' => $data['terms_version'], 'privacy' => $data['privacy_version']] as $type => $version) {
                DB::table('legal_acceptances')->insert([
                    'id' => PrefixedUlid::make('ACE'), 'user_id' => $user->id, 'document_type' => $type,
                    'document_version' => $version, 'accepted_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            return [$user, $companyId];
        });

        $this->sendToken($user, 'email_verification', '/verificar-email');
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('active_company_id', $company);

        return response()->json(['message' => 'Cadastro criado. Confirme seu e-mail antes de escolher a assinatura.', 'company_id' => $company], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['cpf' => ['required', 'string'], 'password' => ['required', 'string']]);
        $cpf = preg_replace('/\D/', '', $data['cpf']);
        $user = User::where('cpf', $cpf)->first();
        if (! $user || $user->status === 'desativada' || ($user->locked_until && $user->locked_until->isFuture()) || ! Hash::check($data['password'], $user->password)) {
            if ($user) $this->recordFailedLogin($user);
            return response()->json(['message' => 'CPF ou senha inválidos.'], 422);
        }
        $user->forceFill(['failed_login_attempts' => 0, 'login_attempt_window_started_at' => null, 'locked_until' => null])->save();
        Auth::login($user);
        $request->session()->regenerate();
        return response()->json(['user' => $this->userPayload($user), 'companies' => $this->companiesFor($user)]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->noContent();
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->userPayload($request->user()), 'companies' => $this->companiesFor($request->user()), 'active_company_id' => $request->session()->get('active_company_id')]);
    }

    public function selectCompany(Request $request)
    {
        $data = $request->validate(['company_id' => ['required', 'string', 'size:30']]);
        $membership = DB::table('company_memberships')->where('company_id', $data['company_id'])->where('user_id', $request->user()->id)->where('status', 'ativo')->first();
        abort_unless($membership, 403, 'Sem acesso a esta empresa.');
        $request->session()->put('active_company_id', $data['company_id']);
        return response()->json(['active_company_id' => $data['company_id']]);
    }

    public function verifyEmail(Request $request)
    {
        $token = $this->consumeToken($request->validate(['token' => ['required', 'string']])['token'], 'email_verification');
        $user = User::findOrFail($token->user_id);
        $user->forceFill(['email_verified_at' => now(), 'status' => 'ativa'])->save();
        DB::table('companies')->where('created_by', $user->id)->where('status', 'pendente')->update(['status' => 'ativa', 'updated_at' => now()]);
        Auth::login($user);
        $request->session()->regenerate();
        $companyId = DB::table('company_memberships')->where('user_id', $user->id)->where('status', 'ativo')->value('company_id');
        if ($companyId) $request->session()->put('active_company_id', $companyId);
        return response()->json(['message' => 'E-mail confirmado com sucesso.']);
    }

    public function resendVerification(Request $request)
    {
        $this->sendToken($request->user(), 'email_verification', '/verificar-email');
        return response()->json(['message' => 'Enviamos um novo link de confirmação.']);
    }

    public function requestPasswordReset(Request $request)
    {
        $data = $request->validate(['cpf' => ['required', 'string']]);
        $user = User::where('cpf', preg_replace('/\D/', '', $data['cpf']))->first();
        if ($user && $user->email_verified_at) {
            $this->sendToken($user, 'password_reset', '/criar-senha');
        }
        // Do not reveal whether a CPF exists.
        return response()->json(['message' => 'Se houver uma conta elegível, enviamos as instruções para o e-mail confirmado.']);
    }

    public function setPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);
        $token = $this->consumeToken($data['token'], ['password_reset', 'password_creation']);
        $user = User::findOrFail($token->user_id);
        $user->forceFill([
            'password' => $data['password'], 'status' => 'ativa',
            'email_verified_at' => $user->email_verified_at ?: now(),
        ])->save();
        if ($token->payload && ($membershipId = data_get(json_decode($token->payload, true), 'membership_id'))) {
            DB::table('company_memberships')->where('id', $membershipId)->where('user_id', $user->id)
                ->update(['status' => 'ativo', 'updated_at' => now()]);
        }
        return response()->json(['message' => 'Senha criada com sucesso. Agora você já pode entrar.']);
    }

    public function acceptMembership(Request $request)
    {
        $token = $this->consumeToken($request->validate(['token' => ['required', 'string']])['token'], 'membership_acceptance');
        $membershipId = data_get(json_decode($token->payload, true), 'membership_id');
        abort_unless($membershipId, 422, 'Convite inválido.');
        DB::table('company_memberships')->where('id', $membershipId)->where('user_id', $token->user_id)
            ->where('status', 'pendente')->update(['status' => 'ativo', 'updated_at' => now()]);
        return response()->json(['message' => 'Vínculo aceito. A empresa está disponível na sua conta.']);
    }

    public function acceptAdminTransfer(Request $request)
    {
        $token = $this->consumeToken($request->validate(['token' => ['required', 'string']])['token'], 'admin_transfer');
        $payload = json_decode($token->payload, true) ?: [];
        foreach (['company_id', 'from_membership_id', 'to_membership_id'] as $key) abort_unless(isset($payload[$key]), 422, 'Transferência inválida.');
        DB::transaction(function () use ($payload, $token) {
            $target = DB::table('company_memberships')->where('id', $payload['to_membership_id'])
                ->where('company_id', $payload['company_id'])->where('user_id', $token->user_id)->where('status', 'ativo')->lockForUpdate()->first();
            $from = DB::table('company_memberships')->where('id', $payload['from_membership_id'])
                ->where('company_id', $payload['company_id'])->whereNotNull('active_admin_company_id')->lockForUpdate()->first();
            abort_unless($target && $from, 422, 'A transferência não pode mais ser concluída.');
            DB::table('company_memberships')->where('id', $from->id)->update([
                'active_admin_company_id' => null,
                'status' => ! empty($payload['keep_previous_access']) ? 'ativo' : 'removido',
                'deleted_at' => ! empty($payload['keep_previous_access']) ? null : now(),
                'updated_by' => $token->user_id, 'updated_at' => now(), 'version' => $from->version + 1,
            ]);
            DB::table('company_memberships')->where('id', $target->id)->update([
                'role_id' => DB::table('roles')->where('code', 'admin')->value('id'),
                'active_admin_company_id' => $payload['company_id'], 'updated_by' => $token->user_id,
                'updated_at' => now(), 'version' => $target->version + 1,
            ]);
            DB::table('audit_events')->insert([
                'id' => PrefixedUlid::make('AUD'), 'company_id' => $payload['company_id'], 'actor_user_id' => $token->user_id,
                'entity_type' => 'company_membership', 'entity_id' => $target->id, 'operation' => 'update',
                'before_masked' => json_encode(['previous_membership_id' => $from->id]),
                'after_masked' => json_encode(['new_admin_membership_id' => $target->id, 'previous_access_kept' => (bool) ($payload['keep_previous_access'] ?? false)]),
                'expires_at' => now()->addDays(180), 'created_at' => now(),
            ]);
        });
        return response()->json(['message' => 'Administração transferida com sucesso.']);
    }

    public function sendToken(User $user, string $purpose, string $path, array $payload = []): void
    {
        $plain = Str::random(64);
        DB::table('security_tokens')->insert([
            'id' => PrefixedUlid::make('TKN'), 'user_id' => $user->id, 'purpose' => $purpose,
            'token_hash' => hash('sha256', $plain), 'payload' => $payload ?: null,
            'expires_at' => now()->addDay(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $url = rtrim(config('app.url'), '/').$path.'?token='.$plain;
        Mail::raw("Use este link para continuar: {$url}", fn ($mail) => $mail->to($user->email)->subject('Fokus Cloud: confirme seu acesso'));
    }

    private function consumeToken(string $plain, string|array $purpose): object
    {
        $token = DB::table('security_tokens')->where('token_hash', hash('sha256', $plain))->whereIn('purpose', (array) $purpose)->whereNull('used_at')->where('expires_at', '>', now())->first();
        abort_unless($token, 422, 'Link inválido ou expirado.');
        DB::table('security_tokens')->where('id', $token->id)->update(['used_at' => now(), 'updated_at' => now()]);
        return $token;
    }

    private function companiesFor(User $user): array
    {
        return DB::table('company_memberships as m')->join('companies as c', 'c.id', '=', 'm.company_id')->join('roles as r', 'r.id', '=', 'm.role_id')->where('m.user_id', $user->id)->where('m.status', 'ativo')->select('c.id', 'c.legal_name as name', 'r.code as role')->get()->all();
    }

    private function userPayload(User $user): array
    {
        return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'email_verified' => (bool) $user->email_verified_at, 'status' => $user->status];
    }

    private function recordFailedLogin(User $user): void
    {
        $windowStart = $user->login_attempt_window_started_at;
        $attempts = $windowStart && $windowStart->gt(now()->subMinutes(15)) ? $user->failed_login_attempts + 1 : 1;
        $user->forceFill(['failed_login_attempts' => $attempts, 'login_attempt_window_started_at' => now(), 'locked_until' => $attempts >= 5 ? now()->addMinutes(30) : null, 'status' => $attempts >= 5 ? 'bloqueada' : $user->status])->save();
    }
}
