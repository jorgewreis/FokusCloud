# Modelo de empresas, unidades e vinculos

## Empresa

Uma empresa e o limite principal de isolamento, contratacao e governanca no
Fokus Cloud. Pode representar pessoa fisica ou juridica e pode contratar um ou
mais produtos. Cada CPF ou CNPJ normalizado identifica uma unica empresa ativa.

Toda empresa recebe automaticamente uma unidade principal do tipo
`headquarters`. Filiais, departamentos e equipes sao unidades adicionais,
diretamente vinculadas a empresa e sem hierarquia entre si na primeira versao.

## Unidades

| Tipo | Uso | Documento proprio |
| --- | --- | --- |
| `headquarters` | Contexto operacional padrao da empresa. | Usa o documento da empresa por relacionamento. |
| `branch` | Filial da matriz. | Pode possuir CNPJ proprio. |
| `department` | Departamento interno. | Nao aplicavel por padrao. |
| `team` | Equipe operacional. | Nao aplicavel por padrao. |

O tipo da unidade e imutavel. Uma correcao estrutural exige encerrar a unidade
e criar outra, preservando os dados e o historico. Nome, status e demais dados
operacionais poderao ser alterados conforme permissao.

## Estados

Empresas usam os estados `pending`, `active`, `suspended`, `cancelled` e
`closed`. Unidades usam `active`, `suspended` e `closed`.

Uma unidade suspensa ou encerrada nao recebe novos usuarios e bloqueia o uso
operacional. A empresa pode continuar ativa com outras unidades funcionando.
Ao encerrar uma unidade, os dados ficam disponiveis somente para consulta
autorizada; nao ha exclusao ou transferencia automatica.

## Administracao

Somente o `admin` pode criar, alterar, suspender ou encerrar unidades. O
`admin` possui escopo de toda a empresa e nao precisa estar associado a uma
unidade em `membership_units`. Pode selecionar uma unidade para operar dados
especificos, sem perder o escopo administrativo global.

## Vinculos

`gestor` e `usuario` precisam estar associados a uma ou mais unidades ativas.
Se ficarem sem unidades, o vinculo empresarial permanece, mas passa para
`suspended` e perde acesso operacional. A associacao de uma nova unidade pode
reativar o acesso conforme as demais regras.

O `gestor` pode administrar usuarios da propria unidade, incluindo convite,
visualizacao, suspensao, restauracao e remocao. Nao pode alterar perfis,
administrar gestores ou atuar fora das unidades sob sua gestao.

## Contexto ativo

Quando o usuario possui varias unidades, deve selecionar uma unidade ativa.
Cada troca exige validacao do servidor e a sessao mantem um unico contexto
operacional por vez. O `admin` pode visualizar todas as unidades; o `gestor`
consolida apenas as unidades autorizadas; o `usuario` visualiza somente a
unidade ativa.

## Associacao e transferencia

Adicionar um usuario a uma unidade cria uma associacao adicional e nao remove
as unidades existentes. Transferir e uma operacao distinta: adiciona a nova
unidade e remove a anterior em uma transacao unica, preservando o historico.

O `admin` pode transferir qualquer usuario. O `gestor` pode transferir
usuarios somente entre unidades sob sua gestao. Unidades suspensas ou
encerradas nao podem receber novas associacoes.

## Referencias

- [Modelo relacional](relational-model.md)
- [Politica de controle de acesso](../07-security/access-control-policy.md)
- [Modelo de permissoes e perfis](../07-security/permission-model.md)
