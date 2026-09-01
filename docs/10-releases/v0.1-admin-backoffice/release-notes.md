# Notas da release 0.1

## Status da 0.0.1

Concluida como release documental apos validacao, commit, tag anotada e push
ao remoto.

## Status da 0.1

Planejada.

## Status da 0.0.2

Implementacao consolidada para nova validacao do Marco 2. O fechamento exige
commit, atualizacao da tag anotada, push e GitHub Actions aprovado; a tag nao
deve ser considerada evidencia final antes dessa execucao.

## Entrega prevista

A release `0.1` entrega o Backoffice Admin funcional em ambiente de
desenvolvimento/homologacao, com operacao comercial e financeira administravel
pela equipe interna da Fokus Cloud.

## Incluido na release

### 0.0.1 - pacote documental

- Pasta de release `docs/10-releases/v0.1-admin-backoffice`.
- Roadmap dos oito marcos da versao `0.1`.
- Checklist por modulo.
- Analise de lacunas tecnicas.
- Criterios de aceite.
- Plano de testes.
- Roteiro de homologacao.
- Matriz de rastreabilidade.
- Definition of Done.
- Checklist de revisao documental.
- Decisoes abertas classificadas.

### 0.1 - entrega funcional prevista

- Acesso interno separado com MFA.
- Perfis internos `superadministrador` e `administrador_comercial`.
- Painel geral do Backoffice.
- Gestao administrativa de empresas.
- Gestao de planos, catalogo e publicacao.
- Gestao de assinaturas e historico comercial.
- Gestao de vouchers/cupons.
- Auditoria de acoes sensiveis.
- Alertas operacionais.
- Mercado Pago em sandbox real.
- Conciliacao manual de divergencias financeiras.

### 0.0.2 - seguranca interna

- RBAC exclusivo de plataforma com dois papeis internos.
- Pagina propria de acesso interno, MFA por e-mail e ativacao por convite.
- Bloqueio progressivo por conta, IP e dispositivo.
- Gestao de administradores internos com revogacao de sessoes.
- Auditoria de seguranca mascarada com retencao de 180 dias.

## Fora da release

- Emissao fiscal.
- Suporte cadastral amplo a dados pessoais de clientes.
- Alteracao de CPF, CNPJ, razao social, nome civil, e-mail ou senha do cliente
  pelo Backoffice.
- Acesso operacional aos dados internos do Fokus Law ou Fokus Lead.
- Publicacao agendada de catalogo.
- Aprovacao em duas pessoas.
- Relatorios financeiros avancados.

## Evidencias de fechamento

Evidencias da release documental `0.0.1`:

| Evidencia | Resultado |
| --- | --- |
| `git diff --check` | Concluido |
| Revisao de links internos | Concluido |
| Checklist documental | Concluido |
| Commit documental | Concluido |
| Tag anotada `v0.0.1` | Concluido |

Evidencias da release funcional `0.1`, a preencher no encerramento da
homologacao:

| Evidencia | Resultado |
| --- | --- |
| Testes automatizados | Pendente |
| Build frontend | Pendente |
| Homologacao guiada | Pendente |
| Falhas bloqueantes | Pendente |
| Decisoes nao bloqueantes | Pendente |

## Observacoes

Este documento deve ser atualizado quando a implementacao funcional da `0.1`
for concluida, incluindo commit, ambiente homologado, data de fechamento e
restricoes conhecidas.
