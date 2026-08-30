# Matriz de planos e funcionalidades

## Objetivo

Esta matriz organiza os 11 planos comerciais e suas funcionalidades por contexto de uso. Ela combina modulos existentes com modulos propostos e deve ser aprovada antes de alterar migrations, seeders ou registros no banco.

O catalogo deve preferir um modulo tecnico unico com variantes de contexto, regras, permissoes e limites. Uma variante comercial nao significa automaticamente um novo modulo no banco.

Legenda:

- `X`: incluido no plano sugerido;
- vazio: nao incluido por padrao, mas pode ser contratado se estiver disponivel;
- `Existente`: o modulo-base ja existe no catalogo tecnico atual;
- `Novo`: o modulo-base precisa ser cadastrado ou implementado.

Quando um modulo existente aparece com uma variante de contexto, isso significa que a base tecnica pode ser reutilizada, mas suas regras, campos, permissoes e limites ainda precisam ser configurados para aquele modelo.

## Fokus Cloud Law

### Modulos processuais e de relacionamento

| Modulo tecnico | Variante/contexto | Situacao | Advocacia | Cartorio Criminal | Cartorio Civel | Gestao de Audiencias | Gestao de Expedientes |
| --- | --- | --- | ---: | ---: | ---: | ---: | ---: |
| `processos` | Processos e Movimentacoes - Advocacia | Existente | X |  |  |  |  |
| `processos` | Processos e Movimentacoes - Cartorio Criminal | Existente |  | X |  |  |  |
| `processos` | Processos e Movimentacoes - Cartorio Civel | Existente |  |  | X |  |  |
| `processos` | Processos e Movimentacoes - Audiencias | Existente |  |  |  | X |  |
| `processos` | Processos e Movimentacoes - Expedientes | Existente |  |  |  |  | X |
| `partes` | Partes Processuais - Cartorios | Existente |  | X | X | X |  |
| `partes` | Clientes e Partes - Advocacia | Existente | X |  |  |  |  |

### Modulos de expedientes e documentos

| Modulo tecnico | Variante/contexto | Situacao | Advocacia | Cartorio Criminal | Cartorio Civel | Gestao de Audiencias | Gestao de Expedientes |
| --- | --- | --- | ---: | ---: | ---: | ---: | ---: |
| `oficios` | Oficios - Setor Cartorio | Existente |  | X | X |  | X |
| `oficios` | Oficios - Setor Gabinete | Existente |  | X | X |  | X |
| `cartas-exp` | Cartas Expedidas - Processuais e Administrativas | Existente |  | X | X |  | X |
| `cartas-rec` | Cartas Recebidas - Processuais e Administrativas | Existente |  | X | X |  | X |
| `editais` | Editais Criminais | Existente |  | X |  |  |  |
| `editais` | Editais Civeis | Existente |  |  | X |  |  |
| `guias` | Guias de Execucao - Varas Criminais Comuns | Existente |  | X |  |  |  |
| `guias` | Guias de Execucao - Varas de Execucao Penal | Existente |  | X |  |  |  |
| `custas` | Custas e Recolhimentos Civeis | Novo |  |  | X |  |  |
| `documentos` | Documentos e Modelos | Novo | X |  |  |  | X |
| `expedientes` | Central de Expedientes | Novo |  |  |  |  | X |

O plano Advocacia nao inclui expedicao ou recebimento institucional. Portanto,
`oficios`, `cartas-exp` e `cartas-rec` nao fazem parte da oferta sugerida de
Advocacia e somente podem ser considerados em uma composicao personalizada se
houver uma necessidade especifica validada.

### Instancias de expedicao por setor

Os modulos de expedicao nao devem ser tratados como uma unica fila por unidade.
Uma mesma unidade cartoraria pode possuir varias instancias do modulo `oficios`,
uma por setor ou origem operacional, por exemplo:

- Cartorio;
- Gabinete;
- outros setores cadastrados pela unidade.

Cada instancia deve possuir, no minimo, identificacao do setor, responsaveis,
sequencia de numeracao, prefixo ou serie, regras de permissao, fluxo de
aprovacao, controles e historico proprios. A matriz indica a disponibilidade do
recurso; a quantidade de instancias deve ser configuravel conforme a unidade.

