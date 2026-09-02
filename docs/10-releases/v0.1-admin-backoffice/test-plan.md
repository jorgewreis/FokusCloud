# Plano de testes da release 0.1

## Testes automatizados obrigatorios

| Grupo | Cenarios minimos |
| --- | --- |
| Autenticacao interna | Login por senha exige MFA, MFA invalido falha, MFA valido inicia sessao, logout encerra sessao. |
| Isolamento | Cliente nao acessa Backoffice; admin interno nao acessa portal do cliente como usuario. |
| Bloqueio | Tentativas invalidas bloqueiam conta interna; desbloqueio exige superadministrador e auditoria. |
| Perfis | Administrador comercial nao gerencia admins, seguranca, publicacao final, override ou conciliacao corretiva. |
| Catalogo | Criar, editar, validar, publicar, pausar e arquivar itens; publico ignora itens indisponiveis. |
| Vouchers | Tipos aceitos, limites, validade, produto/plano, uso por empresa e snapshot de resgate. |
| Assinaturas | Pausa, reativacao, cancelamento agendado, upgrade, downgrade e override. |
| Billing | Checkout sandbox, webhook valido, webhook invalido, evento duplicado e pagamento recusado. |
| Conciliacao | Divergencia aberta, revisada, corrigida ou descartada conforme perfil. |
| Auditoria | Motivo obrigatorio, before/after mascarado, expiração e filtro por perfil. |

## Testes manuais no navegador

- Abrir `/backoffice/acesso` e completar login com MFA.
- Navegar por todas as paginas do menu administrativo.
- Validar que tabelas carregam dados reais via API.
- Confirmar que botoes proibidos por perfil nao aparecem e que o backend
  tambem bloqueia a acao.
- Confirmar modais de confirmacao em acoes sensiveis.
- Criar plano em rascunho e publicar com superadministrador.
- Criar voucher e verificar listagem, pausa, reativacao e uso.
- Executar fluxo sandbox Mercado Pago e conferir alteracao de status.
- Criar divergencia simulada e corrigir com superadministrador.

## Comandos de validacao

```bash
composer test
php artisan test
php artisan migrate:fresh --seed
npm run build
```

Se algum comando nao estiver disponivel no ambiente, registrar a indisponibilidade
no resultado da homologacao.

## Evidencias esperadas

- Saida dos testes automatizados.
- Capturas ou registro textual dos fluxos de homologacao.
- Lista de falhas bloqueantes resolvidas.
- Lista de falhas nao bloqueantes aceitas para versao posterior.

## Evidencia da 0.0.2

- A suite do Marco 2 cobre isolamento entre guards, senha + MFA, expiracao,
  quinta tentativa, reenvio, bloqueio temporario e manual, origem suspeita,
  RBAC, convite, ativacao, ultima conta superadministradora e auditoria.
- O gate do GitHub Actions executa `php artisan migrate:fresh --seed` em SQLite
  limpo, `php artisan test` e `npm run build` antes de liberar o deploy.
- A evidencia final deve apontar para a execucao aprovada em
  `https://github.com/jorgewreis/FokusCloud/actions/workflows/deploy.yml`.

## Evidencia da 0.0.3

- `CatalogAdminTest` cobre criacao de rascunho por administrador comercial,
  bloqueio de publicacao por perfil, publicacao por superadministrador,
  recusa de plano ativo sem composicao, estabilidade do snapshot publico e
  pausa restrita.
- O contrato publico esperado para catalogo e `contract_version: 0.0.3`, com
  `published_version`, `published_at`, produto, funcionalidades e planos.
- A validacao final deve executar `php artisan migrate:fresh --seed`,
  `php artisan test`, `npm run build` e `git diff --check`.
## Testes da release 0.0.4

Cobrir tipos de voucher, validade, limites, elegibilidade por plano, concorrência/idempotência de reserva, confirmação/liberação, snapshot, crédito comercial, edição pré/pós-resgate, exclusões, fallback de publicação e payload base_name. A validação automatizada da pré-release `v0.0.4-alpha.1` executa 57 testes aprovados, incluindo regressões de expiração, falha do gateway, webhook repetido, imutabilidade pós-resgate e exclusão com reserva pendente. O aceite final ainda requer navegador real para todos os formulários e perfis.

## Testes do Marco 5 / `v0.0.5-alpha.1`

Executar em banco descartavel SQLite e registrar a evidencia real:

```bash
php artisan migrate:fresh --seed
php artisan test
npm run build
git diff --check
```

Os cenarios abrangem consulta paginada de empresas, mascaramento, detalhe de
assinatura, pagamentos, itens, historico, snapshots, pausa, reativacao,
cancelamento agendado, upgrade pendente, downgrade agendado, override,
permissoes, auditoria e aplicacao de mudancas pelo comando agendado. Billing
sandbox, conciliacao e homologacao de navegador permanecem fora da alpha.

## Marco 6 — alpha.1

Executar em banco descartável:

```bash
php artisan migrate:fresh --seed
php artisan test
npm run build
node --check public/backoffice/assets/js/currency-input.js
git diff --check
```

A cobertura inclui checkout idempotente, HMAC válido/inválido, eventos
duplicados, recorrência, inadimplência, conciliação, permissões e reembolso.
Homologar posteriormente com credenciais sandbox, comprador/vendedor de teste,
webhook público e reembolso de teste.
