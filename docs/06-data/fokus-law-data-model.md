# Modelo de dados do Fokus Law

## Objetivo

Definir o modelo relacional alvo da v1 do Fokus Law para Cartorio Criminal, com
base reutilizavel para Cartorio Civel.

Este documento complementa:

- [Fokus Law](../04-products/fokus-law.md);
- [Requisitos do Fokus Law](../05-requirements/fokus-law.md);
- [Modelo relacional multiempresa](relational-model.md);
- [Modelo de empresas, unidades e vinculos](company-and-unit-model.md).

## Principios

- `companies` continua sendo a fronteira de isolamento empresarial.
- `company_units` continua sendo a unidade operacional generica da plataforma.
- `law_units` especializa `company_units` para o dominio juridico.
- Todas as tabelas operacionais do Law devem possuir `company_id`.
- Tabelas vinculadas a unidade juridica devem possuir `law_unit_id`.
- FKs empresariais devem impedir relacionamento entre entidades de empresas
  diferentes.
- Processos sao a entidade central da v1.
- Cartas recebidas sao classe de processo, nao expediente separado.
- Cartas expedidas sao expedientes vinculados a processo e nao possuem
  numeracao interna propria.
- Oficios possuem sequencia anual por unidade e setor.
- Dados internos prevalecem para campos operacionais.
- Datajud prevalece apenas para metadados processuais publicos sincronizados.
- Dados sigilosos devem ser protegidos por permissao e evitados em logs.
- Exclusao fisica deve ser evitada em registros operacionais usados no fluxo.

## Prefixos

| Prefixo | Entidade |
| --- | --- |
| LWU | Unidade juridica do Fokus Law |
| LWM | Vinculo usuario-unidade Law |
| LCS | Processo Law |
| LPT | Parte processual Law |
| LCP | Vinculo processo-parte Law |
| LOF | Oficio Law |
| LOL | Carta expedida Law |
| LTK | Prazo ou pendencia Law |
| LAL | Alerta operacional Law |
| LAU | Auditoria Law |
| LCA | Acesso a processo sigiloso Law |
| LSR | Solicitacao de suporte Law |
| LDS | Sincronizacao Datajud |
| LDD | Divergencia Datajud |

Todos os IDs devem seguir o padrao geral do projeto:

```text
XXX-ULID_DE_26_CARACTERES
```

## Tabelas alvo

### `law_units`

Especializa uma `company_unit` como unidade juridica.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LWU`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `company_unit_id` | char(30) | Sim | Unidade operacional generica. |
| `court_code` | varchar | Sim | Tribunal, exemplo: `TJBA`. |
| `district_name` | varchar | Sim | Comarca. |
| `unit_name` | varchar | Sim | Vara, cartorio ou unidade exibida. |
| `jurisdiction` | varchar | Sim | Competencia principal, exemplo: criminal ou civel. |
| `status` | enum | Sim | `active`, `suspended`, `archived`. |
| `version` | int | Sim | Controle de concorrencia. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |
| `deleted_at`, `deleted_by` | audit | Nao | Inativacao logica quando aplicavel. |

Regras:

- `company_unit_id` deve pertencer a mesma `company_id`.
- Uma `company_unit` pode ter no maximo uma `law_unit` ativa.
- `law_units` nao substitui dados cadastrais da empresa ou da unidade generica.

### `law_unit_memberships`

Define acesso do usuario a unidade juridica e perfil especifico do Fokus Law.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LWM`. |
| `company_id` | char(30) | Sim | Empresa do vinculo. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `company_membership_id` | char(30) | Sim | Vinculo usuario-empresa. |
| `role` | enum | Sim | `unit_admin`, `chief_clerk`, `operator`, `viewer`. |
| `status` | enum | Sim | `active`, `suspended`, `removed`. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |
| `deleted_at`, `deleted_by` | audit | Nao | Remocao logica. |

Regras:

- O mesmo usuario pode ter perfis diferentes em unidades diferentes.
- Usuario so acessa uma `law_unit` se possuir vinculo ativo.
- Permissoes comerciais nao pertencem a esta tabela.

### `law_cases`

