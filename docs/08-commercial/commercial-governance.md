# Governanca comercial

## Permissoes

O catalogo comercial e administrado pelo Backoffice Fokus Cloud. A v1 usa os
perfis internos `superadministrador` e `administrador_comercial`, sempre com
escopo `platform` e sem aproveitar perfis de empresa.

O administrador comercial pode criar e editar dados comerciais. A publicacao,
pausa e arquivamento final de itens publicados ficam reservados ao
superadministrador. As permissoes devem ser separadas por capacidade:

- visualizar;
- criar;
- editar;
- revisar;
- publicar;
- pausar;
- arquivar;
- excluir quando permitido.

## Fluxo de publicacao

1. O administrador cria ou edita um item em rascunho.
2. O sistema valida dados, vinculos, precos, dependencias e conteudo.
3. O administrador revisa a previa.
4. O superadministrador confirma a publicacao imediata.
5. O catalogo publico passa a usar a nova versao publicada.
6. O cache e invalidado automaticamente.

A versao anterior permanece disponivel para consulta historica, sem restauracao automatica. Agendamento de publicacao pode ser criado em evolucao futura, mas nao e requisito da v1 do Backoffice.

## Auditoria

Devem gerar auditoria:

- criacao e edicao;
- ativacao, pausa e arquivamento;
- publicacao e agendamento;
- exclusao;
- alteracoes de preco;
- alteracoes de composicao;
- alteracoes de limites e dependencias;
- alteracoes em vouchers e resgates.

Cada evento deve registrar administrador, data, entidade, identificador, motivo quando aplicavel e metadados relevantes.

## Exclusao

A exclusao fisica somente e permitida quando nao houver vinculos. Sistemas, planos ou funcionalidades com historico, assinaturas, vouchers ou resgates devem ser mantidos e retirados por arquivamento.

## Regras de seguranca comercial

- nao confiar em precos enviados pelo frontend;
- recalcular descontos no backend;
- impedir mistura de sistemas em planos;
- impedir comercializacao de itens nao publicados;
- preservar snapshots de contratacoes e resgates;
- registrar todas as alteracoes sensiveis.
## Governança de vouchers 0.0.4

Administrador comercial cria e edita vouchers não resgatados. Superadministrador executa arquivamento, exclusão e ações finais. Toda operação destrutiva exige motivo, confirmação explícita e auditoria; o frontend não substitui as permissões do backend.
