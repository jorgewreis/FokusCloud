# Vouchers

## Finalidade

Vouchers concedem beneficios comerciais para a contratacao de sistemas e planos.

## Beneficios suportados

- `trial_free`: assinatura gratuita conforme a duracao definida;
- `percentage`: desconto percentual;
- `fixed`: desconto em valor fixo.

Para Trial Free, o desconto corresponde a 100% e os campos de percentual e valor devem permanecer bloqueados no formulario.

## Aplicacao

Um voucher pode ser vinculado a:

- um sistema e um plano especifico;
- um sistema e todos os seus planos.

O plano escolhido deve pertencer ao sistema informado.

## Valores

O desconto deve ser calculado sobre o preco vigente do plano no momento do resgate. O cadastro pode exibir a base atual para conferencia, mas o valor final deve ser recalculado no backend.

O percentual deve estar entre 0 e 100. Valores monetarios devem usar BRL com duas casas decimais. O backend nunca deve confiar apenas no calculo feito pelo navegador.

## Validade e status

O cadastro deve exigir data inicial igual ou posterior a hoje. A data final e calculada a partir da duracao do beneficio.

Status comerciais:

- `ativa`: pode ser resgatado;
- `suspensa`: temporariamente bloqueado;
- `encerrada`: cancelado;
- `expirada`: derivado automaticamente quando a data final passou.

O estado expirado nao deve depender de alteracao manual.

## Limites

Cada voucher pode possuir:

- limite total de resgates;
- limite de resgates por empresa;
- lote de codigos individuais.

O codigo pode ser gerado automaticamente ou informado manualmente, sempre com validacao de unicidade.

## Snapshot do resgate

Cada resgate deve preservar, no minimo:

- codigo e voucher;
- sistema e plano;
- preco-base;
- tipo e valor do beneficio;
- valor efetivamente descontado;
- preco final;
- periodo concedido;
- assinatura criada;
- empresa que utilizou o voucher.

Isso preserva a auditoria mesmo depois de alteracoes no catalogo.
