# Marco 5 — Empresas e assinaturas

## Objetivo

Entregar a primeira pre-release funcional de consulta comercial de empresas e
gestao controlada de assinaturas: `v0.0.5-alpha.1`.

## Escopo

A alpha inclui listagem paginada e detalhe de empresas, assinaturas,
pagamentos locais, itens contratados, uso recente e historico comercial.
Tambem inclui pausa, reativacao, cancelamento agendado, upgrade pendente de
pagamento, downgrade agendado e override exclusivo do superadministrador.

As paginas reais sao `public/backoffice/pages/companies.html` e
`public/backoffice/pages/subscriptions.html`. As APIs administrativas sao:

- `GET /api/backoffice/companies`
- `GET /api/backoffice/companies/{company}`
- `GET /api/backoffice/subscriptions`
- `GET /api/backoffice/subscriptions/{subscription}`
- `PATCH /api/backoffice/subscriptions/{subscription}`

## Regras comerciais e seguranca

- `platform.companies.view` controla consultas de empresas.
- `platform.subscriptions.manage` controla consultas e acoes de assinaturas.
- Override exige `platform.commercial.override`, motivo e snapshot before/after.
- Suspensao, reativacao, cancelamento, upgrade e downgrade exigem motivo.
- Precos e composicao sao recalculados pelo catalogo publicado.
- Upgrade permanece aguardando confirmacao financeira; downgrade e
  cancelamento respeitam a data efetiva.
- Transicoes usam transacao, bloqueio da assinatura, versionamento e auditoria.
- CPF, CNPJ e e-mails sao mascarados; dados pessoais nao podem ser alterados
  por estas APIs.

## Status e snapshots

Os status antigos `pendente` e `pendente_pagamento` sao convertidos para
`aguardando_pagamento`. Assinaturas e pagamentos suportam os novos estados
normativos, e `subscription_changes` preserva `before_snapshot`,
`after_snapshot`, versao e o administrador que aprovou a mudanca.

## Criterios de aceite

- Consultas paginadas retornam os dados comerciais sem exposicao de dados
  pessoais completos.
- Acoes autorizadas geram mudanca, auditoria e snapshot coerentes.
- Upgrade nao altera recursos antes da confirmacao; downgrade so altera na
  data efetiva.
- O comando `fokus:apply-subscription-changes` aplica mudancas agendadas.
- Migration limpa, suite automatizada, build e `git diff --check` passam.
- PR, `origin/main`, GitHub Actions/deploy e tag sao registrados apos a
  validacao.

## Limitacoes da `alpha.1`

Billing sandbox completo do Mercado Pago, conciliacao, reembolsos, alertas
financeiros e homologacao completa em navegador permanecem para os marcos
seguintes. O contrato publico `GET /api/catalog/{product}` continua em
`0.0.3`.
