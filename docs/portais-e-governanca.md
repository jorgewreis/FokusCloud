# Portais e governança

## Ambientes

| Ambiente | Entrada | Identidade | Escopo |
| --- | --- | --- | --- |
| Portal do cliente | `/acesso` e `/portal` | `users`, CPF e senha | Perfil, empresa ativa, usuários e assinaturas próprias. |
| Backoffice Fokus | `/backoffice/acesso` e `/backoffice` | `platform_admins`, e-mail, senha e MFA por e-mail | Operação da plataforma. |

As sessões usam guards diferentes (`web` e `platform`). Uma sessão de cliente
não autoriza rotas `/api/backoffice`, e uma conta interna não aparece como
usuário de empresa.

## Matriz de permissões

| Ação | Usuário/Gestor | Admin da empresa | Superadmin Fokus |
| --- | --- | --- | --- |
| Perfil próprio | Sim | Sim | Não pelo portal |
| Usuários e administração da empresa | Não | Sim | Consulta no backoffice |
| Assinaturas e alterações comerciais próprias | Consulta | Sim | Gestão global |
| Empresas, vouchers e métricas globais | Não | Não | Sim |
| Reset assistido de senha | Não | Não | Sim, com ticket e motivo |

## Cobrança e suporte

Assinaturas guardam ciclo, vigência e identificador da recorrência no provedor.
Upgrade é registrado para aplicação imediata após cobrança proporcional;
downgrade e cancelamento ficam agendados para o término da vigência. Suspensão
e reativação do backoffice são imediatas e auditadas.

Todo reset de senha pelo suporte cria um token de redefinição, exige ticket e
motivo e envia notificação ao e-mail confirmado. Nenhuma senha é exibida ou
definida diretamente pelo backoffice.

## Métricas de uso

Law e Lead enviam `POST /api/integrations/usage` com o cabeçalho
`X-Fokus-Usage-Secret`. O payload diário inclui `company_id`, `product_code`,
`reported_on`, `active_users`, `licensed_seats`, `used_seats`, `key_records`,
`last_activity_at` e métricas agregadas opcionais. O segredo é definido em
`FOKUS_USAGE_INGESTION_SECRET`; ele não deve ser exposto ao navegador.

## Implantação

Defina `FOKUS_INITIAL_SUPERADMIN_NAME`, `FOKUS_INITIAL_SUPERADMIN_EMAIL` e
`FOKUS_INITIAL_SUPERADMIN_PASSWORD`, execute migrations e rode
`php artisan fokus:provision-superadmin` uma única vez. Remova a senha inicial
do ambiente após o provisionamento.
