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
Route::get('/auth/cadastro.html', fn () => response()->file(public_path('auth/cadastro.html')));
Route::get('/auth/verificar-email.html', fn () => response()->file(public_path('auth/verificar-email.html')));
Route::get('/auth/criar-senha.html', fn () => response()->file(public_path('auth/criar-senha.html')));
Route::get('/auth/aceitar-vinculo.html', fn () => response()->file(public_path('auth/aceitar-vinculo.html')));
Route::get('/auth/aceitar-transferencia.html', fn () => response()->file(public_path('auth/aceitar-transferencia.html')));
Route::get('/admin/empresas', fn () => response()->file(public_path('admin/empresas.html')));
Route::get('/admin/empresas.html', fn () => redirect('/admin/empresas', 301));
Route::get('/admin/usuarios', fn () => response()->file(public_path('admin/usuarios.html')));
Route::get('/admin/usuarios.html', fn () => redirect('/admin/usuarios', 301));
Route::get('/admin/assinaturas', fn () => response()->file(public_path('src/pages/fokus-law-assinatura.html')));
Route::get('/admin/assinaturas.html', fn () => redirect('/admin/assinaturas', 301));
Route::get('/admin/transferir-administracao', fn () => response()->file(public_path('admin/usuarios.html')));
Route::get('/produtos/fokus-law', fn () => response()->file(public_path('src/pages/fokus-law.html')));
Route::get('/produtos/fokus-lead', fn () => response()->file(public_path('src/pages/fokus-lead.html')));
Route::get('/assinaturas/fokus-law', fn () => response()->file(public_path('src/pages/fokus-law-assinatura.html')));
Route::get('/assinaturas/fokus-lead', fn () => response()->file(public_path('src/pages/fokus-lead-assinatura.html')));
