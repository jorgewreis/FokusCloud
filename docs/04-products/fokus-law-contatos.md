# Gestao de contatos do Fokus Law

## Objetivo

Gestao de Contatos e o nome comercial do modulo do Fokus Law para cadastrar,
organizar e reutilizar pessoas, advogados, instituicoes, orgaos, unidades,
enderecos e meios de contato relevantes para a operacao juridica.

No menu interno do sistema, o modulo deve aparecer como `Contatos`.

O objetivo e evitar duplicidade de cadastros e permitir que o mesmo contato seja
vinculado a processos, expedicoes, tarefas e outros fluxos com papeis
diferentes.

## Escopo funcional

O modulo deve permitir registrar:

- pessoas fisicas;
- pessoas juridicas;
- advogados;
- escritorios de advocacia;
- orgaos publicos;
- instituicoes privadas;
- unidades judiciais;
- delegacias;
- Ministerio Publico;
- Defensoria Publica;
- peritos;
- testemunhas;
- representantes;
- enderecos;
- telefones;
- e-mails;
- observacoes operacionais;
- tags informativas.

## Diferenca para agenda

O modulo nao deve ser chamado de Agenda para evitar confusao com compromissos,
audiencias, prazos e pendencias.

Agenda e Compromissos deve continuar representando datas, eventos e
compromissos. Gestao de Contatos representa cadastro reutilizavel de pessoas,
instituicoes e canais de comunicacao.

## Relacao com processos

Em Gestao de Processos, um contato pode assumir papel processual, como:

- autor;
- reu;
- vitima;
- testemunha;
- advogado;
- defensor;
- promotor;
- representante legal;
- interessado;
- orgao de origem;
- outro papel definido pela unidade.

O papel nao pertence ao contato isoladamente. Ele pertence ao vinculo entre o
contato e o processo, permitindo que o mesmo contato tenha papeis diferentes em
processos distintos.

## Relacao com expedicoes

Em Gestao de Expedicoes, um contato pode ser usado como:

- destinatario;
- orgao de destino;
- comarca de destino;
- unidade expedidora ou recebedora;
- representante externo;
- responsavel por recebimento;
- canal preferencial de envio.

A expedicao deve guardar snapshot minimo do destinatario usado no momento da
emissao quando isso for necessario para preservar historico.

## Relacao com tarefas

Em Gestao de Tarefas, um contato pode ser vinculado como envolvido externo,
responsavel externo, interessado ou referencia operacional. O contato nao
substitui o responsavel interno da tarefa, que continua sendo usuario ou
vinculo da unidade.

## Regras de negocio

- Gestao de Contatos e modulo independente.
- O menu interno deve usar apenas o rotulo `Contatos`.
- O cadastro deve ser reutilizavel dentro da empresa/unidade autorizada.
- Um contato pode ser pessoa, instituicao, orgao publico ou unidade externa.
- Um contato pode ter multiplos enderecos e meios de contato.
- Papeis processuais e papeis em expedicoes ficam nos vinculos, nao no cadastro
  principal.
- Tags de contato sao informativas e nao substituem tipo, papel ou permissao.
- Dados pessoais devem ser minimizados, protegidos e mascarados quando o
  contexto exigir.
- Contatos vinculados a processo sigiloso devem respeitar o nivel de sigilo do
  processo.
- A inativacao ou mesclagem de contato deve preservar historico dos vinculos.

## Limites da v1

Na v1, o modulo deve priorizar dados necessarios para uso processual e
operacional. Nao faz parte do escopo inicial transformar contatos em CRM amplo,
agenda de compromissos, disparador de comunicacao em massa ou base publica de
terceiros.

## Criterios de aceite

- O nome comercial e Gestao de Contatos.
- O menu interno usa o rotulo Contatos.
- O modulo nao e confundido com Agenda e Compromissos.
- Pessoas, advogados, instituicoes, orgaos e unidades externas podem ser
  cadastrados em uma base reutilizavel.
- O mesmo contato pode ser vinculado a mais de um processo com papeis
  diferentes.
- O mesmo contato pode ser usado como destinatario ou orgao de destino em
  expedicoes.
- Contatos vinculados a processos sigilosos respeitam as regras de sigilo.
