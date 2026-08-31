# Ciclo de vida da conta global

## Objetivo

Definir a identidade global do usuario no Fokus Cloud, incluindo cadastro,
identificacao, confirmacao, credenciais, recuperacao, sessoes, convites,
encerramento e transferencia de administracao.

## Identidade global

- O usuario possui uma conta global e pode participar de varias empresas.
- O CPF e obrigatorio, valido, normalizado e unico.
- O CPF e o identificador principal de login.
- O e-mail principal e obrigatorio e unico na conta global.
- O e-mail serve para confirmacao, recuperacao e notificacoes.
- O CPF nao pode ser alterado pelo usuario, administrador ou empresa.
- Correcoes de CPF dependem do backoffice, validacao documental, justificativa,
  auditoria e invalidacao das sessoes.

## Estados da conta

| Estado | Significado | Acesso ao portal |
| --- | --- | --- |
| `pending` | Cadastro ou confirmacao ainda incompleto. | Nao |
| `active` | Conta confirmada e utilizavel. | Conforme vinculos ativos |
| `suspended` | Suspensao administrativa temporaria. | Nao |
| `blocked` | Bloqueio de seguranca, abuso ou incidente. | Nao |
| `closed` | Conta encerrada pelo usuario ou pela plataforma. | Nao |

Antes da confirmacao do e-mail, o usuario pode apenas concluir o cadastro e
confirmar a conta. O portal empresarial permanece bloqueado.

Depois da confirmacao do e-mail, o usuario ainda deve aceitar os documentos
obrigatorios vigentes antes de acessar o portal empresarial.

Conta `blocked` pode ser desbloqueada automaticamente quando o bloqueio for
temporario ou por revisao do backoffice. Ao sair de `closed`, a reabertura
depende do backoffice e de validacao de identidade.

## Senha e recuperacao

- A senha sera armazenada somente como hash Argon2id.
- A senha original nunca sera armazenada ou recuperada.
- A recuperacao usara link aleatorio, de uso unico e expiracao curta.
- O token sera invalidado depois do uso, de nova solicitacao ou da troca da
  senha.
- Tokens de recuperacao nao serao armazenados em formato recuperavel.

## E-mail principal

Para trocar o e-mail principal, o usuario devera informar a senha atual,
confirmar o novo e-mail e concluir os controles de seguranca exigidos. A
alteracao deve notificar o e-mail anterior e invalidar sessoes sensiveis.

## Convites

| Situacao | Regra |
| --- | --- |
| Usuario ja existente | Reutilizar conta global e criar novo vinculo. |
| Usuario inexistente | Criar identidade global `pending` com CPF e e-mail. |
| Unidade inicial | Convite sempre nasce associado a uma unidade especifica. |
| Validade | 7 dias. |
| Reenvio | Invalida o token anterior e gera novo token. |
| Cancelamento | `admin` ou `gestor` autorizado cancela dentro do proprio escopo. |
| Recusa | Usuario pode recusar; token e invalidado e empresa e notificada. |
| Aceite | Usuario confirma identidade, cria senha quando necessario e ativa o vinculo. |

Cancelar ou recusar um convite nao exclui a conta global do convidado. A
conta pode possuir outros vinculos ou receber novo convite posteriormente.

## Empresas e unidades

- O usuario pode criar varias empresas na mesma conta global.
- Uma empresa pode contratar varios produtos Fokus.
- Empresas podem representar pessoa fisica ou juridica.
- Cada CPF ou CNPJ pode identificar uma unica empresa ativa.
- Filiais sao unidades internas da matriz e podem manter CNPJ proprio.
- O usuario pode ser associado a uma ou mais unidades da empresa.

## Sessoes

O portal web utilizara sessoes gerenciadas pelo servidor, com cookies seguros.
Aplicativos moveis utilizarao JWT de curta duracao e refresh tokens rotativos,
vinculados ao dispositivo e revogaveis individualmente.

O usuario podera manter varias sessoes, visualizar seus dispositivos e
encerrar uma sessao especifica ou todas as sessoes. Suspensoes, remocoes e
mudancas de permissao deverao reavaliar ou invalidar acessos imediatamente.

Se a conta global for suspensa ou bloqueada, todos os acessos serao encerrados,
mas os vinculos empresariais serao preservados. Na reativacao, cada `admin`
devera aprovar novamente o vinculo e selecionar as unidades que voltarao a ser
autorizadas.

## Encerramento da conta

O usuario somente podera encerrar a conta depois de deixar todas as empresas
e transferir responsabilidades de administracao. O encerramento nao implica
exclusao imediata dos dados; aplicam-se as politicas de retencao, anonimização
e eliminacao do Fokus Cloud.

## Transferencia de administracao

1. O `admin` atual inicia a transferencia.
2. O novo responsavel recebe convite especifico.
3. O novo responsavel aceita e confirma a transferencia.
4. O sistema troca os papeis em uma unica transacao.
5. O evento registra iniciador, novo responsavel, empresa, data e resultado.

A transferencia nao pode deixar a empresa sem administrador nem criar dois
administradores ativos.

## Preferencias e aceites

Idioma e fuso horario ficam em estrutura propria de preferencias. O usuario
podera escolher notificacoes de seguranca, respeitando avisos criticos
obrigatorios. Aceites legais ficam em tabela propria, com documento, versao,
data, IP e contexto; novo aceite sera exigido apenas em alteracoes materiais.

## Dependencias

- [Requisitos de identidade e acesso](identity-and-access.md)
- [Modelo relacional](../06-data/relational-model.md)
- [Fluxo de identidade e acesso](../03-architecture/identity-and-access-flow.md)
- [Modelo de permissoes e perfis](../07-security/permission-model.md)
- [Politica de seguranca da conta](../07-security/account-security-policy.md)
