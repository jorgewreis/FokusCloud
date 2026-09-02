# Notas da release 0.1

## Status da 0.0.1

Concluida como release documental apos validacao, commit, tag anotada e push
ao remoto.

## Status da 0.1

Planejada.

## Status da 0.0.2

Concluida como entrega funcional do Marco 2. A validacao aprovada esta em
`https://github.com/jorgewreis/FokusCloud/actions/runs/33564168328`, com
migration/seeder SQLite, 36 testes, build e publicacao aprovados.

## Status da 0.0.3

Concluida como entrega funcional do Marco 3. O catalogo administrativo passa a
gerir produtos, funcionalidades, planos, composicoes, precos e publicacao; o
catalogo publico usa contrato versionado `0.0.3` a partir de snapshot publicado
no backend.

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

### 0.0.3 - catalogo administrativo

- Endpoints administrativos de catalogo para produtos, funcionalidades, planos
  e composicao.
- Publicacao imediata exclusiva do superadministrador, com motivo, auditoria e
  snapshot versionado.
- Contrato publico `0.0.3` consumido pelo frontend de assinatura.
- Remocao de fallback estatico de precos e composicoes no fluxo publico.

Evidencias locais de fechamento:

| Evidencia | Resultado |
| --- | --- |
| `php artisan migrate:fresh --seed --force` com SQLite em memoria | Concluido |
| `php artisan test` | Concluido, 41 testes |
| `npm run build` | Concluido |
| `git diff --check` | Concluido |

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
## 0.0.4 - Marco 4

Entrega de vouchers com reserva e snapshot, crédito comercial na primeira cobrança, edição governada, exclusões condicionais de catálogo/publicação e correções dos formulários. O catálogo público mantém o contrato 0.0.3.

## Status da pré-release `v0.0.4-alpha.1`

A implementação foi validada na branch `develop` com 57 testes automatizados e
271 asserções aprovados. O build frontend e `git diff --check` também foram
aprovados. A homologação manual em navegador real permanece pendente, assim
como a integração de `develop` com `origin/main` e a criação da tag
`v0.0.4-alpha.1`.
