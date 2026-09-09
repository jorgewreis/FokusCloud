# Arquitetura de ofertas

## Nomes comerciais oficiais

| Codigo do produto | Nome comercial |
| --- | --- |
| `law` | Fokus Law |
| `lead` | Fokus Lead |

O codigo tecnico nao deve ser alterado apenas porque o nome comercial mudou.

## Linhas do Fokus Lead

O produto `lead` possui duas linhas comerciais armazenadas nos planos:

| Segmento | Nome comercial | Perfil de oferta |
| --- | --- | --- |
| `one` | Fokus Lead One | Corretores independentes |
| `team` | Fokus Lead Team | Imobiliarias e grupos de corretores |

One e Team compartilham o produto tecnico e podem compartilhar funcionalidades, mas devem possuir composicoes e limites proprios.

## Nomes completos dos planos

O catalogo deve exibir sempre `Nome do sistema - Nome-base do plano`.

### Fokus Law

| Codigo | Nome-base | Nome completo |
| --- | --- | --- |
| `law-advocacia` | Advocacia | Fokus Law - Advocacia |
| `law-cartorio-criminal` | Cartorio Criminal | Fokus Law - Cartorio Criminal |
| `law-cartorio-civel` | Cartorio Civel | Fokus Law - Cartorio Civel |
| `law-gestao-audiencias` | Gestao de Audiencias | Fokus Law - Gestao de Audiencias |
| `law-gestao-expedientes` | Gestao de Expedientes | Fokus Law - Gestao de Expedientes |

### Fokus Lead One

| Codigo | Nome-base | Nome completo |
| --- | --- | --- |
| `lead-one-essencial` | Essencial | Fokus Lead One - Essencial |
| `lead-one-profissional` | Profissional | Fokus Lead One - Profissional |
| `lead-one-avancado` | Avancado | Fokus Lead One - Avancado |
| `lead-one-premium` | Premium | Fokus Lead One - Premium |

### Fokus Lead Team

| Codigo | Nome-base | Nome completo |
| --- | --- | --- |
| `lead-team-essencial` | Team Essencial | Fokus Lead Team - Essencial |
| `lead-team-premium` | Team Premium | Fokus Lead Team - Premium |

## Oferta sugerida e personalizada

Os 11 planos sao ofertas sugeridas. O cliente pode alterar limites permitidos e montar uma composicao personalizada, mas a combinacao personalizada deve ser identificada como `Personalizada` e nao deve criar um novo plano permanente.
