# Modelo de dados do catalogo

## Tabelas principais

### `products`

Representa os sistemas comercializados.

Campos principais:

- `id`: identificador prefixed ULID;
- `code`: codigo tecnico unico;
- `name`: nome exibido;
- `active`: indicador atual de atividade;
- `created_at` e `updated_at`.

### `plans`

Representa os planos de cada sistema.

- `product_id` referencia `products.id`;
- `code` e unico dentro do sistema;
- `name` e o nome exibido.

Para os planos do produto `lead`, deve existir um campo de linha/segmento com os valores `one` ou `team`. O codigo completo recomendado inclui a linha, por exemplo `lead-one-essencial`.

A combinacao `product_id` + `code` e unica.

### `modules`

Representa funcionalidades vendaveis ou componiveis.

- `product_id` referencia o sistema;
- `code` e unico dentro do sistema;
- `name` e o nome exibido;
- `monthly_price` e o preco mensal em BRL.

### `plan_modules`

Tabela associativa entre planos e funcionalidades. Possui chave primaria composta por `plan_id` e `module_id`.

As chaves estrangeiras garantem que a relacao exista e que a exclusao de um plano ou funcionalidade nao deixe associacoes orfas.

## Integridade

O backend deve validar:

- plano pertencente ao sistema informado;
- funcionalidade pertencente ao mesmo sistema do plano;
- ao menos uma funcionalidade em plano publicavel;
- precos nao negativos;
- codigos unicos;
- dependencias e incompatibilidades;
- exclusao fisica somente quando nao houver vinculos.

## Historico comercial

Alteracoes de preco, composicao e limites nao devem modificar o historico de assinaturas ou resgates. Esses registros devem armazenar os valores aplicados no evento.

O catalogo deve manter versoes publicadas para consulta historica. A restauracao automatica de uma versao anterior nao faz parte da regra aprovada.

## Evolucao estrutural necessaria

O modelo atual possui somente os campos basicos de produto, plano e modulo. Para atender ao modelo aprovado, a evolucao deve adicionar ou normalizar:

- descricao tecnica e conteudo comercial;
- status comercial e estado de publicacao;
- ordem de exibicao e destaque;
- linha comercial do Lead;
- precos e descontos por ciclo;
- limites, unidades e opcoes de capacidade;
- dependencias e incompatibilidades;
- capacidades comerciais para diferenciar usos do mesmo modulo, quando aplicavel;
- tipos e instancias operacionais por setor para `expedicoes`, com sequencias de numeracao independentes quando aplicavel;
- guias de execucao comum e penal como tipos ou capacidades de `expedicoes`;
- versoes publicadas e agendamentos.

Esses dados devem ser modelados antes da ativacao do gerenciamento completo pelo backoffice.

## Carga inicial

`database/seeders/DatabaseSeeder.php` contem a carga inicial de sistemas, funcionalidades, planos e associacoes. Ele nao deve ser tratado como a interface definitiva de manutencao comercial quando o gerenciamento pelo backoffice estiver disponivel.