Representa processos do Fokus Law.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LCS`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `case_number` | varchar | Sim | Numero do processo, preferencialmente CNJ quando houver. |
| `case_class` | varchar | Sim | Classe processual; cartas recebidas entram como classe. |
| `subject` | varchar | Nao | Assunto principal. |
| `operational_status` | enum | Sim | `active`, `pending`, `suspended`, `archived`, `cancelled`. |
| `official_status_code` | varchar | Nao | Codigo/situacao externa sincronizada. |
| `official_status_text` | varchar | Nao | Texto de situacao externa sincronizada. |
| `priority` | varchar | Nao | Prioridade operacional. |
| `is_confidential` | boolean | Sim | Indica sigilo processual interno. |
| `relevant_dates` | json | Nao | Datas operacionais relevantes. |
| `external_source` | varchar | Nao | Origem externa, exemplo: `datajud`. |
| `external_id` | varchar | Nao | Identificador externo quando disponivel. |
| `last_external_sync_at` | datetime | Nao | Ultima sincronizacao externa aplicada. |
| `notes` | text | Nao | Observacoes internas. |
| `cancelled_at`, `cancelled_by`, `cancel_reason` | audit | Nao | Obrigatorios quando cancelado. |
| `version` | int | Sim | Controle de concorrencia. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |
| `deleted_at`, `deleted_by` | audit | Nao | Inativacao logica. |

Regras:

- `operational_status` e interno e nao deve ser controlado pelo Datajud.
- Campos `official_*` representam situacao processual externa.
- Cartas recebidas devem usar `case_class`, nao tabela de expediente.
- Processo sigiloso deve restringir partes, expedientes, tarefas, listas,
  buscas, indicadores detalhados e exportacoes.

### `law_parties`

Representa partes reutilizaveis dentro da unidade juridica.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LPT`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `display_name` | varchar | Sim | Nome exibido. |
| `document_type` | enum | Nao | `cpf`, `cnpj`, `other`, quando informado. |
| `document_number` | varchar | Nao | Normalizado e protegido quando aplicavel. |
| `party_type` | enum | Sim | `person`, `organization`, `public_body`, `unknown`. |
| `status` | enum | Sim | `active`, `inactive`, `merged`. |
| `notes` | text | Nao | Observacoes internas. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |
| `deleted_at`, `deleted_by` | audit | Nao | Inativacao logica. |

Regras:

- O cadastro deve conter apenas dados necessarios para identificacao
  operacional.
- Dados pessoais devem ser minimizados e mascarados quando exibidos em contexto
  sem permissao.
- A parte nao define papel processual sozinha; o papel fica no vinculo com o
  processo.

### `law_case_parties`

Relaciona partes a processos.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LCP`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `law_case_id` | char(30) | Sim | Processo vinculado. |
| `law_party_id` | char(30) | Sim | Parte vinculada. |
| `role` | enum | Sim | `author`, `defendant`, `victim`, `prosecutor`, `defense`, `witness`, `interested`, `other`. |
| `role_detail` | varchar | Nao | Complemento quando `role = other` ou quando necessario. |
| `status` | enum | Sim | `active`, `inactive`. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

Regras:

- A mesma parte pode ter papeis diferentes em processos diferentes.
- O vinculo deve respeitar a mesma `company_id` e `law_unit_id` de processo e
  parte.

### `law_offices`

Representa oficios expedidos/controlados pela unidade.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LOF`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `law_case_id` | char(30) | Sim | Processo vinculado. |
| `sector` | varchar | Sim | Setor/origem operacional, exemplo: Cartorio ou Gabinete. |
| `sequence_year` | smallint | Sim | Ano de referencia da numeracao. |
| `sequence_number` | int | Sim | Numero incremental por unidade, setor e ano. |
| `recipient` | varchar | Sim | Destinatario. |
| `subject` | varchar | Sim | Assunto. |
| `status` | enum | Sim | `created`, `signed`, `sent`, `received`, `closed`. |
| `issued_at` | datetime | Nao | Data de expedicao/assinatura. |
| `sent_at` | datetime | Nao | Data de envio. |
| `received_at` | datetime | Nao | Data de recebimento/resposta. |
| `closed_at` | datetime | Nao | Data de encerramento. |
| `responsible_membership_id` | char(30) | Nao | Responsavel operacional. |
| `notes` | text | Nao | Observacoes internas. |
| `cancelled_at`, `cancelled_by`, `cancel_reason` | audit | Nao | Usar apenas se evoluir para cancelamento explicito. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

Indices e unicidade:

