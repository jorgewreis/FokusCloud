<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchDiscoveryController;

// These resources must reach Laravel: both sites share the same public directory.
Route::withoutMiddleware('web')->group(function () {
    Route::get('/robots.txt', [SearchDiscoveryController::class, 'robots']);
    Route::get('/sitemap.xml', [SearchDiscoveryController::class, 'sitemap']);
    Route::get('/sitemap-styles.xml', fn () => redirect()->away('https://styles.fokuscloud.com.br/sitemap.xml', 301));
});

Route::domain('styles.fokuscloud.com.br')->group(function () {
    Route::get('/', function () {
        return response()->file(public_path('styles/index.html'));
    });

    Route::get('/layout', function () {
        return response()->file(public_path('styles/docs/layout/index.html'));
    });

    Route::get('/forms', function () {
        return response()->file(public_path('styles/docs/forms/index.html'));
    });

    Route::get('/components', function () {
        return response()->file(public_path('styles/docs/components/index.html'));
    });

    Route::get('/helpers', function () {
        return response()->file(public_path('styles/docs/helpers/index.html'));
    });

    Route::get('/utilities', function () {
        return response()->file(public_path('styles/docs/utilities/index.html'));
    });
});

Route::get('/', function () {
    return response()->file(public_path('index.html'));
});

// These endpoints intentionally inherit the web group: session cookies and
// CSRF protection are required for every browser-originated request.
Route::prefix('api')->group(base_path('routes/api.php'));

Route::get('/api/csrf-token', fn () => response()->json(['token' => csrf_token()]));

Route::get('/acesso', fn () => redirect('/?acesso=cliente'));
Route::get('/portal', fn () => response()->file(public_path('portal/dashboard.html')));
Route::get('/portal/painel', fn () => response()->file(public_path('portal/dashboard.html')));
Route::get('/portal/perfil', fn () => response()->file(public_path('portal/profile.html')));
Route::get('/cadastro', fn () => response()->file(public_path('auth/cadastro.html')));
Route::get('/verificar-email', fn () => response()->file(public_path('auth/verificar-email.html')));
Route::get('/criar-senha', fn () => response()->file(public_path('auth/criar-senha.html')));
Route::get('/recuperar-senha', fn () => response()->file(public_path('auth/recuperar-senha.html')));
Route::get('/aceitar-vinculo', fn () => response()->file(public_path('auth/aceitar-vinculo.html')));
Route::get('/aceitar-transferencia', fn () => response()->file(public_path('auth/aceitar-transferencia.html')));
Route::get('/portal/empresas', fn () => response()->file(public_path('portal/companies.html')));
Route::get('/portal/usuarios', fn () => response()->file(public_path('portal/users.html')));
Route::get('/portal/assinaturas', fn () => response()->file(public_path('portal/subscriptions.html')));
Route::get('/portal/transferir-administracao', fn () => response()->file(public_path('portal/admin-transfer.html')));
Route::get('/backoffice/acesso', fn () => response()->file(public_path('backoffice/acesso.html')));
Route::get('/backoffice/ativar', fn () => response()->file(public_path('backoffice/ativar.html')));
Route::get('/backoffice/{page?}', fn () => response()->file(public_path('backoffice/painel.html')))->where('page', 'painel|empresas|planos|catalogo|assinaturas|vouchers|pagamentos|billing|auditoria|seguranca');
Route::get('/produtos', fn () => response()->file(public_path('marketing/products/index.html')));
Route::get('/produtos/fokus-styles', fn () => response()->file(public_path('marketing/products/fokus-styles.html')));

// Development-server fallback. Production NGINX redirects these physical legacy paths before serving static files.
Route::permanentRedirect('/admin', '/acesso');
Route::permanentRedirect('/admin/painel', '/portal');
Route::permanentRedirect('/admin/perfil', '/portal/perfil');
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
