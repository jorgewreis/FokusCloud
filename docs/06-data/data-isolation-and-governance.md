# Isolamento e governança de dados

## Isolamento por empresa

O MySQL é acessado somente pela aplicação. Depois da seleção de empresa, a
empresa ativa fica em sessão assinada no servidor. A API obtém esse contexto,
valida vínculo ativo e perfil, e aplica `company_id` em toda leitura ou
gravação empresarial.

O cliente não escolhe livremente `company_id`. Quando uma rota expuser um ID de
empresa, a API deve compará-lo à empresa ativa antes da consulta. Tabelas
empresariais usam `company_id NOT NULL`, índices compostos e FKs compostas para
impedir relações entre registros de empresas diferentes.

## Perfis e entrada de usuários

| Perfil | Escopo |
| --- | --- |
| Admin | Único por empresa; gerencia assinatura, usuários, perfis e transferências. |
| Gestor | Atua somente sobre dados operacionais liberados pelo produto. |
| Usuário | Cria e edita dados operacionais liberados pelo produto. |

Somente o admin alterna perfis entre gestor e usuário. Promoção para admin usa
o fluxo formal de transferência, com reautenticação, aceite e auditoria.

O admin cadastra novos usuários diretamente. O vínculo começa `pendente` e o
destinatário recebe link válido por 24 horas para criar a própria senha. Caso o
CPF já exista, o usuário deve aceitar o novo vínculo antes da ativação.

## Ciclo de vida e retenção

1. A exclusão é lógica, com autor, data e status registrados.
2. O admin restaura registros da própria empresa em até 30 dias.
3. Após 90 dias da exclusão, processo controlado remove fisicamente o dado,
   respeitando FKs restritivas e dependências auditadas.
4. O evento de auditoria mascarado permanece por mais 90 dias.
5. Encerramento de empresa ou conta global é solicitado pelo interessado e
   validado pelo suporte antes de iniciar a retenção.

A rotina diária elimina tokens e convites expirados após 90 dias e vínculos
removidos após 90 dias. Eventos de auditoria são criados com expiração de 180
dias, cobrindo os 90 dias adicionais após o período de restauração.

Remover uma pessoa de uma empresa remove apenas o vínculo empresarial. A conta
global e seus vínculos com outras empresas permanecem inalterados.

## Auditoria e suporte

Criações, edições, exclusões e restaurações geram `AUD` com empresa, ator,
entidade, ID, operação, horário, valores anterior e novo em formato
estruturado. Senhas, hashes e tokens nunca entram na auditoria; CPF e e-mail
são mascarados quando registrados.

A equipe Fokus não possui acesso global permanente. Um acesso `SUP` exige
justificativa, empresa alvo e validade delimitada. Toda operação feita nesse
contexto é auditada.

## Segurança da conta

- CPF e e-mail são normalizados e únicos na conta global.
- A senha possui no mínimo 12 caracteres e bloqueia senhas comuns ou vazadas.
- Cinco falhas de login em 15 minutos bloqueiam a conta por 30 minutos e
  bloqueiam a conta por 30 minutos; no próximo login válido o bloqueio é
  removido e os contadores são reiniciados.
- Confirmação de e-mail, recuperação, criação de senha e aceite de vínculo usam
  links temporários válidos por 24 horas.
- Conta global: `pendente`, `ativa`, `bloqueada` ou `desativada`.
