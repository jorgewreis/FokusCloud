# Convencao de nomes e pastas

## Objetivo

Este documento define o padrao oficial de nomenclatura do Fokus Cloud. A regra vale para novos modulos, documentos, paginas, assets, rotas e componentes internos do projeto.

O objetivo e manter o crescimento organizado desde o inicio, preservando a separacao entre plataforma base, produtos derivados e mockups.

## Principio de decisao

Ao escolher entre alternativas tecnicas, o projeto deve priorizar o que for melhor, mais correto e mais sustentavel para a arquitetura do Fokus Cloud, mesmo quando a opcao exigir mais trabalho no curto prazo.

Facilidade de implementacao pode ser considerada, mas nao deve prevalecer sobre manutencao, clareza, seguranca, escalabilidade, isolamento de dados e consistencia entre a plataforma base e os produtos derivados.

## Regra principal

| Contexto | Padrao |
| --- | --- |
| Pastas do projeto | `kebab-case` quando forem criadas pelo projeto. |
| Arquivos HTML, CSS, JS e Markdown | `kebab-case`. |
| Classes PHP | `PascalCase`, seguindo o padrao Laravel. |
| Metodos PHP | `camelCase`, seguindo o padrao Laravel. |
| Tabelas do banco | `snake_case`, no plural. |
| Colunas do banco | `snake_case`. |
| Codigos internos de produto, plano e modulo | `kebab-case` ou slug estavel. |
| Rotas publicas | portugues claro e estavel, quando forem voltadas ao usuario final. |
| APIs | ingles ou dominio tecnico curto, mantendo consistencia por grupo. |

## Estrutura de alto nivel

```text
app/                  Codigo Laravel da aplicacao
bootstrap/            Inicializacao Laravel
config/               Configuracoes Laravel
database/             Migrations, factories e seeders
deploy/               Arquivos auxiliares de deploy
docs/                 Documentacao do projeto
mockups/              Prototipos e estudos visuais preservados
public/               Entrada publica da aplicacao
resources/            Entradas Vite e recursos Laravel
routes/               Rotas web, API e console
tests/                Testes automatizados
```

## Documentacao

A pasta `docs` deve ser organizada por dominio:

```text
docs/
  README.md
  01-overview/
  02-governance/
  03-architecture/
  04-products/
  05-requirements/
  06-data/
  07-security/
  08-commercial/
  09-operations/
```

Use as pastas numeradas para manter ordem visual estavel. O numero define a ordem de leitura; o nome define o dominio documental.

Novos produtos derivados poderao receber suas proprias pastas quando houver documentacao suficiente:

```text
docs/products/fokus-law/
docs/products/fokus-lead/
```

## Public

A pasta `public` deve separar arquivos por area de uso:

```text
public/
  index.html
  index.php
  assets/
    css/
    js/
    images/
  auth/
  portal/
  backoffice/
  products/
```

Responsabilidades:

| Pasta | Uso |
| --- | --- |
| `public/assets` | Assets compartilhados por telas publicas, portal e produtos. |
| `public/auth` | Telas de cadastro, verificacao, senha e convites. |
| `public/portal` | Telas do cliente e administracao da empresa. |
| `public/backoffice` | Backoffice interno da plataforma. |
| `public/products` | Paginas comerciais e fluxos de assinatura por produto. |

## Produtos

Produtos derivados devem usar codigos tecnicos estaveis:

| Produto | Codigo |
| --- | --- |
| Fokus Law | `fokus-law` |
| Fokus Lead | `fokus-lead` |

Arquivos de produto devem usar o prefixo do produto:

```text
fokus-law.html
fokus-law-subscription.html
fokus-lead.html
fokus-lead-subscription.html
```

## Mockups

Mockups devem ficar fora de `public`, salvo quando uma tela for promovida para uso real.

```text
mockups/
  assets/
  pages/
```

Ao promover um mockup para a aplicacao, copie ou mova apenas o que sera usado em producao para a area correta de `public`.

## Criterios para novos modulos

Todo novo modulo deve declarar:

- produto ao qual pertence;
- codigo tecnico estavel;
- rotas publicas e APIs;
- pasta de documentacao;
- arquivos de interface;
- tabelas e migrations;
- permissoes;
- relacao com assinatura ou catalogo, quando houver;
- testes minimos de acesso e isolamento.

## Regra de descarte

Arquivos temporarios, copias antigas e experimentos que nao forem mockups devem ser removidos. Mockups relevantes devem ser preservados em `mockups/`.
