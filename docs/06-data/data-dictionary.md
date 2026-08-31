# Dicionario de dados

Este arquivo deve descrever tabelas, colunas, tipos, obrigatoriedade e significado de cada campo relevante.

## Modelo

| Tabela | Coluna | Tipo | Obrigatoria | Descricao |
| --- | --- | --- | --- | --- |
| users | email | string | Sim | E-mail usado para identificacao do usuario. |
| companies | name | string | Sim | Nome da empresa cliente. |
| platform_admins | role | enum | Sim | Perfil interno do Backoffice: `superadministrador` ou `administrador_comercial`. |
| platform_admins | status | enum | Sim | Estado da conta interna: `ativo`, `bloqueado`, `suspenso` ou `desativado`. |
| platform_audit_events | expires_at | timestamp | Sim | Data de expiracao da auditoria do Backoffice, com retencao de 180 dias. |
| platform_alerts | queue | enum | Sim | Fila operacional do alerta: financeiro, seguranca, catalogo, suporte interno ou auditoria/revisao. |
| platform_alerts | due_at | timestamp | Sim | Prazo limite de atendimento conforme severidade. |
| platform_alert_comments | expires_at | timestamp | Sim | Data de expiracao do comentario operacional, com retencao de 90 dias. |
| subscriptions | status | enum | Sim | Estado alvo da assinatura no ciclo de billing. |
| payments | status | enum | Sim | Estado alvo do pagamento normalizado a partir do Mercado Pago. |
| refund_requests | status | enum | Sim | Estado da solicitacao de reembolso. |
| payment_reconciliation_alerts | status | enum | Sim | Estado da divergencia de conciliacao. |
| law_units | status | enum | Sim | Estado da unidade juridica: `active`, `suspended` ou `archived`. |
| law_unit_memberships | role | enum | Sim | Perfil Law por unidade: `unit_admin`, `chief_clerk`, `operator` ou `viewer`. |
| law_cases | operational_status | enum | Sim | Estado operacional interno do processo: `active`, `pending`, `suspended`, `archived` ou `cancelled`. |
| law_cases | official_status_code | string | Nao | Codigo/situacao oficial sincronizada de fonte externa, sem controlar o status interno. |
| law_cases | is_confidential | boolean | Sim | Indica sigilo processual e aciona restricoes transversais. |
| law_case_parties | role | enum | Sim | Papel processual: `author`, `defendant`, `victim`, `prosecutor`, `defense`, `witness`, `interested` ou `other`. |
| law_offices | status | enum | Sim | Estado do oficio: `created`, `signed`, `sent`, `received` ou `closed`. |
| law_outgoing_letters | status | enum | Sim | Estado da carta expedida: `created`, `sent`, `received_at_destination`, `returned`, `closed` ou `cancelled`. |
| law_outgoing_letters | destination_number | string | Nao | Numero atribuido posteriormente pela comarca ou orgao de destino. |
| law_tasks | status | enum | Sim | Estado do prazo ou pendencia: `open`, `in_progress`, `waiting`, `done`, `cancelled` ou `overdue`. |
| law_alerts | status | enum | Sim | Estado do alerta operacional Law: `open`, `acknowledged`, `resolved` ou `dismissed`. |
| law_audit_events | reason | string | Nao | Motivo da acao auditada, obrigatorio para alteracoes sensiveis definidas no modelo Law. |
| law_confidential_case_accesses | access_level | enum | Sim | Nivel de acesso ao processo sigiloso: `view`, `operate` ou `manage_confidentiality`. |
| law_support_requests | category | enum | Sim | Categoria de suporte: `access_permissions`, `usage_question`, `technical_error` ou `subscription_modules`. |
| law_support_requests | expires_at | timestamp | Sim | Data de expiracao da solicitacao de suporte, com retencao de 90 dias. |
| law_datajud_syncs | status | enum | Sim | Estado da consulta/sincronizacao Datajud: `requested`, `success`, `partial`, `failed` ou `ignored`. |
| law_datajud_syncs | trigger_type | enum | Sim | Gatilho da sincronizacao automatica: `case_created`, `case_moved` ou `retry`. |
| law_datajud_divergences | status | enum | Sim | Estado da divergencia Datajud: `open`, `reviewed`, `applied` ou `dismissed`. |

## A complementar

Expandir conforme novas migrations forem criadas.

O modelo detalhado das tabelas de Backoffice, Billing, alertas, reembolsos e
conciliacao esta em [Modelo de dados do Backoffice e Billing](backoffice-and-billing-data-model.md).

O modelo detalhado das tabelas do Fokus Law esta em [Modelo de dados do Fokus Law](fokus-law-data-model.md).
