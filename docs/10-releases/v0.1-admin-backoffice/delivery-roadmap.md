# Roadmap de entrega da release 0.1

## Sequencia de marcos

| Marco | Entrega | Dependencias | Saida obrigatoria |
| --- | --- | --- | --- |
| 1 | Pacote documental da release (`0.0.1`) | Documentacao existente de produto, requisitos, dados, seguranca, comercial e operacao | Pasta `docs/10-releases/v0.1-admin-backoffice` publicada, rastreada, validada, commitada e tagueada |
| 2 | Seguranca interna | `platform_admins`, guard `platform`, MFA, auditoria inicial | Login interno, bloqueio rigido e perfis internos testados |
| 3 | Catalogo administrativo | Produtos, planos, modulos, precos, composicao e publicacao | Planos geridos no Backoffice e catalogo publico sem dados duplicados |
| 4 | Vouchers/cupons | Catalogo publicado, regras comerciais e snapshots | Vouchers criados, pausados, arquivados e consumidos com snapshot |
| 5 | Empresas e assinaturas | Empresas, assinaturas, pagamentos e historico | Consulta comercial e acoes controladas sobre assinatura |
| 6 | Billing sandbox | Credenciais sandbox Mercado Pago, webhooks, pagamentos e recorrencia | Fluxo sandbox executavel e divergencias conciliaveis |
| 7 | Dashboard, alertas e auditoria | Eventos auditaveis, dados comerciais e financeiros | Painel operacional e filas de alerta para homologacao |
| 8 | Estabilizacao e release | Testes, homologacao e notas de release | Checklist assinado e release `0.1` registrada |

## Regras de execucao

- O Marco 1 e uma release documental propria: `v0.0.1`.
- Cada marco deve atualizar testes e documentacao afetada antes de ser marcado
  como concluido.
- A [matriz de rastreabilidade](traceability-matrix.md) deve ser atualizada
  quando qualquer marco mudar escopo, aceite ou teste.
- Nenhuma acao sensivel pode depender apenas de controle visual no frontend.
- Catalogo publico e checkout devem consumir dados publicados pelo backend.
- Mercado Pago sandbox deve ser validado com webhook assinado ou mecanismo
  equivalente de ambiente de testes.
- Pendencias podem seguir para versao posterior apenas se forem registradas em
  [decisoes abertas](open-decisions.md) como nao bloqueantes.

## Ordem recomendada

1. Completar migrations e modelos de seguranca, auditoria e billing.
2. Implementar autorizacao por perfil interno no backend.
3. Completar APIs de Backoffice antes de finalizar telas.
4. Ligar telas reais aos endpoints, removendo fallbacks de mock quando houver
   autenticacao.
5. Implementar Mercado Pago sandbox e conciliacao.
6. Fechar dashboard, alertas e auditoria.
7. Rodar testes automatizados e homologacao guiada.

## Marco 1 - entrega documental 0.0.1

O Marco 1 esta concluido quando os criterios de
[Definition of Done](definition-of-done.md) estiverem atendidos e o
[checklist de revisao documental](documentation-review-checklist.md) estiver
versionado.

## Marco 2 - seguranca interna 0.0.2

O Marco 2 esta concluido quando identidade interna, RBAC de plataforma, MFA,
bloqueio progressivo, convite, revogacao de sessoes e auditoria de seguranca
estiverem implementados, testados e documentados em
[Marco 2 - seguranca interna](milestone-2-security.md).

## Marco 3 - catalogo administrativo 0.0.3

O Marco 3 esta concluido quando produtos, funcionalidades, planos, precos,
composicoes e publicacao controlada estiverem implementados, testados e
documentados em [Marco 3 - catalogo administrativo](milestone-3-catalog.md).
O catalogo publico e o checkout devem consumir somente a ultima publicacao
versionada do backend.
## Marco 4 - vouchers/cupons e release 0.0.4

Além de criar, pausar, arquivar e consumir vouchers, este marco inclui commercial_credit, reserva temporária no checkout, confirmação por webhook, liberação em falha/abandono, snapshot completo, edição pré-resgate, exclusões condicionais de planos/funcionalidades/publicação e revisão dos formulários. A saída pública continua com contrato 0.0.3; a entrega é 0.0.4. O detalhamento está em [milestone-4-vouchers.md](milestone-4-vouchers.md).

## Marco 5 - empresas e assinaturas e release 0.0.5

O Marco 5 e entregue inicialmente como `v0.0.5-alpha.1`, com consulta
comercial de empresas, consulta detalhada de assinaturas, pagamentos locais,
itens e historico, alem de pausa, reativacao, cancelamento agendado, upgrade
pendente de pagamento, downgrade agendado e override restrito ao
superadministrador. A implementacao preserva snapshots, auditoria e o
contrato publico 0.0.3 do catalogo. Billing sandbox completo, conciliacao,
reembolsos e alertas financeiros permanecem no Marco 6/7. O detalhamento esta
em [milestone-5-companies-subscriptions.md](milestone-5-companies-subscriptions.md).

## Marco 6 — `v0.0.6-alpha.1`

A alpha adiciona a camada operacional de Billing sandbox: cliente Mercado Pago,
checkout idempotente, webhooks assinados, recorrência, tolerância de
inadimplência, pagamentos, conciliação manual e reembolsos controlados. A saída
depende de testes automatizados, deploy aprovado e homologação sandbox real;
emissão fiscal, chargeback e indicadores avançados permanecem posteriores.
