# Checklist por modulo da release 0.1

## Painel geral

- [ ] Exibir empresas ativas, assinaturas ativas, receita prevista e receita recebida.
- [ ] Exibir alertas por fila, severidade e vencimento.
- [ ] Exibir eventos sensiveis recentes.
- [ ] Diferenciar dados internos, dados do Mercado Pago e divergencias.

## Empresas

- [ ] Listar empresas com busca por nome e documento mascarado.
- [ ] Exibir assinatura, plano, ciclo, status comercial, pagamentos e uso recente.
- [ ] Permitir somente gestao comercial autorizada.
- [ ] Bloquear alteracao de CPF, CNPJ, razao social, nome civil, e-mail e senha.

## Planos e catalogo

- [x] Criar e editar produtos, planos, modulos, precos e composicoes.
- [x] Separar status operacional de estado de publicacao.
- [x] Validar plano antes da publicacao.
- [x] Permitir publicacao, pausa e arquivamento apenas para superadministrador.
- [ ] Invalidar cache apos publicacao, quando houver cache.
- [x] Garantir que catalogo publico e checkout ignorem rascunhos, pausados,
  arquivados e incompletos.

## Assinaturas

- [ ] Consultar assinatura, pagamentos, historico e snapshots.
- [ ] Pausar e reativar assinatura com motivo e auditoria.
- [ ] Agendar cancelamento para fim da vigencia.
- [ ] Aplicar upgrade somente apos cobranca proporcional aprovada.
- [ ] Agendar downgrade para fim da vigencia.
- [ ] Recalcular valores no backend pelo catalogo publicado.
- [ ] Permitir override manual apenas para superadministrador, com motivo e diff.

## Vouchers/cupons

- [ ] Suportar `trial_free`, `percentage`, `fixed` e `commercial_credit`.
- [ ] Validar produto, plano, validade, limites e elegibilidade.
- [ ] Criar, editar, pausar e arquivar voucher conforme perfil.
- [ ] Consultar uso por voucher e empresa.
- [ ] Preservar snapshot completo no resgate.

## Usuarios internos, perfis e seguranca

- [x] Usar `platform_admins` separado de `users`.
- [x] Exigir login interno em `/backoffice/acesso`.
- [x] Exigir MFA por e-mail.
- [x] Bloquear conta interna apos tentativas invalidas conforme politica.
- [x] Permitir desbloqueio apenas por superadministrador.
- [x] Implementar perfis `superadministrador` e `administrador_comercial`.
- [x] Aplicar permissao no servidor para cada acao sensivel.

## Auditoria

- [ ] Registrar ator, acao, entidade, empresa, data, motivo e origem tecnica.
- [ ] Registrar `before_masked` e `after_masked` em alteracoes sensiveis.
- [ ] Definir `expires_at` para eventos com retencao de 180 dias.
- [ ] Mascarar CPF, CNPJ, tokens, codigos MFA, senhas e payloads sensiveis.
- [ ] Filtrar consulta de auditoria conforme perfil interno.

## Billing e conciliacao

- [ ] Criar checkout/preapproval em sandbox Mercado Pago.
- [ ] Persistir assinatura e pagamento somente depois de sucesso no gateway.
- [ ] Validar webhook e processar evento de forma idempotente.
- [ ] Atualizar pagamentos e assinaturas por status interno alvo.
- [ ] Criar alerta de inadimplencia e tolerancia expirada.
- [ ] Criar divergencia de conciliacao quando status interno divergir do gateway.
- [ ] Permitir correcao manual apenas por superadministrador, com auditoria.
## Marco 4 / 0.0.4

- [x] Vouchers com trial, percentual, valor fixo e crédito comercial.
- [x] Reserva, confirmação, liberação, expiração, idempotência e snapshot.
- [x] Edição antes do primeiro resgate e imutabilidade posterior.
- [x] Exclusões físicas condicionais e fallback de publicação.
- [x] Ícones, diálogo acessível e contrato FokusForm nas telas de catálogo/vouchers.
- [x] Suíte automatizada da `v0.0.4-alpha.1` aprovada com 57 testes.
- [ ] Homologação manual em navegador real dos formulários e dos dois perfis.
