# Marco 6 — Billing sandbox

## `v0.0.6-alpha.1`

Esta alpha implementa a primeira camada operacional de billing com Mercado Pago sandbox. O cliente REST centraliza checkout, consulta de preapproval, pagamentos autorizados, pagamentos transacionais e reembolsos, sem registrar tokens, cartões ou payloads brutos.

### Escopo entregue

- checkout com `Idempotency-Key` e tentativa técnica persistida;
- webhooks `subscription_preapproval`, `subscription_authorized_payment` e `payment`;
- validação HMAC com `x-signature`, `x-request-id` e `data.id` da query;
- eventos persistidos e idempotentes;
- estados locais de pagamento, recorrência e inadimplência com tolerância de sete dias;
- consulta administrativa de pagamentos;
- conciliação manual sem correção automática;
- solicitação, aprovação e execução de reembolso total ou parcial;
- auditoria e permissões financeiras;
- página `/backoffice/pages/pagamentos.html` carregada pelo shell administrativo.

### APIs e comandos

`POST /api/subscriptions/checkout`, `POST /api/webhooks/mercado-pago`, `GET /api/backoffice/payments`, `GET /api/backoffice/reconciliation`, `PATCH /api/backoffice/reconciliation/{alert}`, `GET/POST /api/backoffice/refunds` e `PATCH /api/backoffice/refunds/{refund}`.

Comandos: `fokus:expire-subscription-tolerance` e `fokus:reconcile-mercado-pago`, este último com `--dry-run`.

### Limitações da alpha

Billing sandbox real, conciliação externa, emissão fiscal, chargeback completo, alertas financeiros e dashboard avançado dependem de homologação e marcos posteriores. As credenciais sandbox devem permanecer nos secrets do ambiente de homologação. O contrato público do catálogo continua em `0.0.3`.

### Critérios de aceite

Testes automatizados, migration em SQLite limpo, build frontend e `git diff --check` aprovados; webhooks inválidos não alteram dados; eventos repetidos não duplicam efeitos; reembolsos exigem aprovação e execução do superadministrador; nenhum segredo aparece em resposta, snapshot ou log.
