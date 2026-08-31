# Backoffice Fokus Cloud

## Papel

O Backoffice Fokus Cloud e o centro de administracao interna da plataforma. Ele
existe para que a equipe interna da Fokus governe dados comerciais,
assinaturas, vouchers, auditoria e indicadores da operacao sem depender de
alteracoes manuais em seeders, frontend ou banco de dados.

O Backoffice pertence ao Fokus Cloud, pois administra capacidades comuns da
plataforma. Ele nao pertence ao Fokus Law ou ao Fokus Lead, embora gerencie
produtos, planos, funcionalidades, assinaturas e vouchers desses produtos.

## Objetivo da v1

A primeira versao deve permitir implementar a operacao comercial interna com
seguranca e rastreabilidade suficientes para substituir manutencoes tecnicas
recorrentes do catalogo.

O escopo inicial cobre:

- catalogo comercial;
- assinaturas;
- vouchers;
- dashboard operacional;
- indicadores financeiros;
- auditoria;
- gestao comercial de empresas e clientes.

## Fora do escopo da v1

A v1 nao deve incluir suporte cadastral ou pessoal amplo para clientes.

Ficam fora do escopo inicial:

- alteracao de CPF ou CNPJ de empresas ou usuarios;
- alteracao de nome civil, razao social ou dados pessoais do administrador;
- gestao de usuarios clientes, convites, vinculos e perfis do portal;
- reset assistido de senha de clientes;
- acesso operacional aos dados internos dos produtos Fokus Law ou Fokus Lead;
- configuracoes gerais da plataforma fora do dominio comercial;
- emissao fiscal, quando nao estiver vinculada ao fluxo de billing definido.

Quando uma acao exigir correcao documental, validacao de identidade ou suporte
direto a dados pessoais, ela deve ser documentada em fluxo proprio antes de
entrar no Backoffice.

## Acesso e identidade interna

O acesso ao Backoffice usa entrada propria em `/backoffice/acesso` e area
protegida em `/backoffice`.

A identidade interna deve usar `platform_admins`, e-mail, senha e MFA
obrigatorio por e-mail. Essa identidade e separada da conta global de cliente
em `users`.

As sessoes do Backoffice usam guard separado do portal do cliente. Uma sessao
de cliente nao autoriza rotas `/api/backoffice`, e uma conta interna nao deve
aparecer como usuario de empresa.

Contas internas devem ser bloqueadas de forma rigida apos tentativas invalidas
conforme politica de seguranca. O desbloqueio deve ser feito por
superadministrador e gerar auditoria.

## Perfis internos

| Perfil | Papel | Limite principal |
| --- | --- | --- |
| `superadministrador` | Governa o Backoffice, usuarios internos, seguranca, publicacao final, auditoria completa e excecoes comerciais. | Deve ser usado apenas por pessoas responsaveis pela operacao da plataforma. |
| `administrador_comercial` | Cria e edita catalogo, planos, funcionalidades, precos e vouchers. Consulta auditoria do dominio comercial permitido. | Nao gerencia usuarios internos, seguranca, publicacao final nem auditoria completa. |

Permissoes internas possuem escopo `platform` e nao podem ser concedidas por
perfis de empresa como `admin`, `gestor` ou `usuario`.

## Modulos da v1

| Modulo | Finalidade |
| --- | --- |
| Dashboard | Exibir situacao comercial, riscos operacionais e indicadores financeiros. |
| Catalogo comercial | Administrar produtos, planos, funcionalidades, precos, composicoes, status e publicacao. |
| Assinaturas | Consultar assinaturas, historico, snapshots, status, troca de plano e acoes controladas. |
| Vouchers | Administrar vouchers, regras de beneficio, validade, limites, status e uso. |
| Empresas e clientes | Consultar e gerir informacoes comerciais da empresa sem alterar dados cadastrais ou pessoais. |
| Auditoria | Registrar e consultar eventos sensiveis do Backoffice conforme perfil interno. |
| Usuarios internos | Gerenciar contas e perfis internos do Backoffice, apenas por superadministrador. |

## Catalogo comercial

O Backoffice deve permitir criar, editar, revisar, publicar, pausar e arquivar:

- produtos;
- planos;
- funcionalidades;
- precos;
- composicoes de planos;
- vouchers.

A publicacao da v1 pode ocorrer imediatamente, desde que haja confirmacao
explicita e auditoria. Itens incompletos, invalidos, pausados, arquivados ou
nao publicados nao podem aparecer no catalogo publico nem no checkout.

O administrador comercial pode criar e editar dados comerciais. A publicacao
final, pausa e arquivamento de itens publicados ficam sob responsabilidade do
superadministrador.

## Assinaturas

