# Requisitos do modulo identidade e acesso

## Identificacao

- Produto: Fokus Cloud
- Modulo: Identidade, empresas, usuarios e permissoes
- Codigo tecnico: `identity-and-access`
- Status: Em definicao

## Objetivo

Definir a base de identidade e autorizacao do Fokus Cloud, garantindo que usuarios, empresas, vinculos, perfis, sessoes e permissoes sejam tratados de forma consistente antes da criacao dos modulos do Fokus Law e Fokus Lead.

## Escopo

### Dentro do escopo

- Cadastro e autenticacao de usuarios.
- Confirmacao de e-mail.
- Recuperacao e criacao de senha.
- Cadastro de empresa.
- Vinculo entre usuario e empresa.
- Selecao de empresa ativa.
- Perfis `admin`, `gestor` e `usuario`.
- Permissoes atomicas com escopo por empresa, produto e modulo.
- Transferencia de administracao.
- Suspensao, remocao e restauracao de vinculos.
- Auditoria de eventos sensiveis.

### Fora do escopo

- Regras juridicas especificas do Fokus Law.
- Regras imobiliarias especificas do Fokus Lead.
- Cobranca, emissao fiscal ou gateway de pagamento.
- Permissoes internas detalhadas de cada produto derivado.

## Requisitos funcionais

| Codigo | Requisito | Criterio de aceite |
| --- | --- | --- |
| RF-IA-001 | Permitir cadastro de usuario administrador durante a criacao de uma empresa. | Ao concluir o cadastro, devem existir usuario, empresa e vinculo `admin` pendente ou ativo conforme a regra de verificacao. |
| RF-IA-002 | Permitir autenticacao por CPF e senha. | O sistema deve autenticar apenas credenciais validas e bloquear acesso de vinculos inativos. |
| RF-IA-003 | Exigir e-mail confirmado para acoes sensiveis. | Transferencia de administracao e convites devem exigir e-mail confirmado quando aplicavel. |
| RF-IA-004 | Permitir recuperacao de senha por e-mail. | O usuario deve receber link temporario para redefinir senha sem expor credenciais. |
| RF-IA-005 | Exigir selecao de empresa ativa quando o usuario possuir mais de uma empresa. | A sessao deve guardar empresa ativa antes de liberar telas protegidas. |
| RF-IA-006 | Permitir ao admin convidar usuarios para a empresa. | O convite deve criar ou reutilizar conta global e gerar vinculo pendente. |
| RF-IA-007 | Permitir ao admin definir perfil `gestor` ou `usuario`. | O perfil deve valer apenas para a empresa ativa. |
| RF-IA-008 | Permitir transferencia formal de administracao. | A empresa nao pode ficar sem admin nem manter dois admins ativos apos a conclusao. |
| RF-IA-009 | Permitir suspender, remover e restaurar vinculos. | A alteracao deve afetar apenas a empresa selecionada e preservar historico. |
| RF-IA-010 | Registrar eventos sensiveis de identidade e acesso. | Convites, alteracoes de perfil, transferencia e remocoes devem gerar trilha auditavel. |
| RF-IA-011 | Avaliar autorizacao por permissao atomica e escopo. | Toda acao protegida deve validar autenticacao, vinculo, perfil, produto, plano e regra do recurso quando aplicavel. |
| RF-IA-012 | Impedir autorizacao por interface ou por perfil amplo sem catalogo. | A decisao final deve ocorrer no servidor com permissao documentada. |

## Requisitos nao funcionais

| Codigo | Categoria | Requisito | Criterio de aceite |
| --- | --- | --- | --- |
| RNF-IA-001 | Seguranca | Toda consulta protegida deve respeitar usuario autenticado, empresa ativa e perfil. | Testes devem cobrir tentativa de acesso cruzado entre empresas. |
| RNF-IA-002 | Integridade | Cada empresa deve ter exatamente um administrador ativo. | Banco e aplicacao devem impedir estados invalidos. |
| RNF-IA-003 | Privacidade | Dados pessoais devem ser minimizados e mascarados quando exibidos em historicos. | Historicos nao devem expor CPF integral sem necessidade. |
| RNF-IA-004 | Manutencao | Regras comuns de acesso devem ficar na plataforma base. | Produtos derivados devem consumir permissoes sem duplicar logica central. |
| RNF-IA-005 | Auditoria | Eventos sensiveis devem ser rastreaveis. | Cada evento deve registrar ator, empresa, alvo, data e resultado. |
| RNF-IA-006 | Evolucao | O modelo deve permitir permissoes por produto e modulo sem duplicar a autorizacao base. | Novas permissoes devem seguir o catalogo atomico e preservar o isolamento por empresa. |

## Dados envolvidos

- `users`
- `companies`
- `company_user`
- tabelas de convites ou tokens
- tabelas de transferencia de administracao
- tabela de auditoria

## Perfis iniciais

| Perfil | Uso |
| --- | --- |
| `admin` | Administra empresa, usuarios, assinatura e transferencia de administracao. |
| `gestor` | Atua em operacoes liberadas pelo produto, sem controlar administracao da empresa. |
| `usuario` | Usa funcionalidades operacionais liberadas pelo produto. |

## Eventos auditaveis

- Cadastro de empresa.
- Confirmacao de e-mail.
- Login e logout relevantes para seguranca.
- Convite de usuario.
- Aceite de vinculo.
- Alteracao de perfil.
- Suspensao, remocao e restauracao de vinculo.
- Inicio, aceite, cancelamento ou conclusao de transferencia de administracao.

## Dependencias

- Modelo relacional em `docs/06-data/relational-model.md`.
- Politica de controle de acesso em `docs/07-security/access-control-policy.md`.
- Modelo de permissoes e perfis em `docs/07-security/permission-model.md`.
- Fluxo arquitetural em `docs/03-architecture/identity-and-access-flow.md`.

## Perguntas em aberto

- O login por CPF deve aceitar mascara ou apenas numeros?
- O perfil `gestor` deve ter permissoes globais padrao ou permissoes variaveis por produto?
- Convites expirados devem poder ser reenviados automaticamente?
- A empresa deve poder ter administradores temporarios ou somente transferencia definitiva?
- Eventos de login devem aparecer para o usuario final ou apenas em auditoria interna?
