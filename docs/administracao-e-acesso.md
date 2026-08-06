# Administração e acesso

## Modelo de relacionamento

O sistema adota três conceitos distintos:

| Entidade | Responsabilidade |
| --- | --- |
| Empresa | Representa a organização identificada por CPF ou CNPJ e recebe a assinatura. |
| Usuário | Representa a pessoa identificada por CPF, com credenciais e dados de contato próprios. |
| Vínculo usuário-empresa | Define o acesso de um usuário a uma empresa e o seu perfil naquele contexto. |

Uma conta de usuário pode possuir vínculos com várias empresas. Cada empresa
possui exatamente um vínculo com perfil `admin` por vez. Essa exclusividade
deve ser garantida no banco de dados, além das validações de interface.

Os perfis são fixos: `admin`, `gestor` e `usuário`. Gestor e usuário atuam
somente sobre dados operacionais liberados pelo produto. Apenas o admin altera
usuários entre gestor e usuário; a promoção para admin usa a transferência
formal. Consulte [Modelo relacional](modelo-relacional.md) para as restrições
de banco e [Isolamento e governança](isolamento-e-governanca-de-dados.md) para
o escopo de acesso.

## Login e empresa ativa

O login usa CPF e senha. O e-mail não é um identificador de login; ele é usado
para confirmação, recuperação de senha e notificações de segurança.

Quando o usuário tiver acesso a mais de uma empresa, o sistema deve exigir a
seleção da empresa ativa em todo login. A sessão e as permissões seguintes são
sempre avaliadas no contexto da empresa escolhida.

## Administrador da empresa

O administrador é o usuário com perfil máximo dentro de uma empresa. Ele é
responsável pela assinatura, acessos e cadastros de usuários daquela empresa.
O mesmo usuário pode ser administrador de várias empresas, desde que cada uma
tenha somente um administrador ativo.

### Transferência de administração

Somente o administrador atual pode iniciar a transferência. O destinatário
precisa, obrigatoriamente:

- já estar vinculado à mesma empresa;
- estar ativo;
- possuir e-mail confirmado.

O administrador atual confirma a solicitação com sua senha. O novo
administrador recebe um e-mail com link de aceite; a troca só é concluída após
esse aceite.

No início da transferência, o administrador atual escolhe o destino do seu
próprio acesso:

| Escolha | Resultado após o aceite |
| --- | --- |
| Permanecer | O antigo admin passa a ser usuário comum da empresa. |
| Remover acesso | O antigo admin deixa de ter vínculo com a empresa. |

A mudança do perfil do novo admin e a remoção do perfil do admin anterior são
uma única operação. O sistema não pode manter dois admins simultâneos, nem
deixar a empresa sem admin ao concluir a transferência.

### Notificações e histórico

O admin atual recebe avisos de criação e conclusão da solicitação. O novo admin
recebe o convite e a confirmação da conclusão.

O histórico de administração deve ser consultável pelo admin atual e registrar:

- data e hora;
- usuário que iniciou a operação;
- admin anterior e novo admin;
- decisão sobre o acesso do admin anterior;
- status do aceite e resultado final.

## Alterações de dados

| Dado | Regra |
| --- | --- |
| Nome do usuário | Alteração direta pelo próprio usuário. |
| E-mail do usuário | Alteração pendente até a confirmação do novo endereço. |
| Senha | Alteração direta por usuário autenticado. |
| CPF do usuário | Alteração somente por suporte. |
| Nome da empresa | Imutável após a criação. |
| CPF/CNPJ da empresa | Imutável após a criação. |

A troca de e-mail mantém o endereço atual ativo até a confirmação do novo
endereço. Os dois e-mails recebem uma notificação da tentativa de alteração.

## Inclusão e remoção de usuários

O admin cadastra diretamente nome, CPF, e-mail e perfil de um novo usuário. O
vínculo começa pendente e o sistema envia link válido por 24 horas para que o
destinatário crie a própria senha. Se o CPF já corresponder a uma conta global,
o destinatário deve aceitar o vínculo antes da ativação.

Remover alguém de uma empresa afeta somente seu vínculo com ela. A conta global
e acessos a outras empresas permanecem inalterados.
