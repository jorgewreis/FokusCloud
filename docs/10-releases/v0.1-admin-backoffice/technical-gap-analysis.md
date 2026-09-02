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
- paginas reais para empresas e assinaturas, alem das paginas de planos e vouchers;
- testes iniciais de seguranca, isolamento e vouchers.

## Lacunas bloqueantes

| Area | Lacuna | Impacto |
| --- | --- | --- |
| Perfis internos | Concluido na `0.0.2` com RBAC de plataforma e permissao por endpoint. | Reutilizar a matriz nos marcos comerciais. |
| Bloqueio rigido | Concluido na `0.0.2` com risco por conta, IP e dispositivo. | Monitorar eventos no Marco 7. |
| Auditoria | Base de seguranca concluida na `0.0.2` com `before_masked`, `after_masked` e `expires_at`. | Estender aos dominios comerciais nos marcos seguintes. |
| Paginas reais | Empresas e assinaturas foram implementadas na `v0.0.5-alpha.1`; painel e admins ainda tem escopo posterior. | Homologacao em navegador real permanece necessaria. |
| Catalogo | Concluido na `0.0.3` com endpoints administrativos de produtos, funcionalidades, planos, composicao, publicacao e snapshot publico versionado. | Reutilizar o contrato publicado nos marcos de vouchers, assinaturas e billing. |
| Vouchers | API atual aceita `trial_free`, `percentage` e `fixed`, mas ainda precisa `commercial_credit` e snapshot completo de resgate. | Tipos e historico comercial incompletos. |
| Assinaturas | Consulta, snapshots, pausa, reativacao, cancelamento agendado, upgrade, downgrade e override foram implementados na `v0.0.5-alpha.1`. | Billing sandbox, conciliacao e reembolsos permanecem no Marco 6. |
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
- Concluido na `0.0.3`: cobrir catalogo publicado versus rascunho/pausado/arquivado.
- Cobrir Mercado Pago sandbox com eventos validos, invalidos e duplicados.
- Cobrir fluxo manual de conciliacao.

## Marco 6 — atualização da alpha

Concluídos na implementação local: camada REST do Mercado Pago, HMAC pelo
contrato oficial, idempotência persistida, recorrência, tolerância de
inadimplência, consulta de pagamentos, conciliação manual, reembolso controlado
e página de Billing. Permanecem pendentes a validação com credenciais sandbox
reais, emissão fiscal, chargeback e indicadores financeiros avançados.
