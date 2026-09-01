# Marco 2 - seguranca interna (`0.0.2`)

## Entrega

A `0.0.2` implementa a seguranca interna do Backoffice com identidade
separada em `platform_admins`, RBAC de plataforma, MFA obrigatorio por e-mail,
convite de ativacao, bloqueio progressivo, revogacao de sessoes e auditoria
mascarada com retencao de 180 dias.

## RBAC de plataforma

As tabelas `platform_roles`, `platform_permissions` e
`platform_role_permissions` sao exclusivas do escopo `platform` e nao usam os
perfis de empresa. Cada administrador interno possui um papel:

| Papel | Capacidades |
| --- | --- |
| `superadministrador` | Todas as capacidades `platform.*`, incluindo seguranca, auditoria completa, publicacao, override e conciliacao. |
| `administrador_comercial` | Acesso, painel, catalogo, empresas, assinaturas, vouchers e auditoria comercial. |

Permissoes obrigatorias: `platform.access`, `platform.dashboard.view`,
`platform.catalog.manage`, `platform.companies.view`,
`platform.subscriptions.manage`, `platform.vouchers.manage`,
`platform.audit.view_commercial`, `platform.audit.view_all`,
`platform.security.manage`, `platform.catalog.publish`,
`platform.commercial.override` e `platform.reconciliation.manage`.

## Acesso e bloqueio

- Login interno: e-mail, senha e MFA de seis digitos enviado por e-mail.
- O desafio expira em 10 minutos, aceita no maximo cinco tentativas e pode ser
  reenviado apos 60 segundos; o reenvio invalida o codigo anterior.
- Tres falhas de senha em 10 minutos bloqueiam temporariamente por 10 minutos.
- Cinco falhas em 24 horas bloqueiam manualmente a conta ate acao de um
  superadministrador.
- Cinco e-mails internos distintos falhos no mesmo IP ou dispositivo em 10
  minutos impedem novas tentativas daquela origem durante a janela.
- IP e dispositivo sao usados somente como dado tecnico de seguranca; o
  identificador de dispositivo e armazenado como hash.

O bloqueio, desativacao ou troca de perfil da ultima conta ativa de
`superadministrador` e recusado. Mudancas de papel, bloqueio e desativacao
revogam imediatamente as sessoes internas existentes.

## Convites e endpoints

O superadministrador cria um convite com nome, e-mail e papel. O convite usa
token de uso unico, expira em 24 horas e permite que o destinatario defina a
propria senha. Senhas nao sao enviadas por e-mail.

| Endpoint | Controle |
| --- | --- |
| `POST /api/backoffice/auth/login` | Publico, limitado e sujeito a risco. |
| `POST /api/backoffice/auth/verify-mfa` e `/resend-mfa` | Desafio pendente. |
| `POST /api/backoffice/auth/activate-invitation` | Token unico valido. |
| `GET /api/backoffice/admins` e acoes de convite/perfil/bloqueio | `platform.security.manage`. |
| Endpoints comerciais existentes | Permissao `platform.*` especifica por rota. |

## Evidencias de aceite

- A suite automatizada cobre os 18 cenarios minimos e os controles de borda
  adicionais de auditoria, convite e isolamento entre guards.
- `php artisan migrate:fresh --seed`, `php artisan test` e `npm run build`
  executam no gate SQLite do GitHub Actions antes do deploy.
- Cliente autenticado no guard `web` nao acessa o Backoffice; administrador
  comercial recebe `403` ao tentar gerir administradores internos.
- Eventos de seguranca gravam ator, acao, origem, before/after mascarados e
  `expires_at` de 180 dias, com redacao defensiva de senha, token, MFA, CPF,
  CNPJ e documento.
- A evidencia de execucao aprovada fica no workflow de deploy:
  `https://github.com/jorgewreis/FokusCloud/actions/workflows/deploy.yml`.

## Fechamento registrado

- Implementacao de seguranca: `9f73f4c0f5500e9f69ef2b23f358c9a4e8b83f58`.
- Validacao e publicacao aprovadas: `https://github.com/jorgewreis/FokusCloud/actions/runs/33564168328`.
- O gate executou migration/seeder SQLite, 36 testes automatizados e build do
  frontend antes da publicacao. A tag anotada `v0.0.2` foi atualizada para o
  fechamento consolidado.
