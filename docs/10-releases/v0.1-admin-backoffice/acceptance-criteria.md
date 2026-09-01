# Criterios de aceite da release 0.1

## Aceite funcional

- Backoffice acessivel por `/backoffice/acesso` com MFA por e-mail.
- Conta cliente nao acessa `/backoffice` nem `/api/backoffice`.
- Conta interna nao autentica no portal do cliente.
- Superadministrador gerencia usuarios internos, seguranca, publicacao,
  auditoria completa, override e conciliacao.
- Administrador comercial cria e edita dados comerciais permitidos, mas nao
  executa acoes exclusivas de superadministrador.
- Catalogo publico e checkout usam somente dados publicados, ativos, completos
  e comercializaveis do backend.
- Empresas podem ser consultadas e geridas comercialmente sem edicao de dados
  cadastrais ou pessoais.
- Assinaturas podem ser pausadas, reativadas, canceladas, alteradas e
  auditadas conforme regra de billing.
- Vouchers/cupons funcionam com validade, limite, tipo, elegibilidade e
  snapshot de resgate.
- Dashboard exibe indicadores, riscos e alertas com origem clara dos dados.

## Aceite financeiro

- Mercado Pago sandbox cria checkout/preapproval com sucesso.
- Falha do Mercado Pago nao persiste assinatura nem pagamento.
- Webhook valido atualiza pagamento e assinatura.
- Webhook invalido nao altera dados comerciais.
- Evento duplicado nao duplica pagamento, assinatura, voucher ou auditoria.
- Divergencia entre dados internos e Mercado Pago cria alerta.
- Correcao de divergencia exige revisao manual e superadministrador.

## Aceite de auditoria e seguranca

- Toda acao sensivel gera auditoria.
- Acoes que afetam cliente, cobranca ou disponibilidade publica exigem motivo.
- Auditoria registra valores antes/depois mascarados quando houver alteracao.
- Logs, auditoria e comentarios nao armazenam senhas, tokens, codigo MFA, CPF,
  CNPJ ou payload sensivel completo.
- Eventos de auditoria possuem retencao de 180 dias.
- Alertas e comentarios operacionais possuem retencao de 90 dias.

## Aceite tecnico

- Migrations novas rodam em banco limpo.
- Seeders criam dados minimos de catalogo e superadministrador de homologacao
  por fluxo seguro.
- Testes automatizados obrigatorios passam.
- Interfaces reais nao dependem de dados mockados quando autenticadas.
- Endpoints retornam erros controlados e mensagens operacionais claras.

## Aceite de homologacao

- O roteiro de [homologacao](homologation-script.md) deve ser executado em
  ambiente de homologacao.
- Cada falha deve ser classificada como bloqueante ou nao bloqueante.
- A release so pode ser marcada como pronta se nao houver falha bloqueante.

## Aceite da release documental 0.0.1

- A [matriz de rastreabilidade](traceability-matrix.md) cobre os oito marcos.
- A [Definition of Done](definition-of-done.md) separa o pronto documental da
  implementacao funcional dos marcos seguintes.
- O [checklist de revisao documental](documentation-review-checklist.md) esta
  preenchido.
- As notas da release registram a `0.0.1` como release documental.
- O commit documental e a tag anotada `v0.0.1` estao sincronizados no remoto.
