# Requisitos da Gestao de Audiencias

O modulo `audiencias` deve permitir criar, consultar, editar e acompanhar
audiencias conforme a variante contratada.

## Requisitos

- Registrar data, horario, tipo, modalidade, local, sala, responsavel e status.
- Vincular audiencia a processo e participantes de Contatos quando aplicavel.
- Controlar status `scheduled`, `confirmed`, `in_progress`, `completed`,
  `cancelled`, `rescheduled` e `not_held`.
- Preservar historico de toda alteracao de status.
- Criar alertas e tarefas relacionadas a preparacao, alteracao e encerramento.
- Permitir integracao com Expedicoes para convocacoes, intimacoes e comunicacoes.
- Respeitar sigilo processual e permissao da unidade.
- Disponibilizar o add-on externo somente com Gestao de Audiencias contratada.

## Acesso externo

O token externo deve ser temporario, revogavel, vinculado a uma audiencia e
incapaz de acessar dados internos ou outras audiencias.
