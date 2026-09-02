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
            'public/backoffice/pages/companies.html',
            'public/backoffice/pages/subscriptions.html',
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

        $this->assertStringContainsString('20260902-commercial-alpha1', $panel);
        foreach ([$access, $activate] as $contents) {
            $this->assertStringContainsString('20260901-live-controls', $contents);
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

    public function test_catalog_and_voucher_actions_use_accessible_dialogs_and_shared_icons(): void
    {
        $catalog = file_get_contents(base_path('public/backoffice/pages/subscription-plans.html'));
        $vouchers = file_get_contents(base_path('public/backoffice/pages/vouchers.html'));

        $this->assertStringContainsString('id="catalog-destructive-dialog"', $catalog);
        $this->assertStringContainsString('id="voucher-destructive-dialog"', $vouchers);
        $this->assertStringContainsString('Shopping-Basket-Edit--Streamline-Ultimate.png', $catalog);
        $this->assertStringContainsString('Shopping-Basket-Subtract--Streamline-Ultimate.png', $catalog);
        foreach (['Tags-Add--Streamline-Ultimate.png', 'Ticket-Exchange--Streamline-Ultimate.png', 'Tags-Minus--Streamline-Ultimate.png', 'Tags-Remove--Streamline-Ultimate.png'] as $icon) {
            $this->assertStringContainsString($icon, $vouchers, $icon);
        }
        $this->assertStringContainsString('data-voucher-action="remove-or-archive"', $vouchers);
        $this->assertStringNotContainsString('data-voucher-action="archive"', $vouchers);
        $this->assertStringNotContainsString('data-voucher-action="delete"', $vouchers);
        $this->assertStringNotContainsString('window.confirm', $catalog.$vouchers);
        $this->assertStringNotContainsString('prompt(', $catalog.$vouchers);
    }

    public function test_catalog_uses_masked_currency_controls_and_compact_plan_checkboxes(): void
    {
        $catalog = file_get_contents(base_path('public/backoffice/pages/subscription-plans.html'));
        $css = file_get_contents(base_path('public/backoffice/assets/css/components/form-admin.css'));
        $pageCss = file_get_contents(base_path('public/backoffice/assets/css/pages/mockup.css'));

        $this->assertSame(2, substr_count($catalog, 'data-currency-input'));
        $this->assertStringContainsString('plan-module-checkbox', $catalog);
        $this->assertStringContainsString('input:not([type="checkbox"]):not([type="radio"])', $css);
        $this->assertStringContainsString('width: 16px !important', $pageCss);
        $this->assertStringContainsString('height: 16px !important', $pageCss);
    }

    public function test_catalog_tables_expose_reusable_pagination_controls(): void
    {
        $catalog = file_get_contents(base_path('public/backoffice/pages/subscription-plans.html'));

        foreach (['product-pagination', 'module-pagination', 'plan-pagination', 'publication-pagination', 'data-catalog-page', 'pageSize = 20'] as $fragment) {
            $this->assertStringContainsString($fragment, $catalog, $fragment);
        }
    }

    public function test_company_and_subscription_pages_follow_live_backoffice_components(): void
    {
        $pages = [
            'public/backoffice/pages/companies.html' => 'company-page',
            'public/backoffice/pages/subscriptions.html' => 'subscription-page',
        ];

        foreach ($pages as $page => $rootClass) {
            $contents = file_get_contents(base_path($page));

            $this->assertStringContainsString('class="'.$rootClass.' d-flex col"', $contents, $page);
            $this->assertStringContainsString('<hr>', $contents, $page);
            $this->assertStringContainsString('table-panel', $contents, $page);
            $this->assertStringContainsString('data-table', $contents, $page);
            $this->assertStringContainsString('class="submit"', $contents, $page);
            $this->assertStringNotContainsString('class="btn', $contents, $page);
            $this->assertStringNotContainsString('table-container', $contents, $page);
        }

        $pageCss = file_get_contents(base_path('public/backoffice/assets/css/pages/mockup.css'));
        foreach (['.company-page', '.subscription-page'] as $selector) {
            $this->assertStringContainsString($selector, $pageCss, $selector);
        }
    }
}
