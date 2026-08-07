<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->file(public_path('index.html'));
});

// These endpoints intentionally inherit the web group: session cookies and
// CSRF protection are required for every browser-originated request.
Route::prefix('api')->group(base_path('routes/api.php'));

Route::get('/api/csrf-token', fn () => response()->json(['token' => csrf_token()]));

Route::get('/acesso', fn () => response()->file(public_path('admin/index.html')));
Route::get('/portal', fn () => response()->file(public_path('views/portal-painel.html')));
Route::get('/portal/painel', fn () => response()->file(public_path('views/portal-painel.html')));
Route::get('/portal/perfil', fn () => response()->file(public_path('admin/perfil.html')));
Route::get('/cadastro', fn () => response()->file(public_path('auth/cadastro.html')));
Route::get('/verificar-email', fn () => response()->file(public_path('auth/verificar-email.html')));
Route::get('/criar-senha', fn () => response()->file(public_path('auth/criar-senha.html')));
Route::get('/recuperar-senha', fn () => response()->file(public_path('auth/recuperar-senha.html')));
Route::get('/aceitar-vinculo', fn () => response()->file(public_path('auth/aceitar-vinculo.html')));
Route::get('/aceitar-transferencia', fn () => response()->file(public_path('auth/aceitar-transferencia.html')));
Route::get('/portal/empresas', fn () => response()->file(public_path('admin/empresas.html')));
Route::get('/portal/usuarios', fn () => response()->file(public_path('admin/usuarios.html')));
Route::get('/portal/assinaturas', fn () => response()->file(public_path('admin/assinaturas.html')));
Route::get('/portal/transferir-administracao', fn () => response()->file(public_path('admin/transferir-administracao.html')));
Route::get('/backoffice/acesso', fn () => response()->file(public_path('backoffice/acesso.html')));
Route::get('/backoffice/{page?}', fn () => response()->file(public_path('backoffice/painel.html')))->where('page', 'painel|empresas|assinaturas|vouchers|auditoria');
Route::get('/produtos/fokus-law', fn () => response()->file(public_path('src/pages/fokus-law.html')));
Route::get('/produtos/fokus-lead', fn () => response()->file(public_path('src/pages/fokus-lead.html')));
Route::get('/assinaturas/fokus-law', fn () => response()->file(public_path('src/pages/fokus-law-assinatura.html')));
Route::get('/assinaturas/fokus-lead', fn () => response()->file(public_path('src/pages/fokus-lead-assinatura.html')));

// Development-server fallback. Production NGINX redirects these physical legacy paths before serving static files.
Route::permanentRedirect('/index.html', '/');
Route::permanentRedirect('/admin', '/acesso');
Route::permanentRedirect('/admin/painel', '/portal');
Route::permanentRedirect('/admin/perfil', '/portal/perfil');
Route::permanentRedirect('/src/pages/', '/produtos/fokus-law#planos');
Route::permanentRedirect('/src/pages/fokus-law.html', '/produtos/fokus-law');
Route::permanentRedirect('/src/pages/fokus-lead.html', '/produtos/fokus-lead');
Route::permanentRedirect('/src/pages/fokus-law-assinatura.html', '/assinaturas/fokus-law');
Route::permanentRedirect('/src/pages/fokus-lead-assinatura.html', '/assinaturas/fokus-lead');
Route::permanentRedirect('/auth/cadastro.html', '/cadastro');
Route::permanentRedirect('/auth/verificar-email.html', '/verificar-email');
Route::permanentRedirect('/auth/criar-senha.html', '/criar-senha');
Route::permanentRedirect('/auth/recuperar-senha.html', '/recuperar-senha');
Route::permanentRedirect('/auth/aceitar-vinculo.html', '/aceitar-vinculo');
Route::permanentRedirect('/auth/aceitar-transferencia.html', '/aceitar-transferencia');
Route::permanentRedirect('/admin/empresas', '/portal/empresas');
Route::permanentRedirect('/admin/usuarios', '/portal/usuarios');
Route::permanentRedirect('/admin/assinaturas', '/portal/assinaturas');
Route::permanentRedirect('/admin/transferir-administracao', '/portal/transferir-administracao');
Route::permanentRedirect('/admin/empresas.html', '/portal/empresas');
Route::permanentRedirect('/admin/usuarios.html', '/portal/usuarios');
Route::permanentRedirect('/admin/assinaturas.html', '/portal/assinaturas');
