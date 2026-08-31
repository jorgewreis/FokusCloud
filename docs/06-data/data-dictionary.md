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

## A complementar

Expandir conforme novas migrations forem criadas.

O modelo detalhado das tabelas de Backoffice, Billing, alertas, reembolsos e
conciliacao esta em [Modelo de dados do Backoffice e Billing](backoffice-and-billing-data-model.md).
