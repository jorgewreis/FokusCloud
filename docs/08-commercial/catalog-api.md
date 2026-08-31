# API do catalogo

## Fonte e responsabilidade

A API deve consultar o banco de dados e ser a unica fonte consumida pelo backoffice e pelo catalogo publico. Nenhum cliente deve depender de uma lista de nomes ou precos duplicada em JavaScript.

Para o Fokus Law, cada modulo retornado deve incluir `module_code`,
`segment_code`, `context_code`, `variant_code`, `capabilities`,
`dependencies` e `incompatibilities`, permitindo diferenciar a oferta para
advocacia, Judiciario e setor publico sem duplicar o nucleo tecnico.

## Catalogo do backoffice

Endpoint atual:

```http
GET /api/backoffice/catalog
```

Requer autenticacao de administrador da plataforma.

Resposta atual, em forma resumida:

```json
{
  "products": [
    {
      "id": "PRD...",
      "code": "law",
      "name": "Fokus Cloud Law",
      "plans": [
        {
          "id": "PLN...",
          "code": "law-advocacia",
          "name": "Advocacia"
        }
      ]
    }
  ]
}
```

O endpoint atual considera produtos ativos, agrupa as funcionalidades do plano e calcula o valor mensal a partir da composicao cadastrada.

## Requisitos do contrato alvo

O contrato evoluido deve contemplar, quando implementado:

- descricao tecnica e conteudo comercial;
- status e estado de publicacao;
- ordem de exibicao e destaque;
- ciclos mensal e anual;
- descontos configurados;
- funcionalidades, limites e dependencias;
- versao publicada do catalogo.

## Catalogo publico

O endpoint publico deve retornar somente itens ativos, publicados, completos e comercializaveis. Registros pausados, arquivados ou em rascunho nao podem ser usados no checkout.

Em caso de falha de leitura, a aplicacao deve retornar erro controlado e o frontend deve informar indisponibilidade. Nao e permitido usar dados simulados ou o catalogo legado como fallback.

## Cache

O cache pode ser utilizado para leitura, mas deve ser invalidado automaticamente no momento da publicacao. A versao publicada deve estar disponivel imediatamente apos a invalidacao.
