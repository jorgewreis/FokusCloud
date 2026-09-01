# Analise de lacunas tecnicas da release 0.1

## Estado observado

O projeto ja possui base relevante para o Backoffice:

- rotas `/api/backoffice` protegidas por `EnsurePlatformAdmin`;
- login interno com MFA por e-mail;
- controller com dashboard, catalogo, planos, empresas, vouchers, admins,
  assinatura e auditoria;
- migrations iniciais para `platform_admins`, desafios MFA, auditoria,
  vouchers, resgates, mudancas de assinatura e campos de recorrencia;
- paginas reais para planos e vouchers em `public/backoffice/pages`;
- mockups para painel, empresas, assinaturas e admins internos;
- testes iniciais de seguranca, isolamento e vouchers.

## Lacunas bloqueantes

| Area | Lacuna | Impacto |
| --- | --- | --- |
| Perfis internos | `platform_admins` ainda precisa garantir `role` e matriz de permissao por endpoint. | `administrador_comercial` pode executar acoes que deveriam ser exclusivas de superadministrador. |
| Bloqueio rigido | Conta interna nao registra contagem de falhas nem bloqueio automatico completo. | RF-BO-002 incompleto. |
| Auditoria | Eventos nao possuem `before_masked`, `after_masked` e `expires_at`. | Nao atende rastreabilidade e retencao da 0.1. |
| Paginas reais | Apenas planos e vouchers aparecem como paginas reais; painel, empresas, assinaturas e admins dependem de mockups. | Backoffice nao esta funcional ponta a ponta. |
| Catalogo | CRUD atual foca planos; produtos, modulos, composicoes e publicacao final ainda precisam ser completados. | Catalogo administrativo incompleto. |
| Vouchers | API atual aceita `trial_free`, `percentage` e `fixed`, mas ainda precisa `commercial_credit` e snapshot completo de resgate. | Tipos e historico comercial incompletos. |
| Assinaturas | API atual cobre suspensao, reativacao e cancelamento simples; upgrade, downgrade, recalculo e override ainda faltam. | Fluxo comercial incompleto. |
| Billing | Mercado Pago sandbox, webhook validado, recorrencia, inadimplencia, reembolso e conciliacao ainda precisam ser fechados. | Financeiro administrativo incompleto. |
| Alertas | Tabelas e APIs de alertas, comentarios, incidentes e notificacoes ainda precisam ser criadas. | Dashboard operacional e conciliacao ficam incompletos. |

## Evolucoes de banco necessarias

- Adicionar `role`, `failed_login_count`, `blocked_at` e `blocked_reason` em
  `platform_admins`.
- Ampliar status de `platform_admins`, `subscriptions`, `payments` e
  `vouchers` conforme os documentos alvo.
- Adicionar `before_masked`, `after_masked` e `expires_at` em
  `platform_audit_events`.
- Adicionar snapshots antes/depois em `subscription_changes`.
- Adicionar snapshot completo em `voucher_redemptions`.
- Criar `platform_alerts`, `platform_alert_comments`, `platform_incidents`,
  `platform_notifications`, `refund_requests` e
  `payment_reconciliation_alerts`.

## Evolucoes de frontend necessarias

- Transformar mockups de painel, empresas, assinaturas e admins em paginas
  reais consumindo `FokusApi`.
- Adicionar paginas/abas para auditoria, seguranca/perfis e conciliacao
  financeira.
- Remover fallbacks de dados mockados no modo autenticado.
- Exigir confirmacao explicita em publicacao, pausa, arquivamento,
  cancelamento, override e correcao financeira.

## Evolucoes de testes necessarias

- Cobrir permissao por perfil interno.
- Cobrir bloqueio de conta interna.
- Cobrir before/after e retencao de auditoria.
- Cobrir catalogo publicado versus rascunho/pausado/arquivado.
- Cobrir Mercado Pago sandbox com eventos validos, invalidos e duplicados.
- Cobrir fluxo manual de conciliacao.
