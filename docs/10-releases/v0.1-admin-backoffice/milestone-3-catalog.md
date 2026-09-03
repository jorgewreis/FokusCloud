# Marco 3 - catalogo administrativo (`0.0.3`)

## Entrega

A `0.0.3` implementa a gestao administrativa do catalogo comercial pelo
Backoffice. Produtos, funcionalidades, planos, precos, composicoes e
publicacao passam a ter fonte unica no backend, com snapshot versionado para o
catalogo publico e checkout.

## Escopo funcional

- Administrador comercial cria e edita produtos, funcionalidades e planos em
  estado nao publicado.
- Superadministrador publica, pausa e arquiva itens que afetam disponibilidade
  publica.
- Planos possuem composicao explicita por `plan_modules`.
- O catalogo publico consome `GET /api/catalog/{product}` com contrato
  versionado `0.0.3`.
- O checkout valida planos e funcionalidades contra a ultima versao publicada,
  sem aceitar preco informado pelo navegador.

## Endpoints administrativos

| Endpoint | Permissao |
| --- | --- |
| `GET /api/backoffice/catalog` | `platform.catalog.manage` |
| `POST/PATCH /api/backoffice/catalog/products` | `platform.catalog.manage` |
| `POST/PATCH /api/backoffice/catalog/modules` | `platform.catalog.manage` |
| `POST/PATCH /api/backoffice/catalog/plans` | `platform.catalog.manage` |
| `PUT /api/backoffice/catalog/plans/{plan}/modules` | `platform.catalog.manage` |
| `POST /api/backoffice/catalog/{product}/publish` | `platform.catalog.publish` |
| `POST /api/backoffice/catalog/{type}/{id}/pause` | `platform.catalog.publish` |
| `POST /api/backoffice/catalog/{type}/{id}/archive` | `platform.catalog.publish` |

As rotas antigas de planos continuam disponiveis durante a transicao, mas a
fonte normativa do Marco 3 e o grupo `/api/backoffice/catalog`.

## Validacao de publicacao

A publicacao imediata exige produto ativo, ordem unica entre produtos, ao menos
uma funcionalidade ativa, ao menos um plano ativo, plano com composicao,
funcionalidades do mesmo produto, precos validos, dependencias atendidas,
incompatibilidades ausentes e nenhum item arquivado. Quando uma ordem de
produto ja existente e informada, o produto editado ou criado assume essa
posicao e os demais itens subsequentes sao reordenados automaticamente. Cada
publicacao grava uma linha em `catalog_publications` com snapshot completo,
versao, motivo, autor quando houver e data.

## Evidencias de aceite

- `administrador_comercial` cria e edita rascunhos, mas recebe `403` em
  publicacao, pausa e arquivamento.
- `superadministrador` publica com motivo e gera auditoria.
- `GET /api/catalog/{product}` retorna `contract_version: 0.0.3`,
  `published_version`, `published_at`, produto, funcionalidades e planos.
- Edicoes posteriores ao snapshot nao alteram o catalogo publico ate nova
  publicacao.
- O fluxo publico normaliza o contrato versionado sem catalogo estatico ou
  fallback de preco no frontend.

## Evidencias de fechamento

| Evidencia | Resultado |
| --- | --- |
| `php artisan migrate:fresh --seed --force` com SQLite em memoria | Concluido |
| `php artisan test` | Concluido, 41 testes |
| `npm run build` | Concluido |
| `git diff --check` | Concluido |
| Homologacao guiada do fluxo 3 | Validada pelo usuario |
| GitHub Actions da tag `v0.0.3` | Aprovado: `https://github.com/jorgewreis/FokusCloud/actions/runs/33624520690` |
| Falhas bloqueantes conhecidas | Nenhuma |

O documento deve ser atualizado quando houver nova evolucao funcional do
catalogo, incluindo commit, ambiente homologado, data de fechamento e
restricoes conhecidas.