O Backoffice deve permitir consultar assinaturas, pagamentos, historico e
snapshots comerciais.

Acoes controladas permitidas na v1:

- pausar assinatura;
- reativar assinatura;
- cancelar assinatura;
- trocar plano;
- consultar historico e snapshot.

Upgrade deve valer imediatamente, apos confirmacao de cobranca proporcional
quando aplicavel. Downgrade e cancelamento devem ficar agendados para o fim da
vigencia.

Alteracoes de assinatura devem recalcular valores pelo catalogo publicado. A
edicao manual de valor final, ciclo, limites ou datas comerciais e uma excecao
exclusiva do superadministrador e exige motivo, diff completo e auditoria.

## Vouchers

O Backoffice deve administrar vouchers dos seguintes tipos iniciais:

- `trial_free`: assinatura gratuita conforme duracao definida;
- `percentage`: desconto percentual;
- `fixed`: desconto em valor fixo;
- `commercial_credit`: credito comercial aplicado conforme regra do voucher.

Vouchers podem ser criados, editados, pausados, arquivados e consultados no
Backoffice. A operacao deve preservar regras de beneficio, validade, limites,
uso por empresa, lote de codigos e snapshot do resgate.

## Empresas e clientes

A v1 permite gestao comercial da empresa cliente, sem suporte cadastral ou
pessoal direto.

Podem ser tratados no Backoffice:

- plano contratado;
- ciclo comercial;
- valor final;
- limites contratados;
- voucher aplicado;
- datas comerciais;
- observacoes internas;
- status comercial da assinatura.

Nao podem ser alterados neste escopo:

- CPF ou CNPJ;
- razao social ou nome civil;
- e-mail pessoal ou profissional do usuario;
- senha ou credencial do cliente;
- usuarios, vinculos e perfis do portal.

## Dashboard

O painel inicial deve combinar tres visoes:

| Visao | Conteudo esperado |
| --- | --- |
| Operacao comercial | Pendencias do catalogo, assinaturas com acao necessaria, vouchers ativos e eventos comerciais recentes. |
| Governanca e risco | Acoes sensiveis, falhas de login interno, bloqueios, publicacoes e overrides. |
| Indicadores financeiros | Assinaturas ativas, recorrencias, cancelamentos, receita prevista e divergencias de pagamento. |

Indicadores financeiros devem combinar dados internos de assinaturas e
snapshots com dados do Mercado Pago.

As filas, alertas, notificacoes imediatas, rotinas operacionais e incidentes
criticos do Backoffice estao definidos em [Monitoramento, suporte e operacao
do Backoffice](../09-operations/backoffice-monitoring-and-support.md).

O modelo alvo de tabelas, status, relacionamentos, retencao e snapshots esta
definido em [Modelo de dados do Backoffice e Billing](../06-data/backoffice-and-billing-data-model.md).

## Billing e Mercado Pago

Mercado Pago e o fornecedor definido para pagamentos e recorrencia na v1.

As regras de checkout, recorrencia, inadimplencia, reembolso e conciliacao
estao definidas em [Billing e conciliacao com Mercado Pago](../08-commercial/billing-and-reconciliation.md).

Quando houver divergencia entre status interno e status retornado pelo Mercado
Pago, o gateway e a referencia prevalente para pagamento e recorrencia, mas o
ajuste interno exige alerta e revisao manual. Apenas superadministrador pode
aplicar correcao, registrando estado anterior, estado do gateway, decisao,
motivo, operador e auditoria.

## Auditoria

Toda acao sensivel do Backoffice deve gerar auditoria detalhada.

Cada evento deve registrar, no minimo:

- operador;
- data e hora;
- entidade afetada;
- acao executada;
- motivo, quando aplicavel;
- valores antes e depois;
- metadados minimos para investigacao.

Motivo e obrigatorio nas acoes que afetem cliente, cobranca ou disponibilidade
publica.

O superadministrador ve auditoria completa. O administrador comercial ve apenas
eventos dos itens comerciais que pode operar.

A retencao dos eventos de auditoria do Backoffice e de 180 dias. Depois desse
periodo, snapshots essenciais devem preservar assinatura, resgate de voucher,
plano, preco e condicao aplicada.

## Evolucao prevista

Depois da v1, o Backoffice pode evoluir para:

- suporte assistido a contas de clientes;
- correcao cadastral com validacao documental;
- emissao fiscal;
- filas de revisao e aprovacao em duas pessoas;
- agendamento de publicacao;
- relatorios financeiros avancados;
- integracoes adicionais de billing;
- monitoramento operacional por produto alem da rotina inicial do Backoffice.

Cada evolucao deve atualizar requisitos, seguranca, dados, operacao e regras
comerciais correspondentes.
