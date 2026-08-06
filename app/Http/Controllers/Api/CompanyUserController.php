<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PrefixedUlid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyUserController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(DB::table('company_memberships as membership')
            ->join('users', 'users.id', '=', 'membership.user_id')
            ->join('roles', 'roles.id', '=', 'membership.role_id')
            ->where('membership.company_id', $request->attributes->get('active_company_id'))
            ->whereNull('membership.deleted_at')
            ->select('membership.id', 'membership.status', 'membership.version', 'users.name', 'users.cpf', 'users.email', 'users.email_verified_at', 'roles.code as role')
            ->orderBy('users.name')->get());
    }

    public function store(Request $request, AuthController $auth)
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'role' => ['required', Rule::in(['gestor', 'usuario'])],
        ]);
        $cpf = preg_replace('/\D/', '', $data['cpf']);
        abort_unless(strlen($cpf) === 11, 422, 'CPF inválido.');
        $companyId = $request->attributes->get('active_company_id');
        $actor = $request->user();

        $membership = DB::transaction(function () use ($data, $cpf, $companyId, $actor, $auth) {
            $user = User::where('cpf', $cpf)->first();
            $existing = (bool) $user;
            if (! $user) {
                $user = User::create([
                    'id' => PrefixedUlid::make('USR'), 'name' => $data['name'], 'cpf' => $cpf,
                    'email' => Str::lower($data['email']), 'password' => Str::random(48), 'status' => 'pendente',
                ]);
            }
            $roleId = DB::table('roles')->where('code', $data['role'])->value('id');
            abort_unless($roleId, 422, 'Perfil inválido.');
            abort_if(DB::table('company_memberships')->where('company_id', $companyId)->where('user_id', $user->id)->exists(), 409, 'Esta pessoa já possui vínculo com a empresa.');
            $id = PrefixedUlid::make('VNC');
            DB::table('company_memberships')->insert([
                'id' => $id, 'company_id' => $companyId, 'user_id' => $user->id, 'role_id' => $roleId,
                'status' => 'pendente', 'version' => 1, 'created_by' => $actor->id, 'updated_by' => $actor->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('company_invitations')->insert([
                'id' => PrefixedUlid::make('CNV'), 'company_id' => $companyId, 'membership_id' => $id,
                'created_by' => $actor->id, 'expires_at' => now()->addDay(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            $auth->sendToken($user, $existing ? 'membership_acceptance' : 'password_creation', $existing ? '/auth/aceitar-vinculo.html' : '/auth/criar-senha.html', ['membership_id' => $id]);
            return $id;
        });
        return response()->json(['message' => 'Convite enviado para o e-mail informado.', 'membership_id' => $membership], 201);
    }

    public function update(Request $request, string $membership)
    {
        $this->adminOnly($request);
        $data = $request->validate([
            'role' => ['nullable', Rule::in(['gestor', 'usuario'])],
            'status' => ['nullable', Rule::in(['suspenso', 'removido'])],
            'version' => ['required', 'integer', 'min:1'],
        ]);
        abort_if(empty($data['role']) && empty($data['status']), 422, 'Informe uma alteração.');
        $current = DB::table('company_memberships')->where('id', $membership)
            ->where('company_id', $request->attributes->get('active_company_id'))->first();
        abort_unless($current, 404, 'Vínculo não encontrado.');
        abort_if($current->active_admin_company_id, 422, 'Use a transferência formal para alterar o administrador.');
        $changes = ['updated_by' => $request->user()->id, 'updated_at' => now(), 'version' => $current->version + 1];
        if (! empty($data['role'])) $changes['role_id'] = DB::table('roles')->where('code', $data['role'])->value('id');
        if (! empty($data['status'])) {
            $changes['status'] = $data['status'];
            if ($data['status'] === 'removido') $changes['deleted_at'] = now();
        }
        $updated = DB::table('company_memberships')->where('id', $membership)->where('version', $data['version'])->update($changes);
        abort_unless($updated, 409, 'Este vínculo foi alterado por outra pessoa. Atualize a tela e tente novamente.');
        return response()->json(['message' => 'Vínculo atualizado.']);
    }

    public function transferAdmin(Request $request, AuthController $auth)
    {
        $this->adminOnly($request);
        $data = $request->validate(['membership_id' => ['required', 'string', 'size:30'], 'password' => ['required', 'string'], 'keep_previous_access' => ['required', 'boolean']]);
        abort_unless(Hash::check($data['password'], $request->user()->password), 422, 'Senha atual inválida.');
        $companyId = $request->attributes->get('active_company_id');
        $target = DB::table('company_memberships as membership')->join('users', 'users.id', '=', 'membership.user_id')
            ->where('membership.id', $data['membership_id'])->where('membership.company_id', $companyId)->where('membership.status', 'ativo')
            ->whereNotNull('users.email_verified_at')->select('membership.*', 'users.email', 'users.id as user_id')->first();
        abort_unless($target, 422, 'O novo administrador deve estar ativo, vinculado e ter e-mail confirmado.');
        abort_if($target->active_admin_company_id, 422, 'Esta pessoa já é administradora.');
        $auth->sendToken(User::findOrFail($target->user_id), 'admin_transfer', '/auth/aceitar-transferencia.html', [
            'company_id' => $companyId, 'from_membership_id' => $request->attributes->get('active_membership')->id,
            'to_membership_id' => $target->id, 'keep_previous_access' => $data['keep_previous_access'],
        ]);
        return response()->json(['message' => 'Enviamos o aceite de transferência ao novo administrador.']);
    }

    private function adminOnly(Request $request): void
    {
        abort_unless($request->attributes->get('active_membership')->role === 'admin', 403, 'Apenas o administrador pode realizar esta ação.');
    }
}
