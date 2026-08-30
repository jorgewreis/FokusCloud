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

- [Arquitetura da plataforma](arquitetura-da-plataforma.md): divisao entre Fokus Cloud, Fokus Law e Fokus Lead.
- [Administracao e acesso](administracao-e-acesso.md): empresas, usuarios, vinculos, admin e transferencia de administracao.
- [Cadastro e assinatura](cadastro-e-assinatura.md): criacao de empresa, usuario administrador, confirmacao de e-mail e escolha de assinatura.
- [Catalogo comercial](catalogo-comercial.md): sistemas, planos, funcionalidades, precos e estados.
- [Arquitetura de ofertas](arquitetura-de-ofertas.md): linhas comerciais, nomes oficiais, codigos e regras de segmentacao.
- [Matriz de planos](matriz-de-planos-e-funcionalidades.md): composicao dos planos por produto e linha comercial.
- [Catalogo de precos sugeridos](catalogo-de-precos-sugeridos.md): regra de arredondamento e valores mensais e anuais.
- [Modelo de dados](modelo-de-dados-catalogo.md): tabelas, relacionamentos e regras de integridade do catalogo.
- [API do catalogo](api-catalogo.md): contratos de leitura e integracao com backoffice e catalogo publico.
- [Vouchers](vouchers.md): cadastro, validade, descontos, limites, beneficios e resgates.
- [Governanca comercial](governanca-comercial.md): permissoes, auditoria, publicacao e versionamento.
- [Migracao do catalogo legado](migracao-catalogo-legado.md): substituicao de dados estaticos por dados reais do banco.
- [Plano de evolucao](plano-de-evolucao-catalogo.md): etapas para implementar administracao, publicacao e precificacao.

## Principios centrais

- A plataforma base nao deve receber regras especificas de dominio juridico ou imobiliario.
- O Fokus Law deve concentrar funcionalidades juridicas e cartorarias.
- O Fokus Lead deve concentrar funcionalidades imobiliarias e comerciais.
- O banco de dados e a fonte oficial dos produtos, planos, funcionalidades, precos e composicoes comerciais.
- O frontend nao deve manter uma segunda fonte manual para dados do catalogo.
- Cada novo modulo deve declarar seu produto, suas permissoes, seus impactos comerciais e seus criterios minimos de teste.

## Estado da documentacao

As regras de negocio aprovadas neste conjunto representam o modelo alvo. Cada documento pode identificar o que ja existe no codigo e o que ainda precisa ser implementado.
