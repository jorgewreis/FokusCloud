# Requisitos de seguranca

Este arquivo segue uma abordagem inspirada em praticas de seguranca no ciclo de desenvolvimento, como OWASP SAMM.

| Codigo | Requisito | Status |
| --- | --- | --- |
| RS-001 | Toda rota sensivel deve exigir autenticacao. | Em evolucao |
| RS-002 | Dados de empresa devem ser filtrados por empresa ativa e permissao. | Em evolucao |
| RS-003 | Acoes administrativas devem ser auditaveis. | Em evolucao |
| RS-004 | Erros nao devem expor dados sensiveis. | A definir |
| RS-005 | Perfis de empresa nao devem autorizar acesso ao backoffice interno. | Em definicao |
| RS-006 | Alteracoes de vinculo e administracao devem gerar eventos de auditoria. | Em definicao |
| RS-007 | Rotas do Fokus Law devem validar empresa ativa, assinatura, unidade Law, vinculo Law, modulo contratado, permissao e sigilo antes de retornar dados. | Definido |
| RS-008 | Processos sigilosos do Fokus Law devem mascarar dados para usuarios sem autorizacao explicita. | Definido |
| RS-009 | Exportacoes do Fokus Law devem respeitar sigilo e gerar auditoria. | Definido |
| RS-010 | Sincronizacao Datajud nao deve bloquear operacao interna e deve registrar erros de forma sanitizada. | Definido |

## A complementar

Definir controles por modulo, matriz de permissoes e testes de seguranca.

Os controles do Fokus Law v1 estao detalhados em [Seguranca e permissoes do Fokus Law](fokus-law-security-and-permissions.md).
