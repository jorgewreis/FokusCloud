# Fokus Law

## Papel

Fokus Law e o produto derivado do ecossistema Fokus Cloud voltado a rotinas
juridicas, cartorarias e de advocacia.

Na v1, o produto deve nascer como uma solucao cartoraria para organizar e
controlar o fluxo operacional de unidades judiciais, com foco inicial em
Cartorio Criminal e base reaproveitavel para Cartorio Civel.

O Fokus Law nao substitui o core da plataforma. Identidade, empresas,
assinaturas, catalogo comercial, vouchers, billing, auditoria de plataforma e
Backoffice pertencem ao Fokus Cloud. O Fokus Law adiciona regras, dados,
permissoes e fluxos especificos do dominio juridico.

## Objetivo da v1

O objetivo central da v1 e permitir que uma unidade cartoraria acompanhe sua
operacao diaria por meio de:

- processos como entidade central;
- expedientes e documentos vinculados aos processos;
- controle de oficios;
- controle de cartas expedidas;
- prazos e pendencias operacionais;
- partes processuais reutilizaveis na unidade;
- indicadores gerenciais da unidade.

A v1 deve privilegiar governanca operacional, rastreabilidade e reducao de
retrabalho. Quando houver conflito entre abrangencia funcional e clareza de
implementacao, o escopo cartorario definido neste documento prevalece.

## Publico principal

O publico principal da v1 sao cartorios e serventias judiciais.

O primeiro recorte funcional deve considerar Cartorio Criminal, mas os conceitos
base devem evitar acoplamento desnecessario para permitir evolucao futura para
Cartorio Civel.

## Escopo v1

Faz parte do escopo da v1:

- cadastro e acompanhamento operacional de processos;
- vinculacao obrigatoria de expedientes a processos quando o expediente for
  processual;
- cadastro de partes vinculadas a processos e reutilizaveis entre processos da
  mesma unidade;
- controle de oficios com numeracao por unidade e setor;
- controle de cartas expedidas sem numeracao propria interna;
- registro posterior opcional do numero atribuido pela comarca de destino em
  cartas expedidas;
- tratamento de cartas recebidas como classe de processo;
- prazos e pendencias vinculados a processos e expedientes;
- perfis internos por unidade;
- acesso multiunidade por vinculo explicito;
- sigilo processual como regra transversal;
- auditoria de acoes sensiveis e operacionais;
- consulta e atualizacao de dados processuais basicos via Datajud;
- indicadores operacionais por unidade;
- habilitacao de modulos e limites pelo catalogo comercial publicado e pela
  assinatura ativa.

## Fora do escopo v1

Nao faz parte da v1:

- modulos de Advocacia;
- financeiro e honorarios advocaticios;
- gestao completa de audiencias;
- pauta completa, salas, links e agenda de audiencias;
- geracao automatica avancada de documentos a partir de modelos;
- integracoes automaticas alem do Datajud;
- substituicao integral de sistemas judiciais oficiais;
- espelhamento completo de PJe, e-SAJ ou outros sistemas processuais;
- cadastro pessoal amplo fora da finalidade processual/operacional;
- edicao de dados comerciais, assinatura, plano ou cobranca dentro do produto.

Itens fora de escopo podem ser documentados como evolucao futura, mas nao devem
ser tratados como requisito da v1.

## Modulos internos da v1

### Processos

Processos sao a entidade central obrigatoria do Fokus Law v1. Expedientes,
partes, prazos, pendencias e indicadores devem se conectar ao processo sempre
que houver relacao processual.

Dados operacionais minimos:

- numero do processo;
- classe;
- assunto;
- vara ou unidade;
- situacao operacional;
- prioridade;
- indicacao de sigilo;
- datas relevantes;
- observacoes internas.

Cartas recebidas devem ser tratadas como uma classe de processo. Elas nao devem
ser modeladas como expediente separado nem como modulo autonomo de cartas
recebidas.

### Partes

Partes processuais devem ser vinculadas aos processos e reutilizaveis entre
processos da mesma unidade.

O cadastro de partes deve conter dados minimos para identificacao operacional e
papel processual. O objetivo nao e criar um cadastro pessoal amplo, mas permitir
identificacao suficiente para execucao cartoraria, filtros, documentos,
indicadores e historico.

### Oficios

Oficios devem possuir controle proprio por unidade e setor.

Cada oficio deve registrar, no minimo:

- unidade;
- setor;
- numero ou sequencia propria;
- destinatario;
- assunto;
- processo vinculado;
- status;
- responsavel;
- datas relevantes;
- historico.

A numeracao de oficios deve ser independente por setor quando a unidade possuir
mais de uma origem operacional, como Cartorio e Gabinete.

### Cartas expedidas

Cartas expedidas sao expedientes vinculados a um processo da unidade.

Elas nao devem possuir numeracao propria ou controlada internamente pelo Fokus
Law. O cadastro deve exigir o processo de origem da unidade e deve permitir,
posteriormente, o preenchimento opcional do numero atribuido pela comarca ou
orgao de destino.

Cada carta expedida deve registrar, no minimo:

- processo vinculado;
- destino;
- tipo;
- status;
- data de envio;
- data de retorno, quando houver;
- numero atribuido no destino, opcional;
- responsavel;
- historico.

### Prazos e pendencias

Prazos e pendencias devem poder ser vinculados a processos e expedientes.

Cada prazo ou pendencia deve registrar:

- entidade relacionada;
- responsavel;
- data limite, quando aplicavel;
- status;
- prioridade;
- alertas;
- historico.

