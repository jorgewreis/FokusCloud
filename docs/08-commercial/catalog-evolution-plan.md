# Plano de evolucao do catalogo

## Fase 1: aprovacao comercial

1. Revisar a matriz em [Matriz de planos](plans-and-features-matrix.md).
2. Confirmar limites e opcoes de capacidade.
3. Definir dependencias e incompatibilidades.
4. Consolidar modulos equivalentes, evitando duplicar `Gestao de Prazos e Intimacoes` e `Controle de Prazos` sem necessidade.
5. Definir variantes de `Audiencias`, `Contatos` e `Processos`.
6. Definir tipos e instancias de expedicao por setor, incluindo numeracao e permissoes.
7. Definir receitas operacionais que conectam tarefas, expedicoes, prazos e alertas.
8. Definir descontos mensal e anual.
9. Confirmar as funcionalidades Team que ainda nao existem.

## Fase 2: modelo de dados

1. Adicionar linha comercial aos planos do Lead.
2. Adicionar descricoes tecnicas e comerciais.
3. Adicionar status, publicacao, ordenacao e destaque.
4. Adicionar capacidades, limites, dependencias e precificacao por ciclo.
5. Representar disponibilidade em planos sugeridos e contratacao avulsa.
6. Criar tabelas de versao e agendamento.
7. Garantir snapshots em assinaturas e resgates.

## Fase 3: recriacao do catalogo inicial

Concluida no Marco 3 (`0.0.3`) para a base administrativa do catalogo.

Como o projeto ainda nao possui vinculacoes comerciais publicas, os planos Law
atuais podem ser removidos e recriados com os cinco modulos comerciais. A
operacao deve ocorrer em migration controlada e ser acompanhada de seeders
idempotentes. Os planos Lead permanecem inalterados.

Os codigos dos novos planos devem ser:

- `law-advocacia`;
- `law-cartorio-criminal`;
- `law-cartorio-civel`;
- `law-gestao-audiencias`;
- `law-gestao-expedientes`;
- `lead-one-essencial`;
- `lead-one-profissional`;
- `lead-one-avancado`;
- `lead-one-premium`;
- `lead-team-essencial`;
- `lead-team-premium`.

## Fase 4: administracao e publicacao

1. Criar telas de sistemas, planos e funcionalidades.
2. Implementar rascunho, previa, revisao e publicacao.
3. Implementar publicacao imediata com confirmacao explicita e invalidacao de cache.
4. Permitir criacao e edicao por administradores comerciais, reservando publicacao, pausa e arquivamento final a superadministradores.
5. Registrar auditoria completa.

Agendamento de publicacao pode ser criado em evolucao futura, mas nao e requisito da v1 do Backoffice.

## Fase 5: integracao dos consumidores

1. Fazer o catalogo publico consumir a API. Concluido com contrato `0.0.3`.
2. Fazer o checkout consumir a versao publicada. Concluido com snapshot em
   `catalog_publications`.
3. Recalcular precos no backend.
4. Remover o catalogo estatico legado. Concluido: o arquivo publico restante e
   apenas adaptador da API.
5. Validar catalogo vazio, itens inativos, falha da API e composicoes personalizadas.

## Criterio de conclusao

A evolucao da base administrativa foi concluida quando os planos estiveram
cadastrados com composicoes, o catalogo publico passou a exibir apenas a versao
publicada, os limites e precos passaram a ser calculados a partir do backend e
nenhum dado comercial duplicado permaneceu no frontend.
