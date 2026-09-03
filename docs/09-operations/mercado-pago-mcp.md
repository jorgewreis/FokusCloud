# MCP do Mercado Pago no VS Code

O servidor local `tools/mercado-pago-mcp.mjs` expõe consultas somente de leitura para ajudar no diagnóstico do checkout e das assinaturas.

## Configuração

Defina o token no ambiente do usuário, sem gravá-lo no repositório:

```powershell
[Environment]::SetEnvironmentVariable('MERCADO_PAGO_ACCESS_TOKEN', 'SEU_ACCESS_TOKEN', 'User')
[Environment]::SetEnvironmentVariable('MERCADO_PAGO_API_BASE_URL', 'https://api.mercadopago.com', 'User')
```

Reinicie o VS Code e use `MCP: List Servers` para iniciar `mercado-pago`.

## Ferramentas disponíveis

- `mercado_pago_get_subscription`
- `mercado_pago_get_payment`
- `mercado_pago_search_subscriptions`
- `mercado_pago_get_authorized_payments`

O servidor não recebe e-mail, nome de usuário, código de verificação ou senha do FokusCloud. O e-mail só pode ser informado como filtro de pesquisa quando necessário. Não foram incluídas operações de criação, cancelamento, pausa ou alteração de assinatura.
