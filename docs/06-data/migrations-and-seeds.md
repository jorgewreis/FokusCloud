# Migrations e seeds

## Migrations

Migrations devem representar alteracoes pequenas, revisaveis e coerentes com o modelo relacional.

## Seeds

Seeds podem criar dados iniciais para desenvolvimento, testes e carga inicial controlada.

## Regras

- Nao usar seed como substituto permanente de backoffice quando o dado for administravel.
- Registrar impacto de dados comerciais versionaveis.
- Validar chaves estrangeiras e indices antes de implementar fluxos dependentes.
- Evolucoes de Backoffice e Billing devem seguir o [Modelo de dados do Backoffice e Billing](backoffice-and-billing-data-model.md).
- Migrations de status devem tratar conversao do estado atual para o modelo alvo sem perder historico de assinatura, pagamento, voucher ou auditoria.
- Tabelas operacionais com retencao devem incluir `expires_at` quando a regra de negocio exigir limpeza posterior.

## A complementar

Listar migrations criticas e seeds oficiais do projeto.

Migrations ja existentes relacionadas ao modelo:

- `2026_08_06_000300_create_governance_tables.php`: cria `payments`, `audit_events` e `support_accesses`.
- `2026_08_08_000600_create_backoffice_and_commercial_governance_tables.php`: cria `platform_admins`, `platform_login_challenges`, `platform_audit_events`, `vouchers`, `voucher_redemptions`, `subscription_changes`, `usage_snapshots` e campos de recorrencia em `subscriptions`.
