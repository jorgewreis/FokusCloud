# Diretrizes de documentacao e codificacao

## Objetivo

Este documento define as diretrizes oficiais para documentar, codificar e evoluir o Fokus Cloud, incluindo seus produtos derivados Fokus Law e Fokus Lead.

A regra central e construir o projeto com base tecnica solida, usando documentacao oficial, versoes estaveis e decisoes compativeis com crescimento de longo prazo.

## Principios obrigatorios

1. Toda decisao tecnica deve favorecer manutencao, clareza, seguranca, escalabilidade e isolamento de dados.
2. A documentacao deve orientar o codigo, e o codigo deve confirmar a documentacao.
3. Nenhum modulo novo deve ser criado sem antes declarar seu dominio, produto, permissoes, dados, rotas, telas e impactos comerciais.
4. Toda linguagem, framework ou biblioteca deve ser usada conforme sua documentacao oficial.
5. Versoes devem ser atuais, estaveis e compativeis entre si.
6. Dependencias experimentais, abandonadas ou sem manutencao clara devem ser evitadas.
7. O projeto deve preservar a separacao entre plataforma base, produtos derivados, mockups e codigo de producao.

## Fontes oficiais

As fontes abaixo devem ser usadas como referencia primaria antes de criar padroes, atualizar versoes ou resolver duvidas tecnicas.

| Area | Fonte oficial |
| --- | --- |
| HTML | `https://developer.mozilla.org/en-US/docs/Web/HTML` e `https://html.spec.whatwg.org/` |
| CSS | `https://developer.mozilla.org/en-US/docs/Web/CSS` |
| JavaScript | `https://developer.mozilla.org/en-US/docs/Web/JavaScript` |
| PHP | `https://www.php.net/manual/` e `https://www.php.net/supported-versions.php` |
| Laravel | `https://laravel.com/docs` |
| Composer | `https://getcomposer.org/doc/` |
| Node.js | `https://nodejs.org/docs/latest/api/` |
| Vite | `https://vite.dev/guide/` |
| Tailwind CSS | `https://tailwindcss.com/docs` |
| PHPUnit | `https://docs.phpunit.de/` |

Tutoriais, artigos, respostas de forum e exemplos de terceiros podem ajudar, mas nao devem substituir a documentacao oficial.

## Politica de versoes

O projeto deve usar apenas versoes estaveis e suportadas. Antes de atualizar PHP, Laravel, Vite, Tailwind, Node ou qualquer dependencia central, deve ser conferido:

- se a versao e estavel;
- se ainda recebe suporte de seguranca;
- se e compativel com as demais dependencias do projeto;
- se ha mudancas de ruptura documentadas;
- se os ambientes de desenvolvimento, teste e producao suportam a nova versao;
- se o deploy continua viavel no servidor alvo.

## Base tecnica atual

A base tecnica declarada no repositorio e:

| Area | Padrao atual |
| --- | --- |
| Backend | PHP com Laravel |
| Frontend | HTML, CSS e JavaScript, com Vite para build |
| Estilo | CSS modular e Tailwind quando integrado ao fluxo Vite |
| Dependencias PHP | Composer |
| Dependencias JS | npm |
| Testes PHP | PHPUnit via Laravel test runner |
| Padrao de codigo PHP | Laravel Pint |

O `composer.json` e o `package.json` sao a fonte tecnica imediata para verificar as versoes atualmente aceitas pelo projeto.

## Ordem correta de trabalho

Para cada novo modulo ou alteracao relevante, a ordem recomendada e:

1. Documentar o objetivo e o dominio do modulo.
2. Definir dados, permissoes, rotas, telas e regras de negocio.
3. Confirmar impacto comercial, quando envolver planos, assinaturas, vouchers ou billing.
4. Validar a decisao contra a documentacao oficial das tecnologias usadas.
5. Implementar o codigo em arquivos e pastas alinhados a convencao do projeto.
6. Criar ou atualizar testes proporcionais ao risco da alteracao.
7. Revisar links, rotas, assets, migracoes e compatibilidade antes do commit.

## Diretrizes para HTML

