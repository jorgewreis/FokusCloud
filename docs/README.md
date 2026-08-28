# Documentacao do catalogo comercial

Esta pasta concentra a documentacao do catalogo comercial do FokusCloud e suas regras de governanca.

## Documentos

- [Catalogo comercial](catalogo-comercial.md): sistemas, planos, funcionalidades, precos e estados.
- [Arquitetura de ofertas](arquitetura-de-ofertas.md): linhas comerciais, nomes oficiais, codigos e regras de segmentacao.
- [Matriz de planos](matriz-de-planos-e-funcionalidades.md): proposta de composicao dos 11 planos.
- [Catalogo de precos sugeridos](catalogo-de-precos-sugeridos.md): regra de arredondamento e valores mensais e anuais.
- [Modelo de dados](modelo-de-dados-catalogo.md): tabelas, relacionamentos e regras de integridade.
- [API do catalogo](api-catalogo.md): contratos de leitura e integracao com backoffice e catalogo publico.
- [Vouchers](vouchers.md): cadastro, validade, descontos, limites e resgates.
- [Governanca comercial](governanca-comercial.md): permissoes, auditoria, publicacao e versionamento.
- [Migracao do catalogo legado](migracao-catalogo-legado.md): substituicao de dados estaticos por dados reais do banco.
- [Plano de evolucao](plano-de-evolucao-catalogo.md): etapas para implementar administracao, publicacao e precificacao.

## Principio central

O banco de dados e a fonte oficial dos sistemas, planos, funcionalidades, precos e composicoes comerciais. O frontend nao deve manter uma segunda fonte manual desses dados.

## Estado da documentacao

As regras de negocio aprovadas neste conjunto representam o modelo alvo. Cada documento identifica o que ja existe no codigo e o que ainda precisa ser implementado.