- unico por `(company_id, law_unit_id, sector, sequence_year, sequence_number)`;
- indice por `(company_id, law_unit_id, law_case_id)`;
- indice por `(company_id, law_unit_id, status)`.

Regras:

- A sequencia e anual por unidade e setor.
- Geracao do proximo numero deve ocorrer em transacao.
- Alteracoes de numero, setor, processo ou status devem ser auditadas.

### `law_outgoing_letters`

Representa cartas expedidas.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LOL`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `law_case_id` | char(30) | Sim | Processo de origem da unidade. |
| `destination_court` | varchar | Sim | Comarca, vara ou orgao de destino. |
| `letter_type` | varchar | Sim | Tipo da carta expedida. |
| `status` | enum | Sim | `created`, `sent`, `received_at_destination`, `returned`, `closed`, `cancelled`. |
| `sent_at` | datetime | Nao | Data de envio. |
| `destination_received_at` | datetime | Nao | Data de recebimento no destino. |
| `returned_at` | datetime | Nao | Data de retorno/devolucao. |
| `closed_at` | datetime | Nao | Data de encerramento. |
| `destination_number` | varchar | Nao | Numero atribuido pela comarca ou orgao de destino. |
| `responsible_membership_id` | char(30) | Nao | Responsavel operacional. |
| `notes` | text | Nao | Observacoes internas. |
| `cancelled_at`, `cancelled_by`, `cancel_reason` | audit | Nao | Obrigatorios quando cancelada. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

Regras:

- Nao existe sequencia interna propria para carta expedida.
- `destination_number` e opcional e pode ser preenchido posteriormente.
- Carta expedida sempre deve possuir processo de origem.

### `law_tasks`

Representa prazos e pendencias.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LTK`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `target_type` | enum | Sim | `case`, `office`, `outgoing_letter`. |
| `target_id` | char(30) | Sim | Entidade relacionada. |
| `task_type` | enum | Sim | `deadline`, `pending_item`. |
| `title` | varchar | Sim | Titulo curto. |
| `description` | text | Nao | Detalhamento. |
| `responsible_membership_id` | char(30) | Nao | Responsavel. |
| `due_at` | datetime | Nao | Data limite. |
| `priority` | enum | Sim | `low`, `normal`, `high`, `urgent`. |
| `status` | enum | Sim | `open`, `in_progress`, `waiting`, `done`, `cancelled`, `overdue`. |
| `completed_at`, `completed_by` | audit | Nao | Preenchidos quando concluida. |
| `cancelled_at`, `cancelled_by`, `cancel_reason` | audit | Nao | Obrigatorios quando cancelada. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

Regras:

- `due_at` pode ser nulo para pendencia sem prazo.
- Prazos vencidos devem ser identificaveis por consulta e dashboard.
- Alteracao de data limite, responsavel, status ou cancelamento deve ser
  auditada.

### `law_alerts`

Representa alertas operacionais do Fokus Law.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LAL`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `alert_type` | enum | Sim | `deadline`, `confidentiality`, `datajud`, `operation`. |
| `severity` | enum | Sim | `low`, `medium`, `high`, `critical`. |
| `status` | enum | Sim | `open`, `acknowledged`, `resolved`, `dismissed`. |
| `target_type` | enum | Nao | `case`, `office`, `outgoing_letter`, `task`, `datajud_divergence`. |
| `target_id` | char(30) | Nao | Entidade relacionada. |
| `message` | varchar | Sim | Mensagem resumida. |
| `assigned_membership_id` | char(30) | Nao | Responsavel pelo tratamento. |
| `opened_at` | datetime | Sim | Data de abertura. |
| `resolved_at`, `resolved_by` | audit | Nao | Dados de resolucao. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

Regras:

- Alertas Law nao devem usar `platform_alerts`, que pertence ao Backoffice.
- Alertas devem respeitar unidade ativa e sigilo do processo relacionado.

### `law_confidential_case_accesses`

Registra autorizacao explicita para processos sigilosos.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LCA`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `law_case_id` | char(30) | Sim | Processo sigiloso. |
| `law_unit_membership_id` | char(30) | Sim | Usuario/vinculo autorizado. |
| `access_level` | enum | Sim | `view`, `operate`, `manage_confidentiality`. |
| `granted_by` | char(30) | Sim | Usuario que concedeu acesso. |
| `grant_reason` | varchar | Sim | Motivo da concessao. |
| `expires_at` | datetime | Nao | Validade opcional. |
| `revoked_at`, `revoked_by`, `revoke_reason` | audit | Nao | Dados de revogacao. |
| `status` | enum | Sim | `active`, `expired`, `revoked`. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

