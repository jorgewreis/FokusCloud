# Catalogo comercial

## Objetivo

Centralizar os dados utilizados para comercializar os produtos FokusCloud. O catalogo deve ser administravel pelo backoffice e consumido pelo catalogo publico, checkout, assinaturas e vouchers.

## Hierarquia

```text
Sistema (product)
  Plano (plan)
    Funcionalidades (modules)
```

Uma funcionalidade pode participar de varios planos do mesmo sistema. A relacao e registrada em `plan_modules`.

## Sistemas

Cada sistema deve possuir:

- codigo interno estavel;
- nome exibido;
- descricao tecnica e conteudo comercial separados;
- identificacao visual;
- status;
- ordem de exibicao;
- datas de criacao e alteracao.

Os sistemas comerciais oficiais sao:

- `law`: Fokus Cloud Law;
- `lead`: Fokus Cloud Lead.

O produto `lead` possui duas linhas comerciais identificadas nos planos:

- `one`: Fokus Cloud Lead One, para corretores independentes;
- `team`: Fokus Cloud Lead Team, para imobiliarias e grupos de corretores.

O nome exibido pode evoluir sem alterar o codigo interno.

## Planos

Cada plano deve possuir codigo, nome, descricao tecnica, conteudo comercial, sistema vinculado, funcionalidades, status, ordem de exibicao e destaque comercial opcional.

Regras:

- pertence a um unico sistema;
- deve possuir ao menos uma funcionalidade para ser publicado;
- somente pode incluir funcionalidades do proprio sistema;
- pode ser reutilizado por vouchers e assinaturas;
- seu preco-base e derivado da composicao de funcionalidades.

Planos comerciais aprovados:

| Sistema | Planos |
| --- | --- |
| Fokus Cloud Law | Advocacia, Cartorio Criminal, Cartorio Civel, Gestao de Audiencias, Gestao de Expedientes |
| Fokus Cloud Lead One | Essencial, Profissional, Avancado, Premium |
| Fokus Cloud Lead Team | Essencial, Premium |

O nome completo exibido no catalogo segue o formato `Sistema - Plano`. Exemplos:

- `Fokus Cloud Law - Advocacia`;
- `Fokus Cloud Lead One - Essencial`;
- `Fokus Cloud Lead Team - Premium`.

Os codigos tecnicos devem ser slugs estaveis, independentes do texto exibido. Exemplos: `law-advocacia`, `lead-one-essencial` e `lead-team-premium`.

## Funcionalidades

Cada funcionalidade deve possuir codigo, nome, descricao tecnica, conteudo comercial, sistema vinculado, preco mensal, status, ordem de exibicao e datas de criacao e alteracao.

Tambem podera possuir configuracao de capacidade:

- unidade de medicao;
- limite padrao;
- opcoes de capacidade;
- incremento;
- custo adicional, quando aplicavel;
- dependencias e incompatibilidades.

Uma funcionalidade pode ser marcada como disponivel para contratacao avulsa.

O catalogo do Fokus Cloud Law deve contemplar, conforme a matriz de ofertas:

- funcionalidades operacionais existentes, como processos, partes, oficios, cartas, editais, guias e audiencias;
- funcionalidades de Advocacia, como prazos e intimacoes, agenda, tarefas, clientes, honorarios e financeiro;
- funcionalidades de Cartorio Civel, como custas, recolhimentos e controle de prazos;
- funcionalidades de Gestao de Expedientes, como tarefas, fluxos de trabalho e controle de prazos.

Tambem devem ser suportadas ofertas personalizadas com modulos de prazos e intimacoes, relatorios gerenciais, processos, partes, prazos, tarefas, relatorios e notificacoes, individualmente ou em conjunto.

O plano de Advocacia nao inclui expedicao ou recebimento institucional de
oficios e cartas. Esses modulos sao direcionados aos modelos cartorarios e de
expedientes, podendo ser considerados apenas em uma oferta personalizada
validada.

Em unidades cartorarias, `oficios` pode possuir varias instancias por setor,
como Cartorio e Gabinete. Cada instancia deve manter numeracao, serie,
responsaveis, permissoes, fluxo e controles independentes.

Na v1 cartoraria do Fokus Law, `cartas-exp` representa cartas expedidas como
expedientes vinculados a processo e sem numeracao propria interna. O numero
atribuido pela comarca ou orgao de destino e opcional e pode ser informado
posteriormente. Cartas recebidas nao devem ser tratadas como expediente separado
na v1, pois correspondem a uma classe de processo dentro do modulo `processos`.

`Guias de Execucao` possui duas variantes de negocio: Varas Criminais Comuns e
Varas de Execucao Penal. As variantes compartilham o modulo-base, mas podem
possuir campos, fluxos, documentos e regras especificas.

Quando duas denominacoes forem variacoes do mesmo recurso, a modelagem deve preferir um modulo tecnico unico com capacidades ou contextos comerciais diferentes. Isso evita duplicacao e mantem preco, dependencias e limites centralizados.

## Precos

Os valores sao armazenados em BRL com duas casas decimais e exibidos como `R$ 0,00`.

O preco mensal de um plano e calculado pela soma dos precos mensais das funcionalidades vinculadas. Descontos comerciais devem ser configuraveis por ciclo:

- mensal;
- anual.

O preco aplicado a uma assinatura existente nao deve ser alterado retroativamente quando o catalogo mudar. A contratacao deve guardar um snapshot dos valores utilizados.

## Status e publicacao

O ciclo comercial de sistemas, planos e funcionalidades e:

- `ativo`: disponivel para comercializacao;
- `pausado`: temporariamente indisponivel;
- `arquivado`: retirado da comercializacao, preservando historico.

O estado de publicacao deve ser tratado separadamente do ciclo comercial, permitindo rascunho, revisao, publicacao e agendamento. Somente a versao publicada e comercializavel fica disponivel no catalogo publico.

## Ofertas sugeridas e personalizadas

Cada plano aprovado e uma oferta sugerida. O cliente pode alterar limites dentro das opcoes permitidas e pode montar uma oferta personalizada com funcionalidades ativas e compativeis. A oferta personalizada nao cria automaticamente um novo plano no catalogo.

## Regras do catalogo publico

O catalogo publico deve consultar a API e exibir somente registros:

- ativos;
- publicados;
- completos e validos;
- pertencentes a composicoes permitidas.

Se a consulta ao banco/API falhar, o sistema nao deve exibir precos ou opcoes simuladas. Deve informar indisponibilidade e registrar o erro.
