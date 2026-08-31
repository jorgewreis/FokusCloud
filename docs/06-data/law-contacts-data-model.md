# Modelo de dados da gestao de contatos Law

## Objetivo

Definir o modelo conceitual alvo da Gestao de Contatos no Fokus Law.

## Principios

- Gestao de Contatos e o nome comercial do modulo.
- `Contatos` e o rotulo interno no menu.
- O nucleo de contatos deve substituir a ideia restrita de partes isoladas.
- Parte processual, advogado, destinatario e instituicao sao papeis ou
  classificacoes contextuais.
- O contato deve ser reutilizavel por processos, expedicoes e tarefas.
- Dados pessoais devem ser minimizados, protegidos e auditados.
- Contatos vinculados a processo sigiloso herdam restricoes no contexto do
  processo.

## Prefixos

| Prefixo | Entidade |
| --- | --- |
| LCO | Contato Law |
| LDR | Endereco de contato Law |
| LCN | Canal de contato Law |
| LCV | Vinculo processo-contato Law |
| LEV | Vinculo expedicao-contato Law |
| LTV | Vinculo tarefa-contato Law |

## Entidades alvo

### `law_contacts`

Cadastro principal de pessoas, instituicoes, orgaos e unidades externas.

Campos conceituais:

- `id`;
- `company_id`;
- `law_unit_id`, quando o contato for restrito a uma unidade;
- `display_name`;
- `legal_name`;
- `contact_type`;
- `document_type`;
- `document_number`;
- `oab_number`;
- `oab_state`;
- `status`;
- `tags`;
- `notes`;
- metadados de criacao, alteracao, inativacao e mesclagem.

Valores iniciais de `contact_type`:

- `person`;
- `organization`;
- `lawyer`;
- `law_firm`;
- `public_body`;
- `court_unit`;
- `police_unit`;
- `prosecutor_office`;
- `public_defender`;
- `expert`;
- `unknown`.

### `law_contact_addresses`

Enderecos vinculados ao contato.

Campos conceituais:

- `id`;
- `company_id`;
- `law_contact_id`;
- `address_type`;
- `postal_code`;
- `street`;
- `number`;
- `complement`;
- `district`;
- `city`;
- `state`;
- `country`;
- `is_primary`;
- `status`.

### `law_contact_channels`

Telefones, e-mails e outros canais de comunicacao.

Campos conceituais:

- `id`;
- `company_id`;
- `law_contact_id`;
- `channel_type`;
- `channel_value`;
- `is_primary`;
- `is_verified`;
- `status`.

Valores iniciais de `channel_type`:

- `phone`;
- `mobile`;
- `email`;
- `whatsapp`;
- `website`;
- `other`.

### `law_case_contacts`

Vinculo contextual entre contato e processo.

Substitui o conceito restrito de `law_case_parties` no modelo alvo, mantendo
compatibilidade conceitual com papeis processuais.

Campos conceituais:

- `id`;
- `company_id`;
- `law_unit_id`;
- `law_case_id`;
- `law_contact_id`;
- `case_role`;
- `role_detail`;
- `status`;
- metadados de auditoria.

Valores iniciais de `case_role`:

- `author`;
- `defendant`;
- `victim`;
- `witness`;
- `lawyer`;
- `prosecutor`;
- `defender`;
- `representative`;
- `interested`;
- `origin_body`;
- `other`.

### `law_expedition_contacts`

Vinculo contextual entre contato e expedicao.

Campos conceituais:

- `id`;
- `company_id`;
- `law_unit_id`;
- `law_expedition_id`;
- `law_contact_id`;
- `expedition_role`;
- `snapshot_name`;
- `snapshot_address`;
- `snapshot_channel`;
- `status`;
- metadados de auditoria.

Valores iniciais de `expedition_role`:

- `recipient`;
- `destination_body`;
- `destination_court`;
- `external_responsible`;
- `receiving_person`;
- `copy_recipient`;
- `other`.

### `law_task_contacts`

Vinculo opcional entre contato e tarefa.

Campos conceituais:

- `id`;
- `company_id`;
- `law_unit_id`;
- `law_task_id`;
- `law_contact_id`;
- `task_contact_role`;
- `status`;
- metadados de auditoria.

## Regras

- Papeis ficam nos vinculos, nao no contato principal.
- O mesmo contato pode ter papeis diferentes em processos, expedicoes e tarefas.
- Expedicoes devem preservar snapshot minimo do destinatario quando o historico
  documental exigir.
- Inativar contato nao remove vinculos historicos.
- Mesclar contatos deve registrar contato origem, contato destino, motivo e
  usuario responsavel.
- Documentos e canais devem ser mascarados em listas quando a permissao nao
  autorizar exibicao completa.
- Consultas em contexto de processo sigiloso devem aplicar o nivel de sigilo do
  processo antes de retornar dados de contato.

## Relacao com tabelas existentes

No modelo alvo, `law_contacts` substitui o uso restrito de `law_parties` como
cadastro principal. `law_case_contacts` substitui `law_case_parties` como vinculo
processual mais amplo.

Se houver necessidade de compatibilidade durante migracao futura,
`law_parties` pode ser tratado como legado ou visao derivada, mas novas
funcionalidades devem preferir o nucleo de contatos.

## Criterios de aceite

- O modelo possui cadastro unico de contatos reutilizaveis.
- Partes processuais sao papeis de contato em processo.
- Destinatarios de expedicao sao papeis de contato em expedicao.
- Contatos podem ter multiplos enderecos e canais.
- Sigilo processual restringe contatos quando consultados pelo contexto do
  processo.
- O modelo permite inativacao e mesclagem sem perder historico.
