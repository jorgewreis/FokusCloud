# Requisitos da gestao de contatos

## Objetivo

Definir requisitos do modulo comercial Gestao de Contatos do Fokus Law, cujo
rotulo interno no menu deve ser Contatos.

## Requisitos funcionais

| Codigo | Requisito | Criterio de aceite |
| --- | --- | --- |
| RF-CTT-001 | O sistema deve permitir cadastrar contatos reutilizaveis por empresa e unidade autorizada. | Um contato pode ser localizado e reutilizado em processos, expedicoes e tarefas sem novo cadastro obrigatorio. |
| RF-CTT-002 | O sistema deve permitir classificar contatos por natureza. | O contato pode ser pessoa fisica, pessoa juridica, advogado, instituicao, orgao publico, unidade judicial ou outro tipo permitido. |
| RF-CTT-003 | O sistema deve permitir registrar documentos de identificacao quando necessarios. | CPF, CNPJ, OAB ou outro identificador podem ser informados, normalizados e protegidos conforme regra de privacidade. |
| RF-CTT-004 | O sistema deve permitir multiplos enderecos e meios de contato. | Um contato pode possuir telefones, e-mails, enderecos e canais preferenciais. |
| RF-CTT-005 | O sistema deve permitir vincular contato a processo com papel contextual. | O mesmo contato pode ser parte, advogado, testemunha, orgao de origem ou outro papel em processos diferentes. |
| RF-CTT-006 | O sistema deve permitir vincular contato a expedicao. | Um contato pode ser destinatario, orgao de destino, comarca, unidade externa ou responsavel por recebimento. |
| RF-CTT-007 | O sistema deve permitir vincular contato a tarefa. | Um contato pode ser envolvido externo ou referencia operacional sem substituir o responsavel interno. |
| RF-CTT-008 | O sistema deve permitir pesquisar e filtrar contatos. | Busca considera nome, documento, tipo, papel vinculado, tags e meios de contato quando autorizados. |
| RF-CTT-009 | O sistema deve permitir inativar ou mesclar contatos duplicados. | Vinculos historicos sao preservados e a operacao e auditada. |
| RF-CTT-010 | O sistema deve registrar auditoria de alteracoes sensiveis. | Criacao, edicao, inativacao, mesclagem e alteracao de documentos ou enderecos geram auditoria. |

## Regras de negocio

- Gestao de Contatos e o nome comercial.
- Contatos e o rotulo do menu interno.
- O modulo nao deve ser chamado de Agenda.
- Agenda e Compromissos controla eventos e datas; Gestao de Contatos controla
  pessoas, instituicoes, enderecos e canais.
- O contato principal nao define papel processual fixo.
- Papel processual fica no vinculo entre contato e processo.
- Papel em expedicao fica no vinculo entre contato e expedicao ou no snapshot
  historico da expedicao.
- Contatos vinculados a processos sigilosos devem herdar restricoes de exibicao
  do processo no contexto de consulta.
- Tags de contato sao informativas e nao substituem tipo, papel ou permissao.
- Dados pessoais devem ser coletados por necessidade operacional e exibidos com
  minimizacao.

## Permissoes

| Acao | Administrador da unidade | Chefe/Escrivao | Servidor operacional | Visualizador |
| --- | ---: | ---: | ---: | ---: |
| Criar e editar contatos | X | X | X |  |
| Inativar contatos | X | X |  |  |
| Mesclar contatos duplicados | X | X |  |  |
| Vincular contatos a processos | X | X | X |  |
| Vincular contatos a expedicoes | X | X | X |  |
| Consultar contatos nao sigilosos | X | X | X | X |
| Consultar dados protegidos | X | X | Conforme autorizacao |  |

## Criterios de aceite

- Gestao de Contatos aparece como nome comercial e Contatos como rotulo interno.
- O modulo e documentado como independente de Agenda e Compromissos.
- Contatos podem representar advogados, instituicoes, orgaos, partes e
  destinatarios.
- O mesmo contato pode assumir papeis diferentes em processos distintos.
- Expedicoes podem usar contatos como destinatarios ou orgaos de destino.
- Tarefas podem referenciar contatos externos sem alterar o responsavel interno.
- Sigilo processual restringe a exibicao de contatos no contexto do processo.
