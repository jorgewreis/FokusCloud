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
