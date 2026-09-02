# Marco 4 - Vouchers e exclusões controladas (0.0.4)

## Objetivo

Entregar vouchers administráveis e utilizáveis no checkout, com reserva
temporária, confirmação idempotente, liberação segura e snapshot completo do
benefício. A mesma entrega corrige os formulários do Backoffice e torna
explícitas as regras de exclusão física do catálogo.

## Status da pré-release `v0.0.4-alpha.1`

A implementação funcional do Marco 4 está presente na branch `develop`, com
validação automatizada aprovada em 57 testes e 271 asserções. A validação
inclui reservas, expiração, liberação em falha, idempotência de webhook,
snapshot, `commercial_credit`, governança de edição e exclusões condicionais.

O build frontend e `git diff --check` também foram aprovados. A homologação
manual em navegador real permanece pendente para as etapas `beta`, `rc` ou a
versão final. A tag `v0.0.4-alpha.1` só deve ser criada após a integração da
branch com `origin/main`.

## Contratos implementados

- commercial_credit concede crédito fixo limitado ao valor da primeira
  cobrança; não cria carteira nem recorrência.
- O checkout reserva o voucher por 30 minutos. A reserva conta para os limites,
  recebe request_key idempotente e é liberada em falha, cancelamento,
  abandono/expiração ou encerramento.
- O webhook de aprovação confirma uma única reserva e cria o resgate. O
  snapshot registra voucher, código, produto, plano, tipo, valor, base,
  desconto, valor final, ciclo, empresa, assinatura e janela do benefício.
- Voucher sem resgate pode ser editado. Depois do primeiro resgate, as regras
  comerciais ficam imutáveis; somente pausa, reativação e encerramento são
  permitidos. Exclusão física exige ausência de resgates e reservas pendentes.
- Planos e funcionalidades só são excluídos fisicamente sem dependências
  históricas, comerciais, de assinatura ou de publicação; caso contrário a API
  retorna 422 explicando o vínculo e orientando arquivamento.
- Apenas a publicação ativa/mais recente pode ser excluída. A anterior volta a
  ser a publicação corrente; versões históricas continuam protegidas.
- A interface usa ícones compartilhados, diálogo acessível com motivo
  obrigatório, confirmação explícita, validação FokusForm, tratamento de API e
  estado de carregamento.

## APIs

- PATCH /api/backoffice/vouchers/{voucher}
- POST /api/backoffice/vouchers/{voucher}/archive
- GET /api/backoffice/vouchers/{voucher}
- DELETE /api/backoffice/catalog/plans/{plan}
- DELETE /api/backoffice/catalog/modules/{module}
- DELETE /api/backoffice/catalog/publications/{publication}
- POST /api/subscriptions/checkout com reserva/aplicação de voucher

O contrato público de GET /api/catalog/{product} permanece 0.0.3; 0.0.4 é a
versão da entrega do conjunto.

## Aceite técnico

Executar migrate:fresh --seed, suíte PHP, build frontend e git diff --check.
Homologar no navegador os formulários de segurança, empresas, produtos,
funcionalidades, planos, publicação e vouchers, nos dois perfis, incluindo
sucesso, validação, erro de API, edição, exclusão e carregamento dinâmico.