Regras:

- Acesso sigiloso e sempre explicito por processo.
- Concessao e revogacao devem gerar auditoria.
- Processo sigiloso deve continuar mascarado para usuarios sem acesso ativo.

### `law_audit_events`

Registra auditoria operacional do Fokus Law.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LAU`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `user_id` | char(30) | Sim | Usuario global que executou a acao. |
| `law_unit_membership_id` | char(30) | Nao | Perfil usado na unidade. |
| `entity_type` | varchar | Sim | Tipo da entidade auditada. |
| `entity_id` | char(30) | Sim | Entidade auditada. |
| `action` | varchar | Sim | Acao executada. |
| `reason` | varchar | Nao | Motivo, quando obrigatorio. |
| `before_values` | json | Nao | Valores anteriores minimizados. |
| `after_values` | json | Nao | Valores posteriores minimizados. |
| `metadata` | json | Nao | Metadados tecnicos minimos. |
| `created_at` | datetime | Sim | Data do evento. |

Regras:

- Auditoria Law e separada de `platform_audit_events`.
- Motivo e obrigatorio para sigilo, cancelamento, inativacao, prazo, mudanca de
  responsavel e alteracao que afete historico operacional relevante.
- Dados sensiveis devem ser mascarados ou minimizados em `before_values`,
  `after_values` e `metadata`.

### `law_support_requests`

Registra solicitacoes de suporte orientativo abertas dentro do produto.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LSR`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `requested_by` | char(30) | Sim | Usuario solicitante. |
| `category` | enum | Sim | `access_permissions`, `usage_question`, `technical_error`, `subscription_modules`. |
| `status` | enum | Sim | `open`, `in_review`, `answered`, `closed`, `cancelled`. |
| `description` | text | Sim | Descricao informada pelo usuario, sem dados sensiveis. |
| `sanitized_attachments` | json | Nao | Referencias a prints/anexos sanitizados. |
| `technical_metadata` | json | Nao | Metadados tecnicos minimos e sanitizados. |
| `answered_at`, `answered_by` | audit | Nao | Dados de resposta orientativa. |
| `closed_at`, `closed_by` | audit | Nao | Dados de encerramento. |
| `expires_at` | datetime | Sim | Retencao de 90 dias. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

Regras:

- Suporte e orientativo e nao concede acesso operacional ao Backoffice.
- Prints/anexos nao sanitizados nao devem ser armazenados.
- Metadados nao devem incluir dados juridicos sensiveis, payload Datajud bruto
  ou conteudo sigiloso.

### `law_datajud_syncs`

Registra consultas e sincronizacoes Datajud.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LDS`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `law_case_id` | char(30) | Nao | Processo relacionado, quando identificado. |
| `query_case_number` | varchar | Sim | Numero consultado. |
| `status` | enum | Sim | `requested`, `success`, `partial`, `failed`, `ignored`. |
| `result_summary` | json | Nao | Resumo sanitizado do retorno. |
| `applied_metadata` | json | Nao | Campos aplicados no processo. |
| `error_code` | varchar | Nao | Codigo de erro. |
| `error_message` | varchar | Nao | Mensagem resumida, sem payload bruto sensivel. |
| `requested_by` | char(30) | Nao | Usuario relacionado, quando houver; jobs automaticos podem usar ator de sistema. |
| `trigger_type` | enum | Sim | `case_created`, `case_moved`, `retry`. |
| `requested_at` | datetime | Sim | Data da consulta. |
| `finished_at` | datetime | Nao | Data de conclusao. |

Regras:

- Payload bruto do Datajud nao deve ser armazenado sem sanitizacao e necessidade
  explicita.
- Falha de sincronizacao nao pode corromper nem bloquear dados internos.
- Metadados aplicados devem ser auditaveis.

### `law_datajud_divergences`

Registra divergencias entre dados internos e Datajud.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LDD`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `law_case_id` | char(30) | Sim | Processo relacionado. |
| `law_datajud_sync_id` | char(30) | Nao | Sincronizacao que detectou a divergencia. |
| `field_name` | varchar | Sim | Campo divergente. |
| `internal_value` | json | Nao | Valor interno minimizado. |
| `external_value` | json | Nao | Valor externo minimizado. |
| `status` | enum | Sim | `open`, `reviewed`, `applied`, `dismissed`. |
| `reviewed_by`, `reviewed_at` | audit | Nao | Dados de revisao. |
| `resolution_reason` | varchar | Nao | Motivo da decisao. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

