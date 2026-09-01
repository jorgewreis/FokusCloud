# Processo de release

## Objetivo

Definir como mudancas deixam o desenvolvimento e chegam a producao.

## Fluxo inicial

1. Documentar decisao ou requisito.
2. Implementar alteracao.
3. Rodar validacoes disponiveis.
4. Revisar impacto em dados, seguranca e comercial.
5. Criar commit coerente.
6. Publicar e acompanhar resultado.

## Release 0.1 - Backoffice Admin

A release `0.1` usa o pacote documental
[`docs/10-releases/v0.1-admin-backoffice`](../10-releases/v0.1-admin-backoffice/README.md)
como fonte de acompanhamento.

A release documental `0.0.1` corresponde ao Marco 1 da `0.1` e deve ficar
versionada com commit proprio e tag anotada `v0.0.1`.

O fechamento da `0.1` exige:

1. concluir o checklist por modulo;
2. executar o plano de testes automatizados e manuais;
3. validar o roteiro de homologacao em ambiente de homologacao;
4. classificar falhas restantes como bloqueantes ou nao bloqueantes;
5. corrigir todas as falhas bloqueantes;
6. atualizar as notas da release com evidencias de fechamento.

Falhas nao bloqueantes podem seguir para versao posterior somente quando
registradas no documento de decisoes abertas da release.

## A complementar

- Estrategia de branches.
- Versionamento.
- Checklist de release.
- Plano de rollback.
