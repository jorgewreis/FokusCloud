# Modelo de dados da gestao processual Law

## Objetivo

Definir o modelo conceitual alvo da Gestao Processual no Fokus Law.

Este documento complementa o [Modelo de dados do Fokus Law](fokus-law-data-model.md).

## Principios

- Processos sao a entidade central do Fokus Law.
- Dados oficiais e dados internos devem permanecer separados.
- Datajud atualiza apenas metadados oficiais sincronizaveis.
- Tags informativas nao substituem classe, prioridade, sigilo ou status.
- Sigilo deve ser representado por nivel no modelo alvo.
- Cartas recebidas permanecem como classe processual.

## Extensoes de `law_cases`

Campos alvo:

- `case_number`;
- `case_class`;
- `subjects`;
- `legal_basis`;
- `filing_date`;
- `distribution_date`;
- `distribution_data`;
- `official_status_code`;
- `official_status_text`;
- `operational_status`;
- `operational_priority`;
- `confidentiality_level`;
- `internal_tags`;
- `responsible_membership_id`;
- `relevant_dates`;
- `notes`.

## Niveis de sigilo

Valores iniciais de `confidentiality_level`:

- `public_internal`;
- `unit_restricted`;
- `case_confidential`;
- `enhanced_confidential`.

Quando o nivel exigir autorizacao explicita, o acesso deve ser registrado em
`law_confidential_case_accesses`.

## Tags processuais

Tags podem ser armazenadas como estrutura propria ou como JSON no modelo inicial,
desde que permitam:

- codigo estavel;
- nome exibido;
- cor ou categoria opcional;
- escopo por empresa ou unidade;
- status ativo/inativo.

## Linha do tempo

A linha do tempo pode ser materializada futuramente ou composta por consulta nos
registros relacionados:

- movimentacoes oficiais sincronizadas;
- tarefas;
- expedicoes;
- prazos e pendencias;
- partes;
- auditoria operacional relevante.

## Criterios de aceite

- O modelo de processos diferencia status oficial e operacional.
- `confidentiality_level` e o campo alvo para sigilo.
- `is_confidential` pode existir apenas como compatibilidade derivada quando
  necessario.
- Tags permanecem informativas.
- Datajud nao sobrescreve campos operacionais.
- Cartas recebidas usam `case_class`.
