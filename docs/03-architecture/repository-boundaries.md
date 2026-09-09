# Fronteiras dos repositorios Fokus

## Objetivo

Este documento define como os quatro repositorios do ecossistema Fokus se
relacionam. O Fokus Cloud e a plataforma-mae e a porta institucional de entrada;
os demais repositorios permanecem independentes, com ciclos de desenvolvimento,
release e deploy proprios.

## Repositorios oficiais

| Repositorio | Codigo | Tipo | Responsabilidade | Dominio canonico |
| --- | --- | --- | --- | --- |
| `fokus-cloud` | `cloud` | Plataforma e hub institucional | Marketing, portfolio, identidade, empresas, assinaturas, billing e backoffice | `https://fokuscloud.com.br` |
| `fokus-styles` | `styles` | Framework CSS open source | Tokens, temas, componentes, acessibilidade, documentacao e pacote npm | `https://styles.fokuscloud.com.br` |
| `fokus-law` | `law` | SaaS pago | Operacoes juridicas, cartorarias e modulos Law | `https://law.fokuscloud.com.br` |
| `fokus-lead` | `lead` | SaaS pago | CRM imobiliario, leads, imoveis e automacoes comerciais | `https://lead.fokuscloud.com.br` |

## Regra de pertencimento

- O Cloud contem somente recursos comuns, comerciais ou administrativos da
  plataforma.
- Styles contem o framework visual reutilizavel e nao regras de negocio dos
  produtos.
- Law contem regras, dados, permissoes e fluxos juridicos.
- Lead contem regras, dados, permissoes e fluxos imobiliarios.
- Uma funcionalidade comum deve ser contratada no Cloud antes de ser duplicada
  em Law ou Lead.

## Integracoes

O Cloud e a fonte operacional comum para identidade, empresa ativa, catalogo,
assinaturas, billing e autorizacao de acesso aos produtos pagos. Law e Lead
podem enviar metricas de uso por `product_code`, mas nao alteram regras
comerciais da plataforma.

O catalogo usa os codigos estaveis `law` e `lead`. O nome publico pode evoluir
sem alterar esses codigos nem o historico comercial.

## Portfolio publico

O Cloud apresenta os quatro projetos no portfolio institucional. A pagina do
Cloud pode resumir cada projeto, mas a documentacao e a aplicacao de cada
produto permanecem em seu repositorio e dominio canonico.

| Projeto | Chamada principal |
| --- | --- |
| Fokus Styles | Documentacao, NPM e GitHub |
| Fokus Law | Conhecer, assinar e acessar o sistema |
| Fokus Lead | Conhecer, assinar e acessar o sistema |

## Manifesto

Cada repositorio deve possuir `fokus-project.json` com identidade, tipo, versao,
status, repositorio e URLs. O arquivo e validado no CI e usado para documentacao;
nao e uma dependencia de runtime do Cloud.
