# Gestao processual do Fokus Law

## Objetivo

Gestao Processual e o nome comercial do modulo central do Fokus Law. No menu
interno do sistema, o modulo deve aparecer como `Processos`.

O modulo organiza os processos da unidade juridica e serve como eixo para
partes, tarefas, expedicoes, prazos, sigilo, historico e indicadores.

## Escopo da v1

Faz parte do escopo:

- numero do processo, preferencialmente CNJ quando houver;
- classe processual;
- assuntos, artigos ou capitulacoes;
- prioridades operacionais;
- niveis de sigilo;
- dados de autuacao e distribuicao;
- situacao processual oficial;
- situacao operacional interna;
- integracao com Datajud;
- partes processuais;
- tags informativas;
- linha do tempo do processo;
- vinculos com tarefas, expedicoes, prazos e pendencias.

Cartas recebidas continuam sendo tratadas como classe processual dentro de
Processos, nao como expedicao.

## Dados oficiais e dados internos

O modulo deve separar dados oficiais de dados operacionais internos.

| Tipo de dado | Exemplos | Regra |
| --- | --- | --- |
| Oficial/Datajud | classe, assunto, orgao julgador, movimentacoes publicas, situacao oficial | Pode ser sincronizado. |
| Operacional interno | responsavel, prioridade, tags, observacoes, status de trabalho | Nao deve ser sobrescrito pelo Datajud. |
| Sensivel | sigilo, partes, observacoes restritas, dados protegidos | Deve respeitar permissao e mascaramento. |
| Relacional | partes, tarefas, expedicoes, prazos e pendencias | Deve permanecer vinculado ao processo. |

## Status oficial e status operacional

O status oficial representa a situacao externa sincronizada ou informada a
partir de fonte oficial.

O status operacional representa a leitura interna da unidade, como:

- ativo;
- pendente de providencia;
- aguardando expedicao;
- aguardando retorno;
- suspenso internamente;
- concluido internamente;
- arquivado internamente;
- cancelado.

O Datajud nunca deve controlar automaticamente o status operacional.

## Tags informativas

Tags informativas sao marcadores configuraveis para facilitar triagem,
filtros, filas e indicadores.

Exemplos:

- reu preso;
- urgente para pauta;
- aguardando Ministerio Publico;
- mutirao;
- processo monitorado;
- carta recebida;
- prioridade de gabinete.

Tags nao substituem prioridade, sigilo, classe processual nem status.

## Niveis de sigilo

O modulo deve prever niveis de sigilo, em vez de depender apenas de marcador
binario.

Niveis iniciais:

- `public_internal`: publico para usuarios autorizados da unidade;
- `unit_restricted`: restrito a perfis operacionais da unidade;
- `case_confidential`: sigiloso com autorizacao especifica por processo;
- `enhanced_confidential`: sigilo reforcado quando a regra da unidade exigir.

Processos em nivel sigiloso devem restringir partes, tarefas, expedicoes,
prazos, buscas, indicadores e exportacoes.

## Linha do tempo

A linha do tempo do processo deve consolidar:

- movimentacoes oficiais sincronizadas;
- tarefas criadas, concluidas ou canceladas;
- expedicoes geradas, enviadas, retornadas ou encerradas;
- prazos e pendencias;
- alteracoes de partes;
- mudancas de prioridade, tag ou sigilo;
- eventos de auditoria relevantes.

## Relacao com receitas operacionais

Processos podem sugerir tarefas ou expedicoes com base em classe, assunto, tag,
prioridade ou situacao operacional. Essas sugestoes nao devem executar acao
automaticamente sem receita operacional habilitada e regra aprovada.

## Criterios de aceite

- O nome comercial e Gestao Processual.
- O menu interno usa o rotulo Processos.
- Dados oficiais e dados operacionais internos ficam separados.
- Tags informativas nao substituem prioridade nem sigilo.
- Niveis de sigilo substituem o uso de sigilo apenas binario no modelo alvo.
- A linha do tempo consolida eventos oficiais, operacionais e relacionais.
- Cartas recebidas permanecem como classe processual.
