# Politica de seguranca da conta

## Principios

- A conta global e separada dos vinculos empresariais.
- O CPF identifica a conta, mas nunca substitui autenticacao.
- Nenhum administrador de empresa pode contornar a seguranca da conta global.
- Alteracoes criticas devem invalidar acessos e gerar auditoria.
- A interface informa disponibilidade; o servidor decide autorizacao.

## Credenciais

- O login inicial usa CPF e senha.
- CPF e e-mail sao normalizados e unicos.
- Senhas sao armazenadas somente como hash Argon2id.
- Recuperacao usa link de uso unico, de expiracao curta.
- Troca de senha invalida sessoes web e refresh tokens moveis.
- Outras credenciais, como provedores externos e passkeys, podem ser adicionadas
  sem substituir obrigatoriamente a senha local.

## Tentativas de login

O sistema deve aplicar limitacao progressiva por CPF, IP e dispositivo, com
bloqueios temporarios e registro de eventos. Nao deve bloquear uma conta
permanentemente apenas por tentativas invalidas.

Bloqueios temporarios podem ser removidos automaticamente apos o prazo. Um
bloqueio relacionado a incidente, abuso ou risco relevante exige revisao do
backoffice. O administrador de empresa nunca desbloqueia uma conta global.

## MFA

O MFA sera opcional para todos os perfis. O metodo inicial sera aplicativo
autenticador com TOTP e codigos de recuperacao de uso unico.

Quando o dispositivo for perdido, o usuario podera usar um codigo de
recuperacao. Sem codigo, a recuperacao exigira suporte, validacao de identidade
e auditoria. O administrador da empresa nao podera remover o MFA de outro
usuario.

Dispositivos confiaveis serao opcionais, terao validade limitada e poderao ser
revogados individualmente. Nao poderao contornar exigencias especificas para
operacoes de alto risco.

## Notificacoes

O usuario podera escolher quais notificacoes de seguranca deseja receber.
Avisos criticos, como alteracao de senha, alteracao de e-mail, ativacao ou
remocao de MFA e encerramento de sessoes, poderao ser obrigatorios conforme a
politica de seguranca e nao poderao ser completamente desativados.

Termos de Uso e Politica de Privacidade vigentes devem ser aceitos antes do
acesso ao portal, depois da confirmacao do e-mail. Alteracoes materiais exigem
novo aceite; correcoes editoriais nao exigem.

Eventos de novo login, novo dispositivo, alteracao de senha, alteracao de
e-mail, ativacao ou remocao de MFA e encerramento de sessoes devem ser
registrados. Tentativas invalidas serao registradas e notificadas conforme
limites e comportamento suspeito.

## Aceites legais

Aceites ficam em estrutura propria, com usuario, documento, versao, data, IP e
contexto. Novo aceite sera exigido somente em alteracao material ou novo
documento obrigatorio.

## Referencias

- [Ciclo de vida da conta global](../05-requirements/user-account-lifecycle.md)
- [Modelo relacional](../06-data/relational-model.md)
- [Modelo de permissoes e perfis](permission-model.md)
- [Privacidade e LGPD](privacy-and-lgpd.md)
