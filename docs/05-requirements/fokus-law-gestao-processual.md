# Requisitos da gestao processual

## Objetivo

Definir requisitos do modulo `processos`, cujo nome comercial deve ser Gestao
Processual e cujo rotulo no menu interno deve ser Processos.

## Requisitos funcionais

| Codigo | Requisito | Criterio de aceite |
| --- | --- | --- |
| RF-GPR-001 | O sistema deve cadastrar processos como entidade central da unidade. | Um processo e criado com numero, classe, unidade, situacao, dados de autuacao e distribuicao. |
| RF-GPR-002 | O sistema deve registrar assuntos, artigos ou capitulacoes. | O processo permite filtrar e consultar por assunto juridico e base legal informada. |
| RF-GPR-003 | O sistema deve separar situacao oficial de status operacional interno. | Alteracao Datajud nao sobrescreve status operacional da unidade. |
| RF-GPR-004 | O sistema deve permitir prioridades operacionais. | Prioridade aparece em listas, filtros, tarefas e indicadores sem se confundir com sigilo. |
| RF-GPR-005 | O sistema deve permitir tags informativas configuraveis. | Tags podem ser aplicadas e filtradas sem substituir classe, prioridade, sigilo ou status. |
| RF-GPR-006 | O sistema deve controlar niveis de sigilo. | Processo pode ter nivel publico interno, restrito a unidade, sigiloso por autorizacao ou sigilo reforcado. |
| RF-GPR-007 | O sistema deve integrar dados processuais basicos com Datajud. | Metadados oficiais sao sincronizados sem sobrescrever dados operacionais internos. |
| RF-GPR-008 | O sistema deve vincular partes processuais reutilizaveis. | A mesma parte pode ser usada em mais de um processo da unidade com papel proprio por processo. |
| RF-GPR-009 | O sistema deve exibir linha do tempo do processo. | A linha do tempo consolida movimentacoes, tarefas, expedicoes, prazos, partes e auditoria relevante. |
| RF-GPR-010 | O sistema deve permitir sugestoes por receitas operacionais. | Classe, assunto, tag ou status podem sugerir tarefas/expedicoes sem execucao automatica indevida. |
| RF-GPR-011 | O sistema deve tratar cartas recebidas como classe processual. | Carta recebida aparece em Processos e nao cria expedicao propria. |

## Regras de negocio

- `processos` e o codigo tecnico do modulo.
- Gestao Processual e o nome comercial.
- Processos e o rotulo do menu interno.
- Dados oficiais sincronizados ficam separados dos dados operacionais internos.
- Datajud prevalece apenas em metadados oficiais sincronizaveis.
- Dados internos como responsavel, prioridade, tags, observacoes e status
  operacional nao podem ser sobrescritos por integracao externa.
- Tags sao informativas e nao substituem sigilo, prioridade, classe ou status.
- Niveis de sigilo devem ser avaliados antes de exibir partes, tarefas,
  expedicoes, prazos, indicadores e exportacoes.
- Receitas operacionais podem sugerir proximas acoes, mas execucoes dependem de
  regra habilitada e usuario autorizado.

## Interfaces minimas

- lista de processos;
- detalhe do processo;
- cadastro e edicao de processo;
- partes do processo;
- tags e prioridades;
- controle de sigilo;
- linha do tempo;
- aba ou secao de tarefas;
- aba ou secao de expedicoes;
- aba ou secao de prazos e pendencias;
- resumo de sincronizacao Datajud.

## Criterios de aceite

- Gestao Processual aparece como nome comercial e Processos como rotulo interno.
- Processo possui classe, assuntos/artigos, prioridade, sigilo, autuacao e
  distribuicao.
- Status oficial e status operacional sao independentes.
- Tags filtram e classificam sem substituir regras estruturais.
- Linha do tempo exibe eventos relevantes sem vazar dados sigilosos.
- Datajud nao sobrescreve dados internos.
- Cartas recebidas permanecem no fluxo de processos.
