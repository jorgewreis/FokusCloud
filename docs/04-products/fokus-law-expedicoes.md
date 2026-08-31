# Gestao de expedicoes do Fokus Law

## Objetivo

Gestao de Expedicoes e o nome comercial do modulo do Fokus Law para registrar, acompanhar e
auditar documentos expedidos pela unidade juridica.

No menu interno do sistema, o modulo deve aparecer como `Expedicoes`.

Na v1, ele substitui modulos documentais separados por um nucleo unico com
tipos configuraveis. Oficios, mandados, cartas precatorias, cartas rogatorias,
cartas de ordem, editais, guias de execucao e atos ordinatorios sao tipos
iniciais de expedicao.

## Escopo da v1

Faz parte do escopo:

- cadastrar tipos de expedicao habilitados para a unidade;
- configurar instancias de expedicao por unidade, setor ou origem operacional;
- registrar expedicoes vinculadas a processo quando forem processuais;
- controlar numeracao interna quando o tipo exigir;
- permitir numero externo posterior quando o tipo aceitar;
- acompanhar status, datas, responsavel, destino, assunto e historico;
- vincular prazos, pendencias e alertas a expedicoes;
- conectar expedicoes a tarefas e receitas operacionais;
- herdar sigilo do processo vinculado.

Nao faz parte do escopo:

- tratar cartas recebidas como expedicao;
- gerar automaticamente documentos a partir de modelos;
- substituir sistemas judiciais oficiais;
- criar fluxo avancado de assinatura ou remessa externa automatica.

Cartas recebidas continuam pertencendo ao modulo de processos como classe
processual.

## Tipos iniciais

Tipos iniciais da v1:

| Tipo | Regra principal |
| --- | --- |
| `oficio` | Exige numeracao interna por unidade, instancia e ano. |
| `mandado` | Pode exigir numeracao interna e acompanhamento de cumprimento. |
| `carta_precatoria` | Exige processo de origem e destino; nao exige numeracao interna. |
| `carta_rogatoria` | Exige processo de origem e destino; nao exige numeracao interna. |
| `carta_de_ordem` | Exige processo de origem e destino; nao exige numeracao interna. |
| `edital` | Pode ser expedido diretamente ou por tarefa de publicacao. |
| `guia_execucao_penal` | Exige processo e pode acompanhar remessa, retorno ou conferencia. |
| `guia_execucao_comum` | Exige processo e pode acompanhar remessa, retorno ou conferencia. |
| `ato_ordinatorio` | Pode registrar ato expedido sem retorno obrigatorio. |

Outros tipos podem ser cadastrados desde que declarem as mesmas regras basicas:
vinculo processual, destino, numeracao, numero externo, retorno, datas, status
e se pode ser criado diretamente ou apenas por tarefa/fluxo.

## Instancias de expedicao

Uma unidade pode possuir varias instancias de expedicao, como Cartorio,
Gabinete ou outro setor cadastrado.

Cada instancia deve registrar:

- unidade juridica;
- setor ou origem operacional;
- tipos de expedicao aceitos;
- responsaveis;
- regras de permissao;
- serie ou prefixo de numeracao quando aplicavel;
- status da instancia.

Instancias permitem que setores diferentes mantenham controles independentes
sem duplicar modulos comerciais.

## Numeracao

A numeracao e configuravel por tipo e instancia.

Regras obrigatorias:

- oficio usa numeracao interna obrigatoria, anual, por unidade e instancia;
- a geracao do proximo numero deve ocorrer em transacao;
- cartas expedidas nao exigem numeracao interna propria na v1;
- tipos que aceitam numero externo podem receber esse dado posteriormente;
- alteracao manual de numero interno, instancia, processo ou status deve gerar
  auditoria.

## Status operacional

Status basicos da v1:

- `created`;
- `signed`;
- `sent`;
- `received`;
- `returned`;
- `closed`;
- `cancelled`.

Cada tipo pode restringir quais status usa, mas a tabela operacional deve
suportar o conjunto comum para evitar novas tabelas por tipo.

## Relacao com tarefas e fluxos

Expedicoes controlam o documento expedido ou acompanhado. Tarefas e fluxos
controlam o trabalho a cumprir.

Uma expedicao pode:

- ser criada diretamente quando o tipo permitir;
- ser gerada por uma tarefa;
- gerar tarefa posterior de retorno, cumprimento ou conferencia;
- manter vinculo com uma ou varias tarefas sem duplicar dados.

As regras de orquestracao ficam no modulo `tarefas_fluxos`, detalhado em
[Gestao de tarefas do Fokus Law](fokus-law-tarefas-fluxos.md).

## Criterios de aceite

- Oficios, cartas, mandados, editais, guias e atos ordinatorios deixam de ser
  modulos documentais autonomos.
- O catalogo comercial usa `expedicoes` como modulo tecnico.
- Oficio e tratado como tipo de expedicao com numeracao interna obrigatoria.
- Cartas precatoria, rogatoria e de ordem, mandados, editais, guias e atos
  ordinatorios sao tipos de expedicao.
- Cartas expedidas podem receber numero externo posterior.
- Tipos podem acompanhar retorno ou cumprimento quando configurados.
- Cartas recebidas continuam como classe de processo.
- Sigilo processual restringe listas, detalhes, prazos, alertas, indicadores e
  exportacoes de expedicoes.
