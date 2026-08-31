# Modelo de ameacas

Este arquivo deve registrar riscos relevantes e controles planejados.

## Modelo

| Risco | Area afetada | Impacto | Controle previsto |
| --- | --- | --- | --- |
| Acesso indevido entre empresas | Multiempresa | Alto | Filtro por empresa ativa, policies e testes de isolamento. |
| Alteracao indevida de planos | Comercial | Alto | Backoffice protegido, auditoria e permissoes administrativas. |
| Exposicao de dados pessoais | Plataforma | Alto | Validacao de acesso, logs seguros e minimizacao de dados. |
| Acesso indevido a processo sigiloso | Fokus Law | Critico | Autorizacao explicita por processo, mascaramento em listas e heranca de sigilo nas entidades filhas. |
| Acesso direto por URL/API a dados de outra unidade | Fokus Law | Alto | Validacao obrigatoria de `company_id`, `law_unit_id`, vinculo Law ativo e permissao no backend. |
| Vazamento por exportacao | Fokus Law | Alto | Exportacao apenas de dados visiveis ao usuario, respeito ao sigilo e auditoria obrigatoria. |
| Payload bruto Datajud em logs | Fokus Law | Alto | Logs sanitizados, sem payload bruto, com retencao tecnica de 7 dias. |
| Falha Datajud bloqueando operacao cartoraria | Fokus Law | Medio | Tentativas limitadas, registro sanitizado de erro e continuidade da operacao interna. |

## A complementar

Adicionar riscos por modulo antes da implementacao.
