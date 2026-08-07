<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->file(public_path('index.html'));
});

// These endpoints intentionally inherit the web group: session cookies and
// CSRF protection are required for every browser-originated request.
Route::prefix('api')->group(base_path('routes/api.php'));

Route::get('/api/csrf-token', fn () => response()->json(['token' => csrf_token()]));

Route::get('/admin', fn () => response()->file(public_path('admin/index.html')));
Route::get('/admin/painel', fn () => response()->file(public_path('admin/painel.html')));
Route::get('/admin/perfil', fn () => response()->file(public_path('admin/perfil.html')));
Route::get('/cadastro', fn () => response()->file(public_path('auth/cadastro.html')));
Route::get('/verificar-email', fn () => response()->file(public_path('auth/verificar-email.html')));
Route::get('/criar-senha', fn () => response()->file(public_path('auth/criar-senha.html')));
Route::get('/recuperar-senha', fn () => response()->file(public_path('auth/recuperar-senha.html')));
Route::get('/aceitar-vinculo', fn () => response()->file(public_path('auth/aceitar-vinculo.html')));
Route::get('/aceitar-transferencia', fn () => response()->file(public_path('auth/aceitar-transferencia.html')));
Route::get('/admin/empresas', fn () => response()->file(public_path('admin/empresas.html')));
Route::get('/admin/usuarios', fn () => response()->file(public_path('admin/usuarios.html')));
Route::get('/admin/assinaturas', fn () => response()->file(public_path('admin/assinaturas.html')));
Route::get('/admin/transferir-administracao', fn () => response()->file(public_path('admin/transferir-administracao.html')));
Route::get('/produtos/fokus-law', fn () => response()->file(public_path('src/pages/fokus-law.html')));
Route::get('/produtos/fokus-lead', fn () => response()->file(public_path('src/pages/fokus-lead.html')));
Route::get('/assinaturas/fokus-law', fn () => response()->file(public_path('src/pages/fokus-law-assinatura.html')));
Route::get('/assinaturas/fokus-lead', fn () => response()->file(public_path('src/pages/fokus-lead-assinatura.html')));

// Development-server fallback. Production NGINX redirects these physical legacy paths before serving static files.
Route::permanentRedirect('/index.html', '/');
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
Route::permanentRedirect('/admin/empresas.html', '/admin/empresas');
Route::permanentRedirect('/admin/usuarios.html', '/admin/usuarios');
Route::permanentRedirect('/admin/assinaturas.html', '/admin/assinaturas');
