# Modelo de dados da conta global

## Convencoes fisicas

- Chaves primarias usam `CHAR(30) CHARACTER SET ascii COLLATE ascii_bin`.
- O valor combina prefixo de entidade e ULID imutavel, como `USR-01J...`.
- Tabelas, colunas, indices e constraints usam ingles e `snake_case`.
- Tabelas usam nomes no plural.
- Datas usam `DATETIME(6)` em UTC.
- Tabelas principais possuem metadados de criacao, alteracao, versao e,
  quando aplicavel, exclusao logica.
- Estados usam texto tecnico com `CHECK`, e nao texto livre.
- Relacoes empresariais usam `company_id` e chaves estrangeiras compostas
  quando o isolamento depender da relacao pai.
- Exclusao fisica e excepcional e depende da politica de retencao.
- Textos pessoais usam `utf8mb4`; identificadores tecnicos usam `ascii_bin`.
- E-mail e CPF sao armazenados em forma normalizada e sem duplicacao.

## `users`

Representa a identidade global da pessoa.

| Campo | Regra |
| --- | --- |
| `id` | ULID `USR`, chave primaria imutavel. |
| `legal_name` | Nome civil obrigatorio; alteracao controlada pelo backoffice. |
| `preferred_name` | Nome de preferencia opcional; alteravel pelo usuario. |
| `cpf` | 11 digitos, normalizado, valido e unico. |
| `email` | E-mail principal normalizado e unico. |
| `email_verified_at` | Nulo enquanto o e-mail nao for confirmado. |
| `status` | `pending`, `active`, `suspended`, `blocked` ou `closed`. |
| `created_at`, `updated_at` | UTC com microssegundos. |
| `version` | Controle de concorrencia otimista. |

`legal_name` e `preferred_name` usam `utf8mb4` e `VARCHAR(255)`. O primeiro e
obrigatorio; o segundo e opcional. O e-mail usa `utf8mb4`, e o CPF usa somente
os 11 digitos normalizados.

Telefone, endereco, foto e preferencias nao ficam na identidade minima.

## `user_credentials`

Mantem credenciais independentes da identidade pessoal. A primeira credencial
sera senha local, com hash Argon2id. A tabela deve suportar provedores futuros,
mas cada provedor so pode ser vinculado uma vez por conta. A ultima credencial
ativa nao pode ser removida sem outra credencial ativa.

Historico de hashes e mantido somente na quantidade e periodo necessarios para
impedir reutilizacao recente.

## `identity_verifications`

Registra solicitacoes de validacao do nome civil e futuras validacoes de
identidade. Usa os estados `pending`, `approved`, `rejected`, `expired` e
`cancelled`. O usuario pode solicitar; somente o backoffice pode concluir.
Documentos nao sao armazenados inicialmente.

## `user_preferences`

Mantem idioma, fuso horario e preferencias de notificacao por evento e canal.
Alertas de seguranca sao ativados por padrao. Alertas criticos definidos pela
politica da plataforma nao podem ser desativados pelo usuario.

## `legal_acceptances`

Registra usuario, documento, versao, data UTC, IP e contexto. Termos de Uso e
Politica de Privacidade vigentes devem ser aceitos antes do acesso ao portal,
apos confirmacao do e-mail. Novo aceite e exigido em alteracoes materiais ou
novo documento obrigatorio.

## `user_sessions` e `mobile_refresh_tokens`

Sessoes web sao gerenciadas pelo servidor. Aplicativos moveis usam JWT curto e
refresh tokens rotativos vinculados ao dispositivo. O usuario pode visualizar
e revogar sessoes individualmente ou em conjunto. Recuperacao ou alteracao de
senha invalida todas as sessoes e refresh tokens.

O usuario pode encerrar a propria conta somente depois de deixar empresas e
transferir administracao. Conta `blocked` pode ser desbloqueada por prazo
automatico ou revisao do backoffice. Ao reativar, cada empresa aprova o
vinculo e escolhe novamente as unidades autorizadas.

## Integridade e privacidade

CPF e e-mail nao devem ser exibidos integralmente por padrao. Valores antigos
de credenciais nao sao mantidos indefinidamente. Alteracoes de CPF exigem
backoffice, validacao documental e auditoria. Dados temporarios, como tokens e
sessoes expiradas, possuem rotina propria de retencao.

## Referencias

- [Ciclo de vida da conta global](../05-requirements/user-account-lifecycle.md)
- [Politica de seguranca da conta](../07-security/account-security-policy.md)
- [Modelo relacional](relational-model.md)
