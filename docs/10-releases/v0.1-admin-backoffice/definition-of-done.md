# Definition of Done da release documental 0.0.1

## Objetivo

Definir quando o Marco 1 da release `0.1` pode ser considerado concluido como
release documental `0.0.1`.

## Pronto para a 0.0.1

A `0.0.1` esta pronta quando:

- a pasta `docs/10-releases/v0.1-admin-backoffice` existir e estiver
  referenciada pelos indices documentais;
- todos os documentos planejados para o pacote inicial existirem;
- a matriz de rastreabilidade ligar marcos, fontes normativas, aceite e testes;
- o checklist de revisao documental estiver preenchido;
- `open-decisions.md` separar decisoes bloqueantes e nao bloqueantes;
- `release-notes.md` registrar a `0.0.1` como release documental;
- `git diff --check` passar sem avisos;
- commit documental e tag anotada `v0.0.1` forem criados e enviados ao remoto.

## Fora do pronto da 0.0.1

A `0.0.1` nao exige:

- implementacao de novas telas;
- alteracao de endpoints;
- migrations novas;
- integracao Mercado Pago;
- testes funcionais do Backoffice completo.

Esses itens pertencem aos marcos 2 a 8 da release `0.1`.

## Pronto para documentos individuais

| Documento | Criterio de pronto |
| --- | --- |
| `README.md` | Explica objetivo, escopo, criterio de pronto, documentos e fontes normativas. |
| `delivery-roadmap.md` | Define os oito marcos, suas dependencias e saidas obrigatorias. |
| `module-checklist.md` | Lista itens verificaveis por modulo da 0.1. |
| `technical-gap-analysis.md` | Diferencia estado observado, lacunas bloqueantes e evolucoes necessarias. |
| `acceptance-criteria.md` | Declara aceite funcional, financeiro, tecnico, seguranca e homologacao. |
| `test-plan.md` | Define testes automatizados, manuais, comandos e evidencias. |
| `homologation-script.md` | Permite executar homologacao guiada passo a passo. |
| `release-notes.md` | Registra status documental da 0.0.1 e expectativa da 0.1. |
| `open-decisions.md` | Mantem decisoes abertas classificadas por impacto. |
| `traceability-matrix.md` | Rastreia marcos para fontes, aceite e testes. |
| `documentation-review-checklist.md` | Registra revisao final do pacote documental. |

## Pronto para marcos seguintes

Os marcos 2 a 8 continuam pendentes depois da `0.0.1`. Cada um deve ser
planejado e implementado em ciclo proprio, usando esta documentacao como fonte
de escopo, aceite e rastreabilidade.
