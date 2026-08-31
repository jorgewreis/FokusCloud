# Modelo de dados das expedicoes Law

## Objetivo

Definir o modelo relacional alvo do modulo `expedicoes`, nucleo documental
unico do Fokus Law.

Este documento complementa o [Modelo de dados do Fokus Law](fokus-law-data-model.md).
Destinatarios e orgaos de destino reutilizaveis estao detalhados em
[Modelo de dados da gestao de contatos Law](law-contacts-data-model.md).

## Principios

- Todas as tabelas possuem `company_id`.
- Tabelas operacionais possuem `law_unit_id`.
- Expedicoes processuais possuem `law_case_id`.
- Tipos definem regras de processo, destino, numeracao, numero externo, retorno
  e origem de criacao.
- Destino pode ser texto livre ou contato vinculado, conforme maturidade do
  cadastro e necessidade historica.
- Instancias representam setores ou origens operacionais da unidade.
- Numeracao interna e transacional por tipo, instancia e ano.
- Cartas recebidas nao entram neste modelo; elas permanecem em `law_cases`.
- Sigilo e herdado do processo vinculado.

## Prefixos

| Prefixo | Entidade |
| --- | --- |
| LET | Tipo de expedicao Law |
| LEI | Instancia de expedicao Law |
| LEX | Expedicao Law |
| LES | Sequencia de numeracao de expedicao Law |
| LEV | Vinculo expedicao-contato Law |

## `law_expedition_types`

Define os tipos de expedicao disponiveis.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LET`. |
| `company_id` | char(30) | Nao | Nulo para tipo padrao global; preenchido para tipo proprio da empresa. |
| `code` | varchar | Sim | Codigo estavel, como `oficio`, `mandado`, `edital` ou `carta_precatoria`. |
| `name` | varchar | Sim | Nome exibido. |
| `requires_case` | boolean | Sim | Exige processo vinculado. |
| `requires_destination` | boolean | Sim | Exige destino ou destinatario. |
| `uses_internal_number` | boolean | Sim | Controla numeracao interna. |
| `accepts_external_number` | boolean | Sim | Permite numero externo posterior. |
| `tracks_return` | boolean | Sim | Indica acompanhamento de retorno, cumprimento ou conferencia. |
| `creation_mode` | enum | Sim | `direct`, `task`, `both`. |
| `status_flow` | json | Sim | Status permitidos para o tipo. |
| `status` | enum | Sim | `active`, `inactive`, `archived`. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

## `law_expedition_instances`

Representa uma origem operacional de expedicoes na unidade.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LEI`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `name` | varchar | Sim | Nome da instancia, como Cartorio ou Gabinete. |
| `sector_code` | varchar | Sim | Codigo do setor/origem operacional. |
| `allowed_type_codes` | json | Sim | Tipos aceitos na instancia. |
| `number_prefix` | varchar | Nao | Prefixo ou serie quando aplicavel. |
| `status` | enum | Sim | `active`, `inactive`, `archived`. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

## `law_expeditions`

