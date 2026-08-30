# Padrao de documentacao

## Objetivo

Este documento explica a organizacao da pasta `docs` e os criterios usados para definir os tipos de documentacao do Fokus Cloud.

## Referencias metodologicas

A estrutura foi adaptada a partir de referencias reconhecidas de documentacao de software:

| Referencia | Uso no projeto |
| --- | --- |
| Diataxis | Separar documentos de explicacao, referencia, guias e tutoriais. |
| arc42 | Cobrir contexto, requisitos, arquitetura, decisoes, qualidade e riscos. |
| C4 Model | Apoiar diagramas de contexto, containers, componentes e codigo quando necessario. |
| OWASP SAMM | Orientar seguranca durante requisitos, design, implementacao e operacao. |

## Taxonomia oficial

| Pasta | Razao |
| --- | --- |
| `01-overview` | Explica o produto, o contexto e o vocabulario. |
| `02-governance` | Define regras de decisao, nomenclatura, perguntas e padroes. |
| `03-architecture` | Registra desenho tecnico, fronteiras, portais e decisoes arquiteturais. |
| `04-products` | Separa a visao dos produtos derivados e seus modulos. |
| `05-requirements` | Guarda requisitos, regras de negocio e criterios de aceite. |
| `06-data` | Centraliza modelo relacional, dicionario de dados e governanca de dados. |
| `07-security` | Trata seguranca, privacidade, LGPD, ameacas e controles. |
| `08-commercial` | Organiza catalogo, planos, precos, vouchers, assinatura e billing. |
| `09-operations` | Cuida de ambientes, deploy, testes, releases, monitoramento e suporte. |

## Regra de criacao de documentos

Todo novo documento deve responder:

- qual problema resolve;
- qual pasta correta;
- qual produto ou modulo afeta;
- se e explicacao, referencia, guia ou registro de decisao;
- quais documentos devem ser atualizados junto com ele.

## Regra de evolucao

A documentacao deve evoluir junto com o codigo. Se uma implementacao alterar arquitetura, dados, seguranca, requisito, regra comercial ou operacao, o documento correspondente deve ser atualizado no mesmo ciclo de trabalho.