### Modulos de prazos, agenda e tarefas

| Modulo tecnico | Variante/contexto | Situacao | Advocacia | Cartorio Criminal | Cartorio Civel | Gestao de Audiencias | Gestao de Expedientes |
| --- | --- | --- | ---: | ---: | ---: | ---: | ---: |
| `prazos` | Prazos e Intimacoes - Advocacia | Novo | X |  |  |  |  |
| `prazos` | Prazos Processuais - Cartorio Civel | Novo |  |  | X |  |  |
| `prazos` | Prazos de Expedientes | Novo |  |  |  |  | X |
| `agenda` | Agenda e Compromissos - Advocacia | Novo | X |  |  |  |  |
| `agenda` | Controle Interno de Audiencias | Novo |  | X | X | X |  |
| `agenda` | Acesso Externo a Audiencias | Novo |  |  |  | X |  |
| `agenda` | Agendamento de Audiencias - Advocacia | Novo | X |  |  |  |  |
| `tarefas_fluxos` | Tarefas e Fluxos - Advocacia | Novo | X |  |  |  |  |
| `tarefas_fluxos` | Tarefas e Fluxos - Expedientes | Novo |  |  |  |  | X |

### Modulos especificos de Advocacia

| Modulo tecnico | Variante/contexto | Situacao | Advocacia | Cartorio Criminal | Cartorio Civel | Gestao de Audiencias | Gestao de Expedientes |
| --- | --- | --- | ---: | ---: | ---: | ---: | ---: |
| `honorarios` | Controle de Honorarios | Novo | X |  |  |  |  |
| `financeiro` | Gerenciamento Financeiro | Novo | X |  |  |  |  |
| `assinatura` | Assinatura Digital | Novo | X |  |  |  | X |

### Modulos especificos do fluxo criminal

| Modulo tecnico | Variante/contexto | Situacao | Advocacia | Cartorio Criminal | Cartorio Civel | Gestao de Audiencias | Gestao de Expedientes |
| --- | --- | --- | ---: | ---: | ---: | ---: | ---: |
| `presos` | Controle de Presos | Novo |  | X |  |  |  |
| `monitoramento` | Monitoramento Eletronico | Existente |  | X |  |  |  |
| `medidas` | Medidas Protetivas | Existente |  | X |  |  |  |
| `penas` | Penas Alternativas | Novo |  | X |  |  |  |

### Modulos transversais do Law

| Modulo tecnico | Variante/contexto | Situacao | Advocacia | Cartorio Criminal | Cartorio Civel | Gestao de Audiencias | Gestao de Expedientes |
| --- | --- | --- | ---: | ---: | ---: | ---: | ---: |
| `relatorios` | Relatorios Operacionais | Novo | X | X | X | X | X |
| `relatorios` | Relatorios Juridicos | Novo | X |  | X |  |  |
| `relatorios` | Relatorios Gerenciais | Novo |  |  |  |  |  |
| `notificacoes` | Notificacoes Internas e de Prazos | Novo | X | X | X | X | X |

## Fokus Cloud Lead One

A linha One evolui por capacidade crescente para corretores independentes.

| Modulo tecnico | Variante/contexto | Situacao | Essencial | Profissional | Avancado | Premium |
| --- | --- | --- | ---: | ---: | ---: | ---: |
| `pessoas` | Gestao de Pessoas - Corretor | Existente | X | X | X | X |
| `imoveis` | Gestao de Imoveis - Corretor | Existente | X | X | X | X |
| `empreendimentos` | Gestao de Empreendimentos - Corretor | Existente |  | X | X | X |
| `leads` | Gestao de Leads - Corretor | Novo |  | X | X | X |
| `funil` | Funil de Vendas - Corretor | Existente |  | X | X | X |
| `website` | Integracao com Website | Existente |  | X | X | X |
| `relatorios` | Relatorios de Transacoes | Existente |  |  | X | X |
| `whatsapp` | Integracao com WhatsApp | Existente |  |  |  | X |
| `notificacoes` | Notificacoes Comerciais | Novo | X | X | X | X |

