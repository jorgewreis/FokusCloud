# Migracao do catalogo legado

## Situacao atual

O arquivo `public/assets/js/subscription-catalog.js` contem uma copia estatica de sistemas, planos, funcionalidades e precos. Essa copia pode divergir do banco e nao deve continuar sendo fonte comercial.

## Objetivo

Substituir completamente o catalogo estatico por dados reais fornecidos pela API baseada no banco.

## Etapas

1. Confirmar que todos os sistemas, planos, funcionalidades, precos e composicoes necessarios existem no banco.
2. Completar campos tecnicos e comerciais no modelo de dados.
3. Criar ou evoluir o endpoint publico do catalogo.
4. Alterar o fluxo de assinatura para consumir a API.
5. Remover listas estaticas e precos duplicados do JavaScript.
6. Validar catalogo, checkout, calculo de preco e resgate de voucher.
7. Remover o arquivo legado depois da validacao funcional.

## Regra de falha

Se a API ou o banco estiver indisponivel, o frontend deve informar a indisponibilidade. Nao deve utilizar valores simulados, dados antigos embutidos ou fallback estatico.

## Validacao de conclusao

A migracao sera considerada concluida quando:

- o catalogo publico carregar somente dados da API;
- nenhum preco comercial permanecer duplicado no frontend;
- itens pausados, arquivados e nao publicados nao aparecerem para venda;
- o checkout recalcular valores no backend;
- os valores das assinaturas e vouchers forem preservados em snapshots;
- os testes cobrirem sucesso, catalogo vazio, item inativo e falha da API.
