# Iconografia do menu lateral do Backoffice

## Objetivo

Manter todos os icones do menu lateral visualmente uniformes e evitar que uma
troca futura reintroduza diferencas de tonalidade, opacidade, escala ou
alinhamento.

## Padrao obrigatorio

- Fonte: conjunto `Ultimate Regular` da Streamline.
- Formato versionado no repositorio: PNG com transparencia.
- Canvas: `48x48` pixels para icones de navegacao e controles do sidebar.
- Exibicao no CSS: tamanho visual definido pelo layout, atualmente `22x22px`.
- Cor: aplicada pela regra CSS comum do sidebar; nao criar filtro, opacidade ou
  excecao especifica para um item.
- Arquivos: manter os nomes semanticos existentes em
  `public/backoffice/assets/icons/`, mesmo quando o icone escolhido for
  substituido.

## Como adicionar ou substituir um icone

1. Escolha o icone na pagina oficial de download da Streamline e confirme que
   a pagina identifica o conjunto como `Ultimate Regular`.
2. Baixe o PNG transparente. Nao misture arquivos de `Ultimate Duotone`,
   `Ultimate Light`, SVG convertido por renderizadores diferentes ou PNGs com
   dimensoes diferentes.
3. Normalize o arquivo para um canvas `48x48` antes de copia-lo para
   `public/backoffice/assets/icons/`.
4. Atualize a referencia no `public/backoffice/index.html` com o mesmo sufixo
   de cache dos icones do sidebar, por exemplo:

   ```html
   <img src="/backoffice/assets/icons/Nome--Streamline-Ultimate.png?v=20260915-sidebar-icons-v1" alt="">
   ```

5. Se o arquivo for alterado, incremente o identificador de cache do conjunto
   e atualize tambem `pageVersion` no `index.html` e a versao da folha
   `admin-dashboard.css`.
6. Nao adicione seletores como `[data-sidebar-item="..."] img` para corrigir
   cor ou opacidade. Se houver diferenca visual, corrija o arquivo PNG ou a
   regra comum do sidebar.

## Itens atualmente cobertos

O mesmo criterio vale para Dashboard, Empresas, Catalogo, Assinaturas,
Pagamentos, Vouchers, Admins, Interessados, Configuracoes e para os icones de
abrir/fechar o menu. O icone institucional do rodape segue a regra propria de
marca, mas deve continuar sendo PNG e nao deve alterar os icones de navegacao.

## Validacao antes do commit

- confirmar que todos os PNGs alterados possuem `48x48` pixels;
- confirmar que os `src` apontam para os arquivos versionados;
- pesquisar por excecoes CSS especificas de itens do sidebar;
- executar `php artisan test --testsuite=Feature`;
- executar `git diff --check`;
- depois do push, verificar o workflow de deploy e o HTTP 200 da pagina
  `/backoffice/` e dos PNGs alterados.

As paginas de referencia utilizadas para os icones devem permanecer registradas
na descricao do commit ou na tarefa correspondente, para que a origem do
desenho possa ser auditada.
