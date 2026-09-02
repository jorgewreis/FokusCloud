# Release 0.1 - Backoffice Admin

## Objetivo

Entregar a versao `0.1` do Fokus Cloud com o Modulo Admin em funcionamento em
ambiente de desenvolvimento/homologacao.

A entrega cobre a administracao interna da plataforma: financeiro
administrativo, planos, assinaturas, usuarios internos, empresas, perfis,
seguranca, vouchers, permissoes, auditoria e Mercado Pago em sandbox real.

## Escopo aprovado

| Area | Resultado esperado na 0.1 |
| --- | --- |
| Painel geral | Indicadores comerciais, financeiros, seguranca, alertas e eventos recentes. |
| Empresas | Consulta e gestao comercial sem alterar dados cadastrais ou pessoais. |
| Planos | CRUD administrativo, status operacional, publicacao e consumo pelo catalogo. |
| Assinaturas | Consulta, historico, snapshots, pausa, reativacao, cancelamento e troca de plano. |
| Vouchers/cupons | Criacao, status, validade, limites, uso e snapshot do resgate. |
| Usuarios internos | Gestao de admins internos e seus perfis. |
| Perfis e seguranca | `superadministrador` e `administrador_comercial`, guard separado e MFA. |
| Auditoria | Eventos sensiveis com motivo, before/after, metadados minimos e retencao. |
| Billing sandbox | Mercado Pago sandbox, webhooks, pagamentos, recorrencia e conciliacao manual. |

## Criterio de pronto

A release `0.1` estara pronta quando:

- a release documental `0.0.1` estiver concluida conforme
  [Definition of Done](definition-of-done.md);
- a [matriz de rastreabilidade](traceability-matrix.md) estiver atualizada;
- todos os itens obrigatorios do [checklist de modulos](module-checklist.md)
  estiverem concluidos;
- os criterios de [aceite](acceptance-criteria.md) passarem;
- o [plano de testes](test-plan.md) for executado sem falhas bloqueantes;
- o [roteiro de homologacao](homologation-script.md) for validado em ambiente
  de homologacao;
- as pendencias restantes em [decisoes abertas](open-decisions.md) estiverem
  classificadas como nao bloqueantes.

## Documentos da release

- [Roadmap de entrega](delivery-roadmap.md)
- [Checklist por modulo](module-checklist.md)
- [Analise de lacunas tecnicas](technical-gap-analysis.md)
- [Criterios de aceite](acceptance-criteria.md)
- [Plano de testes](test-plan.md)
- [Roteiro de homologacao](homologation-script.md)
- [Notas da release](release-notes.md)
- [Decisoes abertas](open-decisions.md)
- [Marco 2 - seguranca interna](milestone-2-security.md)
- [Marco 3 - catalogo administrativo](milestone-3-catalog.md)
- [Matriz de rastreabilidade](traceability-matrix.md)
- [Definition of Done](definition-of-done.md)
- [Checklist de revisao documental](documentation-review-checklist.md)

## Fontes normativas

- [Backoffice Fokus Cloud](../../04-products/backoffice.md)
- [Requisitos do Backoffice](../../05-requirements/backoffice.md)
- [Modelo de dados do Backoffice e Billing](../../06-data/backoffice-and-billing-data-model.md)
- [Modelo de permissoes](../../07-security/permission-model.md)
- [Billing e conciliacao com Mercado Pago](../../08-commercial/billing-and-reconciliation.md)
- [Monitoramento, suporte e operacao do Backoffice](../../09-operations/backoffice-monitoring-and-support.md)
