# Modelo de dados de tarefas e fluxos Law

## Objetivo

Definir o modelo conceitual alvo para tarefas, receitas operacionais e vinculos
com expedicoes no Fokus Law.

Este documento complementa:

- [Modelo de dados do Fokus Law](fokus-law-data-model.md);
- [Modelo de dados das expedicoes Law](law-expeditions-data-model.md).

## Principios

- Tarefas representam trabalho a cumprir.
- Expedicoes representam documentos expedidos ou acompanhados.
- Receitas operacionais conectam tarefa, expedicao, prazo e alerta.
- Empresa e unidade personalizam apenas receitas e tipos permitidos pelo
  sistema.
- Sigilo e herdado do processo relacionado.

## Prefixos

| Prefixo | Entidade |
| --- | --- |
| LTT | Tipo de tarefa Law |
| LOR | Receita operacional Law |
| LTE | Vinculo tarefa-expedicao Law |

## Entidades alvo

### `law_task_types`

Define tipos de tarefa permitidos pelo sistema ou pela empresa.

Campos principais:

- `id`;
- `company_id`, nulo para tipo padrao global;
- `code`;
- `name`;
- `requires_case`;
- `status`;
- metadados de criacao e alteracao.

### `law_operation_recipes`

Define como uma tarefa pode gerar, acompanhar ou ser gerada por expedicao.

Campos principais:

- `id`;
- `company_id`;
- `law_unit_id`, nulo quando a receita for padrao da empresa;
- `task_type_id`;
- `expedition_type_id`, nulo quando a tarefa nao gerar expedicao;
- `creates_expedition`;
- `allows_direct_expedition`;
- `creates_followup_task`;
- `followup_reason`, como `return`, `fulfillment` ou `review`;
- `default_due_days`;
- `default_alert_days`;
- `allowed_roles`;
- `status`.

### `law_task_expeditions`

Relaciona tarefas e expedicoes sem duplicar dados.

Campos principais:

- `id`;
- `company_id`;
- `law_unit_id`;
- `law_task_id`;
- `law_expedition_id`;
- `relation_type`, como `created`, `tracks`, `followup` ou `review`;
- metadados de criacao.

## Relacao com `law_tasks`

`law_tasks` continua sendo a tabela operacional de prazos e pendencias da v1,
mas deve evoluir para aceitar:

- `law_task_type_id`;
- `law_operation_recipe_id`;
- `target_type = case`, `expedition` ou `task`;
- `target_id` com validacao de integridade no dominio.

## Criterios de aceite

- Tarefas e expedicoes possuem dados proprios e nao duplicados.
- Uma tarefa pode se relacionar a nenhuma, uma ou varias expedicoes.
- Uma expedicao pode criar tarefa posterior por receita operacional.
- Receitas operacionais controlam personalizacao por empresa ou unidade.
- O modelo preserva `company_id`, `law_unit_id`, FKs compostas e `ON DELETE RESTRICT`.
