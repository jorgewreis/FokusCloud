# Matriz de rastreabilidade da release 0.1

## Objetivo

Rastrear cada marco da release `0.1` do Backoffice Admin ate suas fontes
normativas, criterios de aceite, testes esperados e evidencia de homologacao.

Esta matriz e o artefato central da release documental `0.0.1`.

## Matriz por marco

| Marco | Entrega | Fontes normativas | Aceite | Testes/evidencias |
| --- | --- | --- | --- | --- |
| 1 | Pacote documental da release | `README.md`, `delivery-roadmap.md`, `technical-gap-analysis.md`, `open-decisions.md` | Documentos completos, links validos, lacunas e decisoes classificadas | `git diff --check`, revisao de links, checklist documental |
| 2 | Seguranca interna | `milestone-2-security.md`, `../../04-products/backoffice.md`, `../../05-requirements/backoffice.md`, `../../07-security/access-control-policy.md` | Login interno, MFA, bloqueio rigido, perfis e guard separado | 18 testes de autenticacao, isolamento, bloqueio e permissao |
| 3 | Catalogo administrativo | `milestone-3-catalog.md`, `../../08-commercial/catalog.md`, `../../08-commercial/catalog-api.md`, `../../06-data/catalog-data-model.md` | CRUD de catalogo, snapshot publicado, publicacao controlada e consumo pelo catalogo publico | `CatalogAdminTest`, contrato publico `0.0.3`, rascunho, publicado, pausado, arquivado e checkout |
| 4 | Vouchers/cupons | `../../08-commercial/vouchers.md`, `../../05-requirements/backoffice.md`, `../../06-data/backoffice-and-billing-data-model.md` | Tipos suportados, validade, limites, elegibilidade e snapshot | Testes de criacao, pausa, arquivamento, resgate e snapshot |
| 5 | Empresas e assinaturas | `../../08-commercial/registration-and-subscription.md`, `../../08-commercial/billing-and-reconciliation.md`, `../../06-data/backoffice-and-billing-data-model.md` | Consulta comercial, historico, pausa, reativacao, cancelamento e troca de plano | Testes de status, snapshots, recalculo e acoes controladas |
| 6 | Billing sandbox | `../../08-commercial/billing-and-reconciliation.md`, `../../09-operations/backoffice-monitoring-and-support.md` | Mercado Pago sandbox, webhook, idempotencia, divergencia e conciliacao | Testes de checkout, webhook valido/invalido, duplicidade e conciliacao |
| 7 | Dashboard, alertas e auditoria | `../../09-operations/backoffice-monitoring-and-support.md`, `../../07-security/security-and-data.md`, `../../05-requirements/backoffice.md` | Painel operacional, filas, eventos sensiveis e auditoria filtrada por perfil | Testes de alertas, retencao, mascaramento e consulta por perfil |
| 8 | Estabilizacao e release | `../../09-operations/release-process.md`, `acceptance-criteria.md`, `homologation-script.md` | Sem falhas bloqueantes, notas atualizadas e release registrada | Suite automatizada, homologacao guiada e evidencias finais |

## Requisitos de Backoffice por area

| Area | Requisitos principais |
| --- | --- |
| Seguranca interna | RF-BO-001, RF-BO-002, RF-BO-003, RNF-BO-001 |
| Catalogo | RF-BO-004, RF-BO-005, RF-BO-006, RF-BO-007, RNF-BO-002 |
| Assinaturas | RF-BO-008, RF-BO-009, RF-BO-010, RF-BO-011, RF-BO-012 |
| Vouchers | RF-BO-013, RF-BO-014 |
| Empresas | RF-BO-015 |
| Dashboard e financeiro | RF-BO-016, RF-BO-017, RF-BO-018, RNF-BO-005 |
| Auditoria | RF-BO-019, RF-BO-020, RNF-BO-003, RNF-BO-004, RNF-BO-006 |

## Evidencia minima por documento

| Documento | Evidencia de completude |
| --- | --- |
| `README.md` | Escopo, criterio de pronto e fontes normativas definidos. |
| `delivery-roadmap.md` | Oito marcos ordenados com saida obrigatoria. |
| `module-checklist.md` | Checklist por modulo com itens verificaveis. |
| `technical-gap-analysis.md` | Estado atual e lacunas bloqueantes registrados. |
| `acceptance-criteria.md` | Aceite funcional, financeiro, tecnico, seguranca e homologacao. |
| `test-plan.md` | Testes automatizados, manuais e evidencias esperadas. |
| `homologation-script.md` | Roteiro executavel por fluxo operacional. |
| `release-notes.md` | Status da `0.0.1` e da `0.1` documentados. |
| `open-decisions.md` | Decisoes classificadas em bloqueantes e nao bloqueantes. |
| `definition-of-done.md` | Condicoes de pronto da `0.0.1` e dos marcos seguintes. |
| `documentation-review-checklist.md` | Revisao documental executada e rastreavel. |

## Regra de manutencao

Quando um marco mudar escopo, status, aceite ou teste obrigatorio, esta matriz
deve ser atualizada no mesmo commit da mudanca documental correspondente.
## Marco 4 / release 0.0.4

| Requisito | Implementação | Evidência |
| --- | --- | --- |
| Voucher reservado e confirmado | VoucherManager, checkout e webhook | VoucherRedemptionTest |
| Snapshot e commercial_credit | migration de snapshot/reservas e VoucherManager | VoucherRedemptionTest |
| Edição/imutabilidade e governança | APIs PATCH/archive/delete e RBAC | BackofficeSecurityTest |
| Exclusões e fallback | CatalogManager e APIs DELETE | CatalogAdminTest |
| Formulários | FokusForm, diálogos, ícones e cache versionado | FormDesignSystemTest + homologação |
