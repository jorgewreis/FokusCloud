<?php

namespace Tests\Feature;

use Tests\TestCase;

class FormDesignSystemTest extends TestCase
{
    public function test_backoffice_forms_use_the_shared_form_contract(): void
    {
        $pages = [
            'public/backoffice/acesso.html',
            'public/backoffice/ativar.html',
            'public/backoffice/pages/security.html',
            'public/backoffice/pages/subscription-plans.html',
            'public/backoffice/pages/vouchers.html',
        ];

        foreach ($pages as $page) {
            $contents = file_get_contents(base_path($page));

            $this->assertNotFalse($contents, $page);
            $this->assertStringContainsString('fc-form', $contents, $page);
            $this->assertStringNotContainsString('<style', $contents, $page);
            $this->assertStringNotContainsString('style=', $contents, $page);
        }
    }

    public function test_form_system_documents_all_required_components(): void
    {
        $css = file_get_contents(base_path('public/backoffice/assets/css/components/form-admin.css'));
        $documentation = file_get_contents(base_path('docs/03-architecture/form-design-system.md'));
        $script = file_get_contents(base_path('public/backoffice/assets/js/form-system.js'));

        foreach (['form-error-summary', 'form-file', 'input-group', 'form-range', 'dialog-panel', 'is-loading'] as $component) {
            $this->assertStringContainsString($component, $css, $component);
        }

        foreach (['FokusForm.validate', 'FokusForm.mapServerErrors', 'FokusForm.setLoading', 'Checklist de contrato'] as $contract) {
            $this->assertTrue(str_contains($script, $contract) || str_contains($documentation, $contract), $contract);
        }
    }

    public function test_backoffice_required_fields_are_auto_marked_and_cache_busted(): void
    {
        $panel = file_get_contents(base_path('public/backoffice/painel.html'));
        $access = file_get_contents(base_path('public/backoffice/acesso.html'));
        $activate = file_get_contents(base_path('public/backoffice/ativar.html'));
        $css = file_get_contents(base_path('public/backoffice/assets/css/components/form-admin.css'));
        $script = file_get_contents(base_path('public/backoffice/assets/js/form-system.js'));

        foreach ([$panel, $access, $activate] as $contents) {
            $this->assertStringContainsString('20260901-required-fields', $contents);
        }

        $this->assertStringContainsString('MutationObserver', $script);
        $this->assertStringContainsString('markRequiredFields', $script);
        $this->assertStringContainsString('form-label-required', $script);
        $this->assertStringContainsString('background: var(--theme-surface, #ffffff)', $css);
    }

    public function test_admin_invite_form_does_not_reuse_sidebar_admin_id(): void
    {
        $panel = file_get_contents(base_path('public/backoffice/painel.html'));
        $security = file_get_contents(base_path('public/backoffice/pages/security.html'));

        $this->assertStringContainsString('id="admin-name"', $panel);
        $this->assertStringNotContainsString('id="admin-name"', $security);
        $this->assertStringContainsString('id="invite-admin-name"', $security);
    }
}
