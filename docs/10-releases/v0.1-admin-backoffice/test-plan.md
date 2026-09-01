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
