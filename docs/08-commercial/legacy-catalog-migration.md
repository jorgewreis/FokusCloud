# Migracao do catalogo legado

## Situacao atual

O arquivo `public/assets/js/subscription-catalog.js` nao contem mais copia
estatica de sistemas, planos, funcionalidades ou precos. Ele consulta
`GET /api/catalog/{product}` e normaliza o contrato versionado `0.0.3` para o
fluxo de assinatura.

## Objetivo

Substituir completamente o catalogo estatico por dados reais fornecidos pela API baseada no banco.

## Etapas

1. Confirmar que todos os sistemas, planos, funcionalidades, precos e composicoes necessarios existem no banco. Concluido no Marco 3.
2. Completar campos tecnicos e comerciais no modelo de dados. Concluido no Marco 3.
3. Criar ou evoluir o endpoint publico do catalogo. Concluido com contrato `0.0.3`.
4. Alterar o fluxo de assinatura para consumir a API. Concluido com normalizacao no frontend.
5. Remover listas estaticas e precos duplicados do JavaScript. Concluido.
6. Validar catalogo, checkout, calculo de preco e resgate de voucher. Parcial: catalogo e checkout base cobertos; resgate completo segue no Marco 4.
7. Remover o arquivo legado depois da validacao funcional. O arquivo permanece apenas como adaptador da API, sem dados estaticos.

## Regra de falha

Se a API ou o banco estiver indisponivel, o frontend deve informar a indisponibilidade. Nao deve utilizar valores simulados, dados antigos embutidos ou fallback estatico.

## Validacao de conclusao

A migracao sera considerada concluida quando:

- o catalogo publico carregar somente dados da API. Concluido na `0.0.3`;
- nenhum preco comercial permanecer duplicado no frontend. Concluido na `0.0.3`;
- itens pausados, arquivados e nao publicados nao aparecerem para venda. Concluido via snapshot publicado;
- o checkout recalcular valores no backend. Concluido contra snapshot publicado;
- os valores das assinaturas e vouchers forem preservados em snapshots;
- os testes cobrirem sucesso, catalogo vazio, item inativo e falha da API.
