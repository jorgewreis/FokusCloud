# Arquitetura da plataforma

## Decisao central

O Fokus Cloud e a plataforma base. Ele nao deve ser tratado como apenas mais um modulo de negocio. Sua funcao e fornecer a infraestrutura comum para produtos digitais derivados, mantendo uma unica base de identidade, empresas, permissoes, assinaturas, catalogo, vouchers, auditoria, cobranca e administracao.

Os produtos derivados sao sistemas verticais que utilizam essa base comum e adicionam funcionalidades especificas de dominio.

## Hierarquia

| Nivel | Nome | Responsabilidade |
| --- | --- | --- |
| Plataforma | Fokus Cloud | Sustenta identidade, empresas, usuarios, assinaturas, catalogo, vouchers, auditoria, billing, backoffice e governanca. |
| Produto derivado | Fokus Law | Atende rotinas juridicas e administrativas ligadas a advocacia, cartorios, audiencias, expedientes, prazos, contatos, partes e documentos. |
| Produto derivado | Fokus Lead | Atende rotinas imobiliarias ligadas a CRM, leads, imoveis, corretores, funil comercial, RT, distribuicao e automacoes. |

## Regra de pertencimento

Uma funcionalidade deve ser classificada pela seguinte regra:

| Pergunta | Destino |
| --- | --- |
| Serve para qualquer produto atual ou futuro? | Fokus Cloud |
| Depende de regra, linguagem ou fluxo juridico? | Fokus Law |
| Depende de regra, linguagem ou fluxo imobiliario? | Fokus Lead |
| Mistura base e dominio? | Separar em servico comum no Fokus Cloud e modulo especifico no produto derivado. |

## Camadas funcionais

```text
Fokus Cloud
  Core
    Identidade e autenticacao
    Empresas e vinculos
    Perfis e permissoes
    Catalogo comercial
    Planos e funcionalidades
    Assinaturas e billing
    Vouchers
    Auditoria
    Backoffice

  Product Shell
    Produto ativo
    Assinatura ativa por produto
    Menu por produto
    Permissoes por produto
    Limites contratados

  Product Modules
    Fokus Law
      Modulos juridicos

    Fokus Lead
      Modulos imobiliarios
```

## Core da plataforma

O core concentra recursos que devem funcionar do mesmo modo para qualquer produto.

Exemplos:

- cadastro e autenticacao de usuarios;
- cadastro de empresas por CPF ou CNPJ;
- vinculos entre usuario e empresa;
- selecao de empresa ativa;
- perfis administrativos;
- catalogo de produtos, planos e funcionalidades;
- assinaturas e snapshots comerciais;
- vouchers, descontos e beneficios;
- auditoria de acoes sensiveis;
- backoffice da plataforma;
- integracoes comuns de pagamento, notificacao e monitoramento.

## Fokus Law

O Fokus Law deve conter apenas funcionalidades cujo valor depende do dominio juridico.

Exemplos de modulos possiveis:

- gestao de processos;
- gestao de contatos;
- partes como papel processual de contatos;
- audiencias;
- expedientes;
- expedicoes;
- prazos e intimacoes;
- tarefas e fluxos juridicos;
- relatorios juridicos;
- rotinas de advocacia;
- rotinas de cartorio criminal;
- rotinas de cartorio civel.

## Fokus Lead

O Fokus Lead deve conter apenas funcionalidades cujo valor depende do dominio imobiliario.

Exemplos de modulos possiveis:

- leads;
- imoveis;
- clientes;
- corretores;
- imobiliarias;
- funil comercial;
- distribuicao de leads;
- captacao;
- propostas;
- relatorio de transacao imobiliaria;
- automacoes comerciais;
- integracoes de origem de lead.

## Produtos e assinaturas

Uma empresa pode contratar um ou mais produtos dentro da mesma plataforma. A assinatura deve ficar vinculada a empresa e ao produto contratado, preservando snapshot de plano, modulos, limites, valores e condicoes comerciais.

O mesmo usuario pode acessar mais de uma empresa e mais de um produto, desde que possua vinculo ativo, permissao adequada e assinatura valida para o produto em uso.

## Catalogo comercial

O catalogo deve ser a fonte oficial de produtos, planos, funcionalidades, precos, status e publicacao. O frontend nao deve manter uma segunda fonte manual desses dados.

Cada produto deve possuir codigo tecnico estavel. Exemplos:

| Produto | Codigo sugerido |
| --- | --- |
| Fokus Cloud Law | `law` |
| Fokus Cloud Lead | `lead` |

Linhas comerciais internas podem ser usadas quando um produto possuir ofertas para publicos diferentes, como `lead-one` e `lead-team`.

## Evolucao por modulo

O desenvolvimento deve priorizar um modulo por vez. Cada novo modulo deve nascer com:

- escopo funcional claro;
- pertencimento definido: plataforma, Law ou Lead;
- impacto no catalogo comercial;
- tabelas e relacionamentos necessarios;
- permissoes exigidas;
- limites de assinatura, quando houver;
- testes minimos de acesso e isolamento;
- documentacao atualizada.

## Criterio de qualidade arquitetural

Uma mudanca esta alinhada com a arquitetura quando:

- nao duplica regras comuns dentro de produtos derivados;
- nao coloca regra juridica ou imobiliaria no core da plataforma;
- respeita empresa ativa e isolamento por vinculo;
- usa o catalogo como fonte oficial de oferta comercial;
- preserva snapshot de condicoes contratadas;
- permite que novos produtos futuros sejam adicionados sem reescrever a base.
