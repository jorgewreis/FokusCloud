# Design system de formulários

## Objetivo

Formulários do Fokus Cloud usam o contrato oficial do Fokus Styles instalado.
Consulte também [Governança visual com Fokus Styles](fokus-styles-ui-governance.md)
e [Padrões de interface do Backoffice](backoffice-ui-patterns.md).

## API oficial

| Necessidade | API Fokus Styles |
| --- | --- |
| Formulário | elemento semântico `form` com componentes oficiais |
| Label | `fs-form-label` |
| Input e textarea | `fs-form-control` |
| Select | `fs-form-select` |
| Organização | `fs-form-row`, `fs-form-col`, `fs-col-*` |
| Botão | `fs-btn` e uma variante oficial |
| Feedback | `fs-alert`, `fs-toast` ou estado oficial equivalente |
| Loading | estado oficial do componente e `aria-busy` quando aplicável |

Use HTML semântico (`form`, `fieldset`, `legend`, `label`, `input`, `select`,
`textarea` e `button`) e associe cada controle a um label. Não substitua a API
oficial por classes locais como `form-input-text`, `form-select`, `button` ou
`.fc-form` em novas páginas.

## Regras de estados e acessibilidade

- Cada controle deve possuir label associado ou um `aria-label` justificado.
- Use `required`, limites, tipos HTML e `autocomplete` adequados.
- Erros devem usar `aria-invalid="true"` e mensagem associada por
  `aria-describedby`.
- Mensagens de formulário devem ser anunciáveis com `role="alert"` ou
  `aria-live` conforme a urgência.
- Botões em processamento devem ficar desabilitados, preservar texto
  acessível e indicar loading pelo mecanismo oficial.
- Foco visível, navegação por teclado e `prefers-reduced-motion` são
  obrigatórios.
- Valide estados normal, foco, erro, loading, sucesso, vazio e desabilitado.

## Responsividade e temas

Use o grid, containers, espaçamento e utilitários do Fokus Styles. Os controles
devem permanecer legíveis e não podem produzir overflow ou sobreposição em
desktop, tablet e mobile. Valide tema claro e escuro quando suportado.

## Migração de contratos legados

Documentos e páginas antigas podem conter classes do contrato anterior, como
`.fc-form`, `.form-field`, `.form-input-text` e `.form-select`. Elas não devem
ser ampliadas em novas implementações. Ao tocar em uma página legada, faça a
migração incremental para `fs-form-label`, `fs-form-control`, `fs-form-select`
e utilitários oficiais, preservando comportamento funcional e
registrando impactos nos consumidores.

Qualquer novo controle compartilhado deve ser proposto no repositório
`fokus-styles`, documentado e validado antes de ser consumido pelo Cloud. Não
edite `node_modules` como solução permanente.
