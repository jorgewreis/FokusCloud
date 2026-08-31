# Gestao de tarefas do Fokus Law

## Objetivo

Gestao de Tarefas e o nome comercial do modulo que controla o trabalho a
cumprir no Fokus Law. Internamente, o nucleo tambem contempla fluxos e receitas
operacionais, enquanto o modulo `expedicoes` controla a saida documental gerada
ou acompanhada pela unidade.

No menu interno do sistema, o modulo deve aparecer como `Tarefas`.

Uma tarefa pode existir sem expedicao, gerar uma ou mais expedicoes, acompanhar
uma expedicao ja criada ou ser criada a partir de uma expedicao que exija
retorno, cumprimento ou conferencia.

## Escopo da v1

Faz parte do escopo:

- cadastrar tipos de tarefa permitidos pelo sistema;
- habilitar receitas operacionais por empresa ou unidade;
- vincular tarefas a processo, expedicao, prazo ou pendencia quando aplicavel;
- permitir que uma tarefa gere expedicao documental;
- permitir que uma expedicao gere tarefa de retorno, cumprimento ou conferencia;
- acompanhar responsavel, prioridade, status, prazo, alertas e historico;
- preservar sigilo herdado do processo relacionado.

Nao faz parte do escopo:

- transformar tarefas em documentos expedidos;
- substituir o modulo de expedicoes;
- automatizar integralmente sistemas judiciais externos;
- criar modelos avancados de documentos.

## Relacao com expedicoes

O modulo `tarefas_fluxos` e independente do modulo `expedicoes`, mas pode
orquestrar sua criacao e acompanhamento.

Vinculos permitidos:

- tarefa sem expedicao;
- expedicao sem tarefa previa;
- tarefa que gera uma expedicao;
- tarefa que gera varias expedicoes;
- expedicao que gera tarefa de retorno;
- expedicao que gera tarefa de cumprimento;
- expedicao que gera tarefa de conferencia.

Exemplos:

| Tarefa | Saida documental possivel |
| --- | --- |
| Expedir oficio | Expedicao do tipo `oficio`. |
| Expedir mandado | Expedicao do tipo `mandado`. |
| Expedir carta precatoria | Expedicao do tipo `carta_precatoria`. |
| Publicar edital | Expedicao do tipo `edital`. |
| Emitir guia de execucao penal | Expedicao do tipo `guia_execucao_penal`. |
| Elaborar ato ordinatorio | Expedicao do tipo `ato_ordinatorio`. |

## Receitas operacionais

Receitas operacionais definem como uma rotina deve se comportar.

Cada receita deve declarar:

- tipo de tarefa;
- tipo de expedicao gerada, quando houver;
- processo obrigatorio ou opcional;
- prazo e alerta padrao, quando houver;
- necessidade de retorno, cumprimento ou conferencia;
- perfis autorizados;
- status inicial e status de encerramento.

Empresa e unidade podem habilitar e ajustar receitas apenas dentro do conjunto
permitido pelo sistema.

## Criterios de aceite

- Tarefas e expedicoes permanecem nucleos tecnicos distintos.
- Tarefa pode gerar ou acompanhar expedicao sem duplicar dados documentais.
- Expedicao pode abrir tarefa posterior quando exigir retorno, cumprimento ou
  conferencia.
- Receitas operacionais controlam personalizacao por empresa ou unidade.
- Sigilo processual restringe tarefas, expedicoes, prazos e alertas vinculados.
