# Decisoes arquiteturais

Este arquivo resume decisoes arquiteturais aprovadas. Decisoes extensas podem receber arquivos proprios nesta pasta.

## Modelo de ADR

```text
Titulo:
Status: proposto | aprovado | substituido
Contexto:
Decisao:
Consequencias positivas:
Consequencias negativas:
Data:
```

## Decisoes iniciais

- Plataforma base comum para identidade, empresas, permissoes, assinaturas, catalogo e backoffice.
- Produtos derivados separados por dominio: Fokus Law e Fokus Lead.
- Dados comerciais devem migrar para banco/API, evitando fonte duplicada no frontend.
- A identidade do usuario e global; um usuario pode criar e participar de varias empresas.
- Uma empresa pode contratar varios produtos Fokus no mesmo ambiente.
- A empresa aceita pessoa fisica ou juridica, identificada por CPF ou CNPJ unico.
- Filiais sao unidades internas da matriz e podem manter CNPJ proprio.
- Perfis base sao fixos inicialmente; unidades restringem o alcance sem criar perfis paralelos.
- O gestor administra usuarios somente nas unidades a que pertence, sem alterar perfis.
- Suspensoes e mudancas de acesso entram em vigor imediatamente.