Representa o registro operacional expedido.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LEX`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `law_case_id` | char(30) | Condicional | Obrigatorio quando o tipo exige processo. |
| `law_expedition_type_id` | char(30) | Sim | Tipo de expedicao. |
| `law_expedition_instance_id` | char(30) | Sim | Instancia/setor de origem. |
| `sequence_year` | smallint | Condicional | Ano da numeracao quando houver numero interno. |
| `sequence_number` | int | Condicional | Numero interno quando o tipo exigir. |
| `external_number` | varchar | Nao | Numero atribuido por orgao de destino, quando permitido. |
| `destination` | varchar | Condicional | Destino ou destinatario. |
| `destination_contact_id` | char(30) | Nao | Contato principal de destino quando houver cadastro reutilizavel. |
| `destination_snapshot` | json | Nao | Nome, endereco ou canal preservados para historico documental. |
| `subject` | varchar | Sim | Assunto resumido. |
| `status` | enum | Sim | `created`, `signed`, `sent`, `received`, `returned`, `closed`, `cancelled`. |
| `issued_at` | datetime | Nao | Data de expedicao ou assinatura. |
| `sent_at` | datetime | Nao | Data de envio. |
| `received_at` | datetime | Nao | Data de recebimento ou ciencia. |
| `returned_at` | datetime | Nao | Data de retorno, quando houver. |
| `reviewed_at` | datetime | Nao | Data de conferencia, quando houver. |
| `closed_at` | datetime | Nao | Data de encerramento. |
| `responsible_membership_id` | char(30) | Nao | Responsavel operacional. |
| `notes` | text | Nao | Observacoes internas. |
| `cancelled_at`, `cancelled_by`, `cancel_reason` | audit | Nao | Obrigatorios quando cancelada. |
| `version` | int | Sim | Controle de concorrencia. |
| `created_at`, `created_by` | audit | Sim | Metadados de criacao. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

## `law_expedition_number_sequences`

Controla sequencias transacionais de tipos numerados.

| Campo | Tipo | Obrigatorio | Regra |
| --- | --- | --- | --- |
| `id` | char(30) | Sim | Prefixo `LES`. |
| `company_id` | char(30) | Sim | Empresa proprietaria. |
| `law_unit_id` | char(30) | Sim | Unidade juridica. |
| `law_expedition_type_id` | char(30) | Sim | Tipo numerado. |
| `law_expedition_instance_id` | char(30) | Sim | Instancia numerada. |
| `sequence_year` | smallint | Sim | Ano de referencia. |
| `next_number` | int | Sim | Proximo numero disponivel. |
| `updated_at`, `updated_by` | audit | Sim | Metadados de alteracao. |

## `law_expedition_contacts`

Relaciona expedicoes a contatos usados como destinatario, orgao de destino,
unidade externa, responsavel por recebimento ou copia.

O detalhamento do modelo esta em [Modelo de dados da gestao de contatos Law](law-contacts-data-model.md).

Regras:

- A expedicao pode ter um contato principal de destino e contatos adicionais.
- O vinculo nao substitui o snapshot historico necessario para prova do envio.
- Sigilo processual deve ser aplicado antes de exibir contatos de expedicoes
  vinculadas a processo sigiloso.

## Indices recomendados

- `law_expedition_types`: unico por `(company_id, code)`.
- `law_expedition_instances`: unico por `(company_id, law_unit_id, sector_code)`.
- `law_expeditions`: unico por `(company_id, law_unit_id, law_expedition_type_id, law_expedition_instance_id, sequence_year, sequence_number)` quando `sequence_number` existir.
- `law_expeditions`: indice por `(company_id, law_unit_id, law_case_id)`.
- `law_expeditions`: indice por `(company_id, law_unit_id, status)`.
- `law_expeditions`: indice por `(company_id, law_unit_id, law_expedition_type_id, status)`.
- `law_expedition_contacts`: indice por `(company_id, law_expedition_id, law_contact_id)`.
- `law_expedition_number_sequences`: unico por `(company_id, law_unit_id, law_expedition_type_id, law_expedition_instance_id, sequence_year)`.

## Regras para migrations

- Criar tipos antes de instancias e expedicoes.
- Criar sequencias antes do fluxo que gera numeros internos.
- Criar tabelas de tarefas e fluxos antes dos vinculos tarefa-expedicao quando
  esse modulo for implementado.
- Usar FKs compostas com `company_id` sempre que o pai tambem for empresarial.
- Nao usar cascade delete automatico.
- Validar no dominio as regras condicionais de processo, destino, numero
  interno e numero externo.
- Tratar incremento de numero em transacao.

## Criterios de aceite

- `law_offices` e `law_outgoing_letters` nao sao tabelas alvo da v1.
- Oficios, mandados, cartas, editais, guias e atos ordinatorios usam
  `law_expeditions`.
- Prazos, tarefas de acompanhamento e alertas apontam para `target_type = expedition`.
- A numeracao de oficios e preservada por configuracao do tipo.
- Tipos podem exigir retorno, cumprimento ou conferencia.
- Cartas recebidas permanecem fora do modelo de expedicoes.