O controle deve permitir identificar prazos vencidos, prazos a vencer,
pendencias abertas, responsaveis e gargalos operacionais.

### Indicadores gerenciais

O painel gerencial da v1 deve apresentar indicadores operacionais por unidade.

Indicadores minimos:

- processos ativos;
- expedientes pendentes;
- prazos vencidos;
- prazos a vencer;
- produtividade por usuario;
- produtividade por setor;
- gargalos por tipo de expediente, status ou responsavel.

Indicadores detalhados devem respeitar sigilo processual e permissoes do usuario
logado.

## Perfis e permissoes

Os perfis internos da unidade na v1 sao:

- administrador da unidade;
- chefe ou escrivao;
- servidor operacional;
- visualizador.

Administrador da unidade e chefe/escrivao podem configurar usuarios e permissoes
dentro da unidade.

Cada usuario deve acessar apenas as unidades as quais foi explicitamente
vinculado. O mesmo usuario pode possuir perfis diferentes em unidades
diferentes.

Permissoes comerciais, assinatura, plano, voucher, billing e Backoffice nao
devem ser gerenciados dentro do Fokus Law.

## Sigilo processual

Sigilo processual deve ser regra transversal da v1.

Processos sigilosos devem ser visiveis apenas para usuarios autorizados na
unidade. A restricao deve se aplicar tambem a:

- partes;
- expedientes;
- prazos;
- pendencias;
- buscas;
- listas;
- indicadores detalhados;
- exportacoes, quando existirem.

Indicadores agregados podem considerar processos sigilosos apenas quando nao
revelarem dados identificaveis indevidos.

## Auditoria

O Fokus Law deve auditar acoes sensiveis e operacionais.

Eventos auditaveis minimos:

- criacao, edicao, cancelamento e inativacao de processo;
- alteracao de sigilo;
- criacao, edicao, cancelamento e mudanca de status de oficio;
- criacao, edicao, cancelamento e mudanca de status de carta expedida;
- criacao, edicao, cancelamento e mudanca de status de prazo ou pendencia;
- vinculacao e desvinculacao de partes;
- alteracao de permissoes na unidade;
- acesso a processo sigiloso;
- exportacao de dados, quando existir.

Cada evento deve registrar usuario, data, unidade, entidade, acao, valores antes
e depois quando aplicavel, motivo quando a acao afetar historico operacional,
sigilo, prazo ou disponibilidade do registro.

## Integracao com Datajud

O Datajud deve ser a integracao inicial da v1.

Seu papel e permitir consulta e atualizacao de dados processuais basicos, sem
substituir o cadastro operacional interno do Fokus Law.

Regras:

- dados internos prevalecem para campos operacionais;
- Datajud prevalece apenas para metadados processuais publicos sincronizados;
- campos operacionais nao devem ser sobrescritos automaticamente pelo Datajud;
- divergencias relevantes devem ser registradas;
- falhas de consulta devem ser tratadas sem bloquear o uso interno do processo;
- identificadores externos devem ser preservados para sincronizacao futura.

Exemplos de campos operacionais internos:

- responsavel;
- status operacional;
- prazos internos;
- pendencias;
- observacoes internas;
- expedientes;
- permissoes e sigilo interno.

Exemplos de metadados sincronizaveis:

- numero CNJ;
- classe;
- assunto;
- orgao julgador;
- tribunal;
- movimentacoes publicas disponiveis;
- data de ultima atualizacao externa.

## Relacao com catalogo e assinatura

Modulos e limites do Fokus Law devem ser habilitados pelo catalogo comercial
publicado e pela assinatura ativa da empresa ou unidade.

O produto nao deve decidir internamente preco, plano, voucher, desconto ou
condicao comercial. Essas regras pertencem ao catalogo, billing e Backoffice do
Fokus Cloud.

O Fokus Law deve apenas consultar o direito de uso concedido pela assinatura
ativa, respeitando:

- produto contratado;
- plano;
- modulos habilitados;
- limites contratados;
- vigencia;
- status da assinatura.

## Evolucao

Evolucoes esperadas apos a v1:

- Cartorio Civel completo;
- gestao completa de audiencias;
- editais;
- guias de execucao;
- documentos e modelos;
- automacoes de expedientes;
- integracoes adicionais com sistemas judiciais;
- modulos de Advocacia;
- prazos e intimacoes para Advocacia;
- agenda, tarefas, clientes, honorarios e financeiro advocaticio.

Essas evolucoes devem respeitar a regra de pertencimento da plataforma: recursos
comuns ficam no Fokus Cloud; regras juridicas ficam no Fokus Law.

## Criterios de aceite do produto

- O Fokus Law v1 esta descrito como produto juridico derivado, nao como core da
  plataforma.
- A v1 esta limitada a cartorios/serventias, com foco inicial em Cartorio
  Criminal e base reutilizavel para Civel.
- Processos sao definidos como entidade central.
- Oficios, cartas expedidas, prazos, pendencias e partes possuem papel claro.
- Cartas recebidas sao tratadas como classe de processo, nao como expediente.
- Audiencias, Advocacia, financeiro/honorarios e geracao avancada de documentos
  ficam fora da v1.
- Sigilo, auditoria, perfis e acesso multiunidade estao definidos.
- Datajud tem papel claro e nao substitui os dados operacionais internos.
- O documento nao contradiz catalogo, assinaturas, Backoffice ou arquitetura da
  plataforma.
