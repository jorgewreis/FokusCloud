# Documentacao do Fokus Cloud

Esta pasta concentra a documentacao funcional, comercial e tecnica do Fokus Cloud e de seus produtos derivados.

## Visao geral

O Fokus Cloud e a plataforma base. Ele concentra autenticacao, empresas, usuarios, permissoes, catalogo comercial, assinaturas, vouchers, auditoria, billing, backoffice e governanca.

Sobre essa base, produtos derivados podem evoluir por modulos independentes:

| Produto | Papel |
| --- | --- |
| Fokus Cloud | Plataforma comum e camada de administracao. |
| Fokus Law | Sistema derivado para rotinas juridicas, cartorarias e de advocacia. |
| Fokus Lead | Sistema derivado para rotinas imobiliarias, CRM, leads e automacoes comerciais. |

## Documentos principais

### Plataforma

- [Arquitetura da plataforma](platform/architecture.md): divisao entre Fokus Cloud, Fokus Law e Fokus Lead.
- [Convencao de nomes e pastas](platform/naming-conventions.md): padrao oficial de arquivos, pastas, rotas e modulos.
- [Diretrizes de documentacao e codificacao](platform/development-guidelines.md): regras para documentar, codificar, versionar tecnologias e validar alteracoes.
- [Administracao e acesso](platform/access-and-administration.md): empresas, usuarios, vinculos, admin e transferencia de administracao.
- [Cadastro e assinatura](platform/registration-and-subscription.md): criacao de empresa, usuario administrador, confirmacao de e-mail e escolha de assinatura.
- [Modelo relacional](platform/relational-model.md): entidades centrais, chaves e restricoes.
- [Isolamento e governanca de dados](platform/data-isolation-and-governance.md): separacao por empresa, vinculo e permissao.
- [Portais e governanca](platform/portals-and-governance.md): separacao entre portal do cliente e backoffice.
- [Seguranca e dados](platform/security-and-data.md): protecao de dados, acesso e responsabilidades.

### Comercial

- [Catalogo comercial](commercial/catalog.md): sistemas, planos, funcionalidades, precos e estados.
- [Arquitetura de ofertas](commercial/offer-architecture.md): linhas comerciais, nomes oficiais, codigos e regras de segmentacao.
- [Matriz de planos](commercial/plans-and-features-matrix.md): composicao dos planos por produto e linha comercial.
- [Catalogo de precos sugeridos](commercial/suggested-pricing.md): regra de arredondamento e valores mensais e anuais.
- [Modelo de dados do catalogo](commercial/catalog-data-model.md): tabelas, relacionamentos e regras de integridade.
- [API do catalogo](commercial/catalog-api.md): contratos de leitura e integracao com backoffice e catalogo publico.
- [Vouchers](commercial/vouchers.md): cadastro, validade, descontos, limites, beneficios e resgates.
- [Governanca comercial](commercial/commercial-governance.md): permissoes, auditoria, publicacao e versionamento.
- [Migracao do catalogo legado](commercial/legacy-catalog-migration.md): substituicao de dados estaticos por dados reais do banco.
- [Plano de evolucao do catalogo](commercial/catalog-evolution-plan.md): etapas para administracao, publicacao e precificacao.

## Principios centrais

- A plataforma base nao deve receber regras especificas de dominio juridico ou imobiliario.
- O Fokus Law deve concentrar funcionalidades juridicas e cartorarias.
- O Fokus Lead deve concentrar funcionalidades imobiliarias e comerciais.
- O banco de dados e a fonte oficial dos produtos, planos, funcionalidades, precos e composicoes comerciais.
- O frontend nao deve manter uma segunda fonte manual para dados do catalogo.
- Cada novo modulo deve declarar seu produto, suas permissoes, seus impactos comerciais e seus criterios minimos de teste.

## Estado da documentacao

As regras de negocio aprovadas neste conjunto representam o modelo alvo. Cada documento pode identificar o que ja existe no codigo e o que ainda precisa ser implementado.