- Usar HTML semantico antes de adicionar estruturas puramente visuais.
- Priorizar acessibilidade basica: `label`, `aria-*` quando necessario, hierarquia correta de titulos e textos alternativos em imagens relevantes.
- Evitar duplicacao de paginas quando uma rota, componente ou template puder representar o mesmo fluxo com clareza.
- Nomes de arquivos HTML devem seguir `kebab-case`.
- Paginas de produto devem usar o prefixo oficial do produto, como `fokus-law` e `fokus-lead`.

## Diretrizes para CSS

- CSS deve ser organizado por responsabilidade, evitando arquivos gigantes sem dominio claro.
- Classes devem expressar funcao visual ou componente, nao valores soltos.
- Tokens, cores, espacamentos e tipografia devem ser centralizados quando se tornarem recorrentes.
- Estilos de mockup nao devem vazar para producao sem revisao.
- Evitar sobrescritas excessivas e seletores muito especificos sem necessidade real.

## Diretrizes para JavaScript

- JavaScript deve ser modular quando o comportamento passar de interacao simples para regra reutilizavel.
- Dados de catalogo, planos, precos e permissoes nao devem ficar duplicados manualmente no frontend quando ja houver fonte no banco ou API.
- Usar `const` por padrao e `let` apenas quando houver reatribuicao.
- Evitar variaveis globais fora de objetos ou namespaces explicitamente aceitos pelo projeto.
- Tratar estados de erro, carregamento e sessao expirada em toda tela que consome API.

## Diretrizes para PHP e Laravel

- Seguir as convencoes nativas do Laravel antes de criar estruturas proprias.
- Controllers devem orquestrar fluxo; regras de negocio relevantes devem migrar para services, actions ou policies quando crescerem.
- Models devem representar entidades do dominio com relacionamentos claros.
- Migrations devem ser pequenas, reversiveis quando possivel e coerentes com o modelo relacional documentado.
- Policies, middlewares e validações devem proteger fronteiras de acesso e isolamento multiempresa.
- Requests, DTOs ou validadores dedicados devem ser usados quando a validacao deixar de ser trivial.
- Codigo PHP deve passar pelo Laravel Pint antes de ser considerado pronto.

## Diretrizes para banco de dados

- Tabelas e colunas devem seguir `snake_case`.
- Relacionamentos devem ser explicitos por chaves estrangeiras sempre que possivel.
- Toda tabela ligada a empresas, usuarios, assinaturas ou produtos deve considerar isolamento multiempresa.
- Dados comerciais versionaveis devem preservar historico quando afetarem cobranca, contrato ou auditoria.
- Seeds podem criar dados iniciais, mas nao devem virar substituto permanente de backoffice quando o dado for administravel.

## Diretrizes para documentacao

- Documentos devem ser escritos antes ou junto da implementacao relevante.
- Cada documento deve indicar o modelo alvo, mesmo que parte dele ainda nao exista no codigo.
- Quando houver diferenca entre estado atual e modelo alvo, isso deve ficar claro.
- Links internos devem ser mantidos atualizados apos renomear arquivos ou pastas.
- Documentacao de arquitetura deve evitar detalhes descartaveis de tela, salvo quando a tela define uma regra de produto.

## Diretrizes para commits

Cada commit deve representar uma unidade coerente de trabalho. Evitar misturar no mesmo commit:

- reorganizacao de pastas;
- mudanca de regra de negocio;
- ajuste visual amplo;
- migracao de banco;
- atualizacao de dependencia;
- correcao emergencial.

Quando a alteracao for grande, preferir commits menores e rastreaveis.

## Criterio de pronto

Uma alteracao so deve ser considerada pronta quando:

- segue a convencao de nomes e pastas;
- foi baseada em documentacao oficial quando envolveu decisao tecnica;
- nao introduz duplicacao desnecessaria de fonte de dados;
- preserva separacao entre Fokus Cloud, Fokus Law e Fokus Lead;
- trata erros e estados relevantes para o usuario;
- possui teste ou justificativa clara quando teste nao for viavel no momento;
- foi validada localmente dentro das ferramentas disponiveis.