Regras:

- Divergencias nao devem sobrescrever campos operacionais automaticamente.
- Resolucao deve gerar auditoria quando alterar dados internos.

## Relacionamentos

```text
companies ──< company_units ──< law_units
companies ──< company_memberships ──< law_unit_memberships >── law_units

law_units ──< law_cases
law_units ──< law_parties
law_cases ──< law_case_parties >── law_parties

law_cases ──< law_offices
law_cases ──< law_outgoing_letters

law_cases ──< law_tasks
law_offices ──< law_tasks
law_outgoing_letters ──< law_tasks

law_units ──< law_alerts
law_units ──< law_audit_events
law_cases ──< law_confidential_case_accesses
law_units ──< law_support_requests
law_cases ──< law_datajud_syncs
law_datajud_syncs ──< law_datajud_divergences
```

Como `law_tasks` e `law_alerts` podem apontar para mais de um tipo de entidade,
o modelo deve validar a integridade polimorfica na camada de dominio e manter
indices por `company_id`, `law_unit_id`, `target_type` e `target_id`.

## Indices recomendados

- `law_units`: unico por `(company_id, company_unit_id)`.
- `law_unit_memberships`: unico por `(company_id, law_unit_id, company_membership_id)`.
- `law_cases`: indice por `(company_id, law_unit_id, case_number)`.
- `law_cases`: indice por `(company_id, law_unit_id, operational_status)`.
- `law_cases`: indice por `(company_id, law_unit_id, is_confidential)`.
- `law_parties`: indice por `(company_id, law_unit_id, display_name)`.
- `law_case_parties`: unico por `(company_id, law_case_id, law_party_id, role)`.
- `law_offices`: unico por `(company_id, law_unit_id, sector, sequence_year, sequence_number)`.
- `law_outgoing_letters`: indice por `(company_id, law_unit_id, law_case_id)`.
- `law_tasks`: indice por `(company_id, law_unit_id, status, due_at)`.
- `law_alerts`: indice por `(company_id, law_unit_id, status, severity)`.
- `law_audit_events`: indice por `(company_id, law_unit_id, entity_type, entity_id, created_at)`.
- `law_confidential_case_accesses`: indice por `(company_id, law_unit_id, law_case_id, status)`.
- `law_support_requests`: indice por `(company_id, law_unit_id, status, category)`.
- `law_datajud_syncs`: indice por `(company_id, law_unit_id, query_case_number, requested_at)`.
- `law_datajud_divergences`: indice por `(company_id, law_unit_id, law_case_id, status)`.

## Pseudo-DDL de referencia

