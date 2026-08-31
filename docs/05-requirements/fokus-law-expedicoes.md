# Requisitos da gestao de expedicoes

## Objetivo

Definir os requisitos especificos do modulo `expedicoes`, cujo nome comercial
deve ser Gestao de Expedicoes e cujo rotulo no menu interno deve ser
Expedicoes.

## Requisitos funcionais

| Codigo | Requisito | Criterio de aceite |
| --- | --- | --- |
| RF-EXP-001 | O sistema deve permitir cadastrar tipos de expedicao. | A unidade pode habilitar tipos como oficio, mandado, carta precatoria, carta rogatoria, carta de ordem, edital, guia de execucao e ato ordinatorio. |
| RF-EXP-002 | O sistema deve permitir configurar instancias de expedicao por unidade e setor. | Cartorio e Gabinete podem operar instancias independentes com responsaveis e numeracao proprios. |
| RF-EXP-003 | O sistema deve criar expedicoes processuais apenas com processo vinculado. | Uma expedicao processual nao pode ser salva sem processo da unidade ativa. |
| RF-EXP-004 | O sistema deve controlar numeracao interna quando o tipo exigir. | Oficios recebem numero anual por unidade e instancia em transacao. |
| RF-EXP-005 | O sistema deve permitir tipos sem numeracao interna. | Cartas, editais, guias ou atos podem dispensar sequencia interna quando configurados assim. |
| RF-EXP-006 | O sistema deve permitir numero externo posterior quando o tipo aceitar. | O numero atribuido pelo orgao de destino pode ser preenchido depois por usuario autorizado. |
| RF-EXP-007 | O sistema deve registrar destino, assunto, status, responsavel, datas e historico. | A tela de expedicao exibe dados atuais e alteracoes relevantes. |
| RF-EXP-008 | O sistema deve permitir cancelar expedicoes com motivo obrigatorio. | Cancelamento preserva registro, usuario, data e motivo. |
| RF-EXP-009 | O sistema deve permitir vincular prazos e pendencias a expedicoes. | Uma expedicao pode possuir prazo ou pendencia propria. |
| RF-EXP-010 | O sistema deve filtrar filas, dashboards e relatorios por tipo, instancia, status e responsavel. | A unidade acompanha todas as expedicoes em uma fila unica com filtros. |
| RF-EXP-011 | O sistema deve permitir que tarefas gerem ou acompanhem expedicoes. | Uma tarefa de expedir mandado, edital, guia, carta ou oficio pode criar expedicao vinculada. |
| RF-EXP-012 | O sistema deve permitir que expedicoes gerem tarefas posteriores. | Uma expedicao com retorno, cumprimento ou conferencia configurada cria tarefa vinculada quando a receita exigir. |
| RF-EXP-013 | O sistema deve permitir usar contatos em expedicoes. | Destinatario, orgao de destino, unidade externa ou responsavel por recebimento pode ser selecionado da Gestao de Contatos. |

## Regras de negocio

- O modulo comercial e `expedicoes`.
- Oficio e tipo de expedicao, nao modulo proprio.
- Carta precatoria, carta rogatoria, carta de ordem, mandado, edital, guia de
  execucao e ato ordinatorio sao tipos de expedicao.
- Cartas recebidas nao sao expedicoes; permanecem como classe de processo.
- Expedicoes processuais exigem processo de origem da unidade ativa.
- Cada tipo declara se exige processo, destino, numeracao interna, se aceita
  numero externo posterior, se acompanha retorno e se pode ser criado
  diretamente ou apenas por tarefa/fluxo.
- A numeracao de tipos numerados deve ser independente por unidade, instancia e
  ano.
- Alteracao de tipo, instancia, numero, processo vinculado, destino, status,
  responsavel ou cancelamento deve ser auditada.
- Destinatarios e orgaos de destino devem usar contatos quando houver cadastro
  reutilizavel disponivel.
- A expedicao deve preservar snapshot minimo do destinatario quando necessario
  para historico documental.

## Permissoes

| Acao | Administrador da unidade | Chefe/Escrivao | Servidor operacional | Visualizador |
| --- | ---: | ---: | ---: | ---: |
| Criar e editar expedicoes | X | X | X |  |
| Cancelar expedicoes | X | X |  |  |
| Configurar tipos e instancias | X | X |  |  |
| Visualizar expedicoes permitidas | X | X | X | X |

Permissoes de perfil nao dispensam assinatura ativa, modulo contratado, vinculo
ativo na unidade nem regra de sigilo.

## Interfaces minimas

- lista unica de expedicoes;
- filtros por tipo, instancia, processo, status, responsavel e periodo;
- cadastro e edicao de expedicao;
- detalhe da expedicao com historico;
- configuracao de tipos e instancias;
- vinculacao de prazos e pendencias.
- vinculacao de contatos como destinatarios ou orgaos de destino.

## Criterios de aceite

- O usuario cria oficio pela tela de expedicoes e recebe numeracao interna.
- O usuario cria carta, edital, guia ou ato sem numero interno obrigatorio
  quando o tipo estiver configurado assim.
- O usuario registra numero externo posterior quando permitido pelo tipo.
- Tarefa pode gerar uma ou varias expedicoes vinculadas.
- Expedicao pode gerar tarefa de retorno, cumprimento ou conferencia.
- Expedicao processual sem processo vinculado e recusada.
- Processo sigiloso mascara ou bloqueia expedicoes vinculadas conforme acesso.
- Dashboard e fila tratam expedicoes em conjunto e filtram por tipo.
- Expedicoes podem reutilizar contatos como destinatarios ou orgaos de destino.