## Fokus Cloud Lead Team

A linha Team deve possuir recursos de colaboracao e governanca de equipes, alem dos modulos comerciais compartilhados com o One.

| Modulo tecnico | Variante/contexto | Situacao | Team Essencial | Team Premium |
| --- | --- | --- | ---: | ---: |
| `pessoas` | Gestao de Pessoas - Equipe | Existente | X | X |
| `imoveis` | Gestao de Imoveis - Equipe | Existente | X | X |
| `empreendimentos` | Gestao de Empreendimentos - Equipe | Existente | X | X |
| `leads` | Gestao de Leads - Equipe | Novo |  | X |
| `funil` | Funil de Vendas - Equipe | Existente | X | X |
| `website` | Integracao com Website | Existente | X | X |
| `relatorios` | Relatorios de Transacoes | Existente |  | X |
| `whatsapp` | Integracao com WhatsApp | Existente |  | X |
| `portal_imoveis` | Portal de Imoveis | Novo |  | X |
| `equipes` | Gestao de Equipes | Novo | X | X |
| `colaboracao` | Colaboracao entre Corretores | Novo | X | X |
| `permissoes` | Permissoes por Funcao | Novo | X | X |
| `distribuicao_leads` | Distribuicao de Leads | Novo |  | X |
| `visao_gerencial` | Visao Gerencial | Novo |  | X |
| `filiais` | Gestao de Filiais | Novo |  | X |
| `relatorios_gerenciais` | Relatorios Gerenciais | Novo | X | X |
| `notificacoes` | Notificacoes de Equipe | Novo | X | X |

## Composicao personalizada

As funcionalidades abaixo podem ser contratadas individualmente ou combinadas, sem criar automaticamente um novo plano permanente:

- Gestao de Prazos e Intimacoes;
- Relatorios Gerenciais;
- Gestao de Processos e Movimentacoes;
- Gestao de Partes;
- Controle de Prazos;
- Tarefas e Fluxos de Trabalho;
- Relatorios;
- Notificacoes.

A composicao personalizada deve respeitar sistema, contexto, dependencias, incompatibilidades, limites e permissoes. O nome da oferta deve ser gerado como `Sistema - Personalizada`.

## Regras de modelagem

- `processos`, `partes`, `prazos`, `agenda`, `tarefas_fluxos`, `relatorios` e `notificacoes` devem ser modulos tecnicos reutilizaveis com variantes de contexto;
- `oficios` deve permitir varias instancias por unidade e setor, com numeracao e controles independentes;
- `cartas-exp` e `cartas-rec` devem permitir configuracao por unidade e setor quando o fluxo operacional exigir;
- `guias` deve possuir as variantes `guias_execucao_comum` e `guias_execucao_penal`, com campos, fluxos e regras proprios;
- `Gestao de Prazos e Intimacoes` e `Controle de Prazos` devem compartilhar o mesmo nucleo tecnico, salvo se houver regras de negocio realmente distintas;
- `Relatorios` e `Relatorios Gerenciais` devem compartilhar o mesmo nucleo e variar por tipos de relatorio e permissao;
- uma variante externa de Audiencias deve possuir permissao de consulta sem permitir alteracoes indevidas;
- regras de prazo de Editais devem ser configuraveis por contexto `criminal` ou `civel`;
- a variante de Partes para Advocacia deve priorizar clientes, enquanto a variante cartoraria deve priorizar partes processuais;
- limites ajustados pelo cliente devem ser escolhidos entre opcoes cadastradas e recalcular o preco no backend.

## Pendencias antes da implementacao

- aprovar ou ajustar a inclusao de cada linha marcada com `X`;
- confirmar se `Custas e Recolhimentos` sera exclusivo do Civel ou compartilhado com Advocacia;
- definir limites e opcoes de capacidade de todos os modulos;
- definir as regras de prazo criminal e civel;
- detalhar campos e permissoes do acesso externo a Audiencias;
- cadastrar as funcionalidades marcadas como `Novo` antes de criar os planos;
- atualizar seeders, migrations, API, checkout e catalogo publico somente apos a aprovacao final.
