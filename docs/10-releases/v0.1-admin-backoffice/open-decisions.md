# Decisoes abertas das releases 0.0.1 e 0.1

## Release documental 0.0.1

| Decisao | Status | Resultado |
| --- | --- | --- |
| Saida formal do Marco 1 | Fechada | Pacote rastreavel com matriz, Definition of Done e checklist documental. |
| Status do Marco 1 | Fechada | Concluido apos checks documentais, commit, tag anotada e push. |
| Versionamento | Fechada | Commit documental e tag anotada `v0.0.1`. |
| Sincronizacao | Fechada | Push do commit e da tag ao remoto. |

## Decisoes bloqueantes da 0.0.1

Nenhuma decisao bloqueante registrada para a release documental `0.0.1`.

## Decisoes nao bloqueantes iniciais

| Decisao | Default para 0.1 | Impacto |
| --- | --- | --- |
| Estrategia final de branches | Usar fluxo atual do projeto ate decisao formal. | Nao bloqueia implementacao funcional. |
| Plano de rollback operacional | Registrar rollback manual por release ate processo definitivo. | Nao bloqueia homologacao, mas deve ser resolvido antes de producao critica. |
| Agendamento de publicacao de catalogo | Fora da 0.1. Publicacao e imediata com confirmacao. | Mantem escopo menor e coerente com documentos atuais. |
| Aprovacao em duas pessoas | Fora da 0.1. Superadministrador executa acoes finais. | Pode entrar em versao posterior. |
| Emissao fiscal | Fora da 0.1. | Depende de integracao propria. |

## Decisoes bloqueantes

Nenhuma decisao bloqueante registrada no inicio da release.

## Decisoes fechadas na 0.0.2

| Decisao | Resultado |
| --- | --- |
| Modelo de permissao interna | RBAC em tabelas proprias do escopo `platform`. |
| MFA interno | Obrigatorio por e-mail; TOTP fica fora deste marco. |
| Bloqueio | 3 falhas/10 minutos temporario; 5 falhas/24 horas manual; origem com 5 e-mails distintos bloqueada por 10 minutos. |
| Provisionamento | Convite de ativacao com token unico de 24 horas; senha definida pelo destinatario. |
| Sessoes | Revogacao imediata ao mudar papel, bloquear ou desativar. |

## Decisoes fechadas na 0.0.3

| Decisao | Resultado |
| --- | --- |
| Escopo do catalogo | Marco 3 cobre produtos, funcionalidades, planos, composicoes, precos e publicacao. |
| Contrato publico | `GET /api/catalog/{product}` retorna contrato versionado `0.0.3` baseado em snapshot publicado. |
| Publicacao | Imediata, exclusiva do superadministrador, com motivo e auditoria. |
| Compatibilidade do frontend | O fluxo de assinatura normaliza o contrato novo sem manter preco ou composicao estatica. |

## Regra de atualizacao

Toda nova duvida deve ser classificada como:

- bloqueante: impede implementar ou homologar a 0.1;
- nao bloqueante: pode seguir para versao posterior sem comprometer a entrega
  funcional definida.
## Decisões fechadas no Marco 4 / 0.0.4

| Decisão | Resultado |
| --- | --- |
| Exclusão | Física quando não houver dependências; caso contrário, 422 e arquivamento. |
| Escopo do voucher | Fluxo completo de reserva, confirmação e snapshot; Mercado Pago billing avançado permanece no Marco 6. |
| commercial_credit | Crédito fixo limitado à primeira cobrança, sem carteira ou recorrência. |
| Edição | Permitida antes do primeiro resgate; após resgate, somente pausa, reativação e encerramento. |
| Publicação | Apenas atual pode ser removida; restaura a anterior e protege histórico. |
| Contrato | Catálogo público permanece em 0.0.3; release do conjunto é 0.0.4. |

## Decisoes fechadas no Marco 5 / 0.0.5

| Decisao | Resultado |
| --- | --- |
| Escopo da alpha | Consulta de empresas e gestao de assinaturas, sem novos campos de notas ou status comerciais em `companies`. |
| Fonte comercial | Composicao e preco de upgrade/downgrade vem do catalogo publicado; valores do navegador nao sao fonte de verdade. |
| Billing | Billing sandbox completo, conciliacao e reembolsos permanecem no Marco 6. |
| Dados pessoais | APIs e paginas administrativas exibem documentos e e-mails mascarados e nao alteram dados cadastrais. |

| Marco 6 alpha | Mercado Pago é o gateway prevalente; webhooks são síncronos e idempotentes; billing real, emissão fiscal e chargeback ficam fora da alpha. |
| Reembolsos | Administrador comercial solicita; somente superadministrador aprova, recusa e executa, sempre com motivo e auditoria. |
