# Modelo de dados de Audiencias Law

O nucleo de Audiencias usa `law_hearings`, seus participantes, historico de
status, alertas, acessos externos e eventos externos. Todas as tabelas possuem
`company_id` e devem ser filtradas pela empresa e unidade ativa.

`law_hearings` guarda o registro operacional. `law_hearing_participants` usa
contatos reutilizaveis e snapshot minimo do nome. `law_hearing_status_history`
preserva a trilha de status. `law_hearing_external_accesses` armazena apenas o
hash do token, expiracao, revogacao e contador de acessos.

O portal externo deve retornar somente dados explicitamente autorizados e nunca
expor processo integral, documentos, contatos nao autorizados ou anotacoes.
