# Catalogo de precos sugeridos

## Regra de arredondamento

Os precos dos planos sugeridos sao derivados da soma dos modulos incluidos e
devem ser apresentados com uma regra comercial de arredondamento para baixo:

1. calcular o valor bruto do plano;
2. reduzir para o multiplo de `R$ 5,00` imediatamente inferior;
3. aplicar final comercial `,90`.

Exemplos:

- `R$ 146,97` -> `R$ 145,00` -> **R$ 144,90**;
- `R$ 75,60` -> `R$ 75,00` -> **R$ 74,90**.

O arredondamento nunca pode aumentar o valor bruto calculado.

## Precos mensais sugeridos

| Plano | Valor bruto | Multiplo comercial | Preco mensal sugerido |
| --- | ---: | ---: | ---: |
| Fokus Cloud Law - Advocacia | R$ 97,92 | R$ 95,00 | **R$ 94,90** |
| Fokus Cloud Law - Cartorio Criminal | R$ 106,56 | R$ 105,00 | **R$ 104,90** |
| Fokus Cloud Law - Cartorio Civel | R$ 88,74 | R$ 85,00 | **R$ 84,90** |
| Fokus Cloud Law - Gestao de Audiencias | R$ 53,37 | R$ 50,00 | **R$ 49,90** |
| Fokus Cloud Law - Gestao de Expedientes | R$ 62,01 | R$ 60,00 | **R$ 59,90** |
| Fokus Cloud Lead One - Essencial | R$ 13,23 | R$ 10,00 | **R$ 9,90** |
| Fokus Cloud Lead One - Profissional | R$ 39,87 | R$ 35,00 | **R$ 34,90** |
| Fokus Cloud Lead One - Avancado | R$ 48,78 | R$ 45,00 | **R$ 44,90** |
| Fokus Cloud Lead One - Premium | R$ 57,69 | R$ 55,00 | **R$ 54,90** |
| Fokus Cloud Lead Team - Team Essencial | R$ 75,60 | R$ 75,00 | **R$ 74,90** |
| Fokus Cloud Lead Team - Team Premium | R$ 146,97 | R$ 145,00 | **R$ 144,90** |

## Precos anuais sugeridos

O ciclo anual utiliza o equivalente a nove mensalidades do plano. O valor
resultante tambem passa pela mesma regra de arredondamento para baixo.

| Plano | Calculo anual | Valor bruto anual | Preco anual sugerido |
| --- | ---: | ---: | ---: |
| Fokus Cloud Law - Advocacia | R$ 94,90 x 9 | R$ 854,10 | **R$ 849,90** |
| Fokus Cloud Law - Cartorio Criminal | R$ 104,90 x 9 | R$ 944,10 | **R$ 939,90** |
| Fokus Cloud Law - Cartorio Civel | R$ 84,90 x 9 | R$ 764,10 | **R$ 759,90** |
| Fokus Cloud Law - Gestao de Audiencias | R$ 49,90 x 9 | R$ 449,10 | **R$ 444,90** |
| Fokus Cloud Law - Gestao de Expedientes | R$ 59,90 x 9 | R$ 539,10 | **R$ 534,90** |
| Fokus Cloud Lead One - Essencial | R$ 9,90 x 9 | R$ 89,10 | **R$ 84,90** |
| Fokus Cloud Lead One - Profissional | R$ 34,90 x 9 | R$ 314,10 | **R$ 309,90** |
| Fokus Cloud Lead One - Avancado | R$ 44,90 x 9 | R$ 404,10 | **R$ 399,90** |
| Fokus Cloud Lead One - Premium | R$ 54,90 x 9 | R$ 494,10 | **R$ 489,90** |
| Fokus Cloud Lead Team - Team Essencial | R$ 74,90 x 9 | R$ 674,10 | **R$ 669,90** |
| Fokus Cloud Lead Team - Team Premium | R$ 144,90 x 9 | R$ 1.304,10 | **R$ 1.299,90** |

## Observacao sobre o calculo anual

Os valores anuais devem ser calculados sobre o mensal sugerido arredondado,
mas a regra precisa ser implementada com uma funcao monetaria centralizada no
backend. O frontend deve apenas exibir o valor retornado pela API.

Antes da publicacao, os valores dos modulos marcados como estimados devem ser
validados comercialmente. A tabela nao deve liberar planos enquanto a versao
estiver em `rascunho`.