```sql
CREATE TABLE law_units (
  id CHAR(30) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
  company_id CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  company_unit_id CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  court_code VARCHAR(30) NOT NULL,
  district_name VARCHAR(120) NOT NULL,
  unit_name VARCHAR(160) NOT NULL,
  jurisdiction VARCHAR(80) NOT NULL,
  status ENUM('active', 'suspended', 'archived') NOT NULL DEFAULT 'active',
  version INT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME(6) NOT NULL,
  created_by CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  updated_by CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  deleted_at DATETIME(6) NULL,
  deleted_by CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NULL,
  UNIQUE KEY uq_law_unit_company_unit (company_id, company_unit_id),
  UNIQUE KEY uq_law_unit_company_id (company_id, id),
  CONSTRAINT fk_law_unit_company FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE RESTRICT,
  CONSTRAINT fk_law_unit_company_unit FOREIGN KEY (company_id, company_unit_id)
    REFERENCES company_units (company_id, id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE law_cases (
  id CHAR(30) CHARACTER SET ascii COLLATE ascii_bin PRIMARY KEY,
  company_id CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  law_unit_id CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  case_number VARCHAR(40) NOT NULL,
  case_class VARCHAR(120) NOT NULL,
  subject VARCHAR(255) NULL,
  operational_status ENUM('active', 'pending', 'suspended', 'archived', 'cancelled') NOT NULL DEFAULT 'pending',
  official_status_code VARCHAR(80) NULL,
  official_status_text VARCHAR(255) NULL,
  priority VARCHAR(80) NULL,
  is_confidential BOOLEAN NOT NULL DEFAULT FALSE,
  relevant_dates JSON NULL,
  external_source VARCHAR(40) NULL,
  external_id VARCHAR(120) NULL,
  last_external_sync_at DATETIME(6) NULL,
  notes TEXT NULL,
  cancelled_at DATETIME(6) NULL,
  cancelled_by CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NULL,
  cancel_reason VARCHAR(255) NULL,
  version INT UNSIGNED NOT NULL DEFAULT 1,
  created_at DATETIME(6) NOT NULL,
  created_by CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  updated_at DATETIME(6) NOT NULL,
  updated_by CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  deleted_at DATETIME(6) NULL,
  deleted_by CHAR(30) CHARACTER SET ascii COLLATE ascii_bin NULL,
  UNIQUE KEY uq_law_case_company_id (company_id, id),
  KEY ix_law_case_number (company_id, law_unit_id, case_number),
  KEY ix_law_case_status (company_id, law_unit_id, operational_status),
  CONSTRAINT fk_law_case_unit FOREIGN KEY (company_id, law_unit_id)
    REFERENCES law_units (company_id, id) ON DELETE RESTRICT
) ENGINE=InnoDB;
```

As demais tabelas devem seguir os mesmos padroes de `company_id`, chave unica
auxiliar `(company_id, id)`, FKs compostas, `ON DELETE RESTRICT`, metadados de
criacao/alteracao e versionamento quando houver edicao concorrente.

## Consultas essenciais

- listar processos ativos por unidade e status;
- buscar processo por numero dentro da unidade;
- listar processos sigilosos apenas para usuarios autorizados;
- listar partes de um processo;
- listar processos vinculados a uma parte;
- listar oficios por unidade, setor, ano e status;
- obter proximo numero de oficio por unidade, setor e ano;
- listar cartas expedidas por processo, destino e status;
- listar tarefas vencidas e a vencer por responsavel;
- consolidar dashboard por unidade;
- listar alertas abertos por unidade;
- consultar auditoria por entidade;
- listar acessos sigilosos ativos por processo;
- listar solicitacoes de suporte por unidade, categoria e status;
- consultar historico de sincronizacao Datajud por processo;
- listar divergencias Datajud abertas.

## Regras para migrations

- Criar tabelas em ordem de dependencia: `law_units`, `law_unit_memberships`,
  `law_cases`, `law_parties`, `law_case_parties`, `law_offices`,
  `law_outgoing_letters`, `law_tasks`, `law_alerts`,
  `law_confidential_case_accesses`, `law_audit_events`,
  `law_support_requests`, `law_datajud_syncs`, `law_datajud_divergences`.
- Criar FKs compostas com `company_id` sempre que o pai tambem for empresarial.
- Nao usar cascade delete automatico.
- Incluir indices de listagem operacional antes de criar telas dependentes.
- Tratar sequencia de oficios em transacao.
- Proteger campos sensiveis em logs, auditoria e payloads de integracao.
- Validar no backend assinatura ativa, modulo contratado, unidade ativa,
  permissao e sigilo em todas as rotas.

## Criterios de aceite

- Todas as tabelas alvo possuem prefixo de ID definido.
- O modelo separa `company_units` de `law_units`.
- O modelo permite perfil Law por unidade.
- Processos sao entidade central e pertencem a empresa e unidade juridica.
- Cartas recebidas nao possuem tabela propria de expediente.
- Oficios possuem sequencia anual por unidade e setor.
- Cartas expedidas nao possuem numeracao interna propria.
- Prazos e pendencias usam `law_tasks`.
- Alertas Law usam `law_alerts`, separados de `platform_alerts`.
- Auditoria Law usa `law_audit_events`, separada de `platform_audit_events`.
- Acesso sigiloso usa `law_confidential_case_accesses`.
- Suporte orientativo usa `law_support_requests`.
- Datajud usa `law_datajud_syncs` e `law_datajud_divergences`.
- Dados operacionais internos nao sao sobrescritos automaticamente pelo Datajud.
- Sigilo pode ser aplicado de forma transversal em consultas e indicadores.
