# Design system de formularios

## Objetivo

Este documento define o contrato visual e semantico compartilhado para formularios do FokusCloud. Paginas funcionais e mockups devem carregar o `main.css` do dominio e reutilizar os componentes de `components/form-admin.css`. A classe-raiz `.fc-form` delimita o alcance dos estilos de controles.

O modelo foi inspirado nos padroes oficiais de formularios e estados de [Bootstrap](https://getbootstrap.com/docs/5.1/forms/overview/), [Tailwind CSS](https://tailwindcss.com/docs/hover-focus-and-other-states) e [Bulma](https://bulma.io/documentation/form/), sem adicionar esses frameworks como dependencias.

## Principios

- HTML semantico antes de classes visuais: `form`, `fieldset`, `legend`, `label`, `input`, `select` e `button`.
- Tokens de tema, papel, cor, tipografia e espacamento sao a unica fonte visual.
- Estados comunicam significado por texto, estrutura e estilo, nunca somente por cor.
- O servidor permanece a fonte final de validacao; o navegador oferece retorno imediato.
- Mockups e paginas de producao usam os mesmos componentes compartilhados.

## Componentes

| Componente | Classes | Uso |
| --- | --- | --- |
| Campo | `form-field`, `form-label`, `form-help` | Agrupa label, controle e orientacao. |
| Texto | `form-input-text` | Texto, email, senha, busca, telefone, numero, moeda, data e hora. |
| Selecao | `form-select` | Select simples ou multiplo. |
| Multilinear | `form-textarea` | Conteudo longo. |
| Checks | `form-check`, `form-check-group` | Opcoes independentes. |
| Radios | `form-radio`, `form-radio-group` | Opcoes mutuamente exclusivas. |
| Switch | `form-switch`, `form-switch-group` | Ativacao binaria persistente. |
| Range | `form-range`, `range-value` | Valor numerico em intervalo. |
| Grupo | `input-group`, `input-group-addon` | Prefixos, sufixos, unidades e botoes. |
| Mensagens | `form-error`, `form-success`, `form-warning`, `system-notice` | Feedback de campo e formulario. |
| Acoes | `form-actions`, `submit`, `button`, `button-secondary`, `button-danger`, `button-ghost` | Comandos do formulario. |
| Dialogo | `dialog-panel`, `dialog-header`, `dialog-content`, `dialog-actions` | Formularios em dialogos e confirmacoes. |

A raiz `.fc-form` escopa os controles nativos. O comportamento compartilhado fica em `backoffice/assets/js/form-system.js`, com `FokusForm.validate`, `FokusForm.mapServerErrors`, `FokusForm.setFeedback`, `FokusForm.setLoading` e `FokusForm.clear`.

## Estados

Controles aceitam os estados nativos `:hover`, `:focus-visible`, `:disabled`, `:read-only`, `:checked`, `:indeterminate`, `:valid` e `:invalid`, alem das classes de campo `is-valid`, `is-invalid`, `is-warning` e `is-loading`.

Use `aria-invalid="true"` quando houver erro confirmado e `aria-invalid="false"` quando a validacao explicita tiver sucesso. Mensagens devem receber um `id` e ser referenciadas por `aria-describedby`. Erros de submissao devem usar `system-notice` com `role="alert"` ou `aria-live="polite"` conforme a urgencia.

Todo controle com o atributo `required` recebe automaticamente um `*` no `label` ou `legend` do `.form-field`. A classe `.form-label-required` permanece disponivel para casos em que o marcador precise ser declarado explicitamente.

## Exemplos

```html
<div class="form-field is-invalid">
    <label class="form-label form-label-required" for="company-email">E-mail</label>
    <input class="form-input-text" id="company-email" type="email"
        aria-invalid="true" aria-describedby="company-email-error" required>
    <small class="form-error" id="company-email-error" role="alert">
        Informe um e-mail valido.
    </small>
</div>
```

```html
<fieldset class="form-field">
    <legend class="form-label">Periodicidade</legend>
    <div class="form-radio-group" aria-describedby="period-help">
        <label class="form-radio"><input class="form-radio-input" type="radio" name="period" value="monthly"> Mensal</label>
        <label class="form-radio"><input class="form-radio-input" type="radio" name="period" value="annual"> Anual</label>
    </div>
    <small class="form-help" id="period-help">Escolha uma opcao.</small>
</fieldset>
```

```html
<div class="input-group">
    <span class="input-group-addon">R$</span>
    <input class="form-input-text" type="number" min="0" step="0.01" aria-label="Valor">
    <button class="button" type="button">Aplicar</button>
</div>
```

## Responsividade

Use `form-grid` e `form-line` para composicoes de formulario. Colunas devem cair para seis trilhas em telas intermediarias e uma coluna em telas pequenas. `input-group` deve permitir quebra sem overflow. Labels, mensagens e botoes nao podem se sobrepor ou alterar a largura estrutural do controle.

## Validacao e acessibilidade

- Todo controle visivel deve ter label associado ou `aria-label` justificado.
- `fieldset` e `legend` sao obrigatorios para grupos de radio e checkbox com uma pergunta comum.
- Use `required`, `min`, `max`, `minlength`, `maxlength`, `pattern` e tipos HTML adequados.
- Execute `checkValidity()` e `reportValidity()` antes de chamadas simples; erros da API devem ser mapeados ao campo.
- Nao exponha senha, token, codigo MFA ou payload sensivel em mensagens, HTML ou logs.
- Foco visivel deve permanecer presente em todos os controles interativos.
- Animacoes devem respeitar `prefers-reduced-motion`.
- Botoes em processamento usam `is-loading`, ficam desabilitados e preservam um texto acessivel.
- Respostas Laravel com `errors` devem ser mapeadas para os campos e tambem aparecer no resumo geral.

## Tokens

Dimensoes recorrentes devem usar os tokens `--form-control-height`, `--form-control-padding-x`, `--form-control-radius`, `--form-control-border`, `--form-control-focus-border`, `--form-control-focus-ring` e `--form-control-gap` definidos em `base/variables.css`.

## Matriz de tipos

| Tipo | Classe | Regras |
| --- | --- | --- |
| `text`, `email`, `password`, `search`, `tel` | `form-input-text` | Label, autocomplete e mensagem de ajuda quando necessario. |
| `number`, `date`, `time`, `datetime-local` | `form-input-text` | Limites e passo no HTML; unidade no `input-group` quando aplicavel. |
| `file` | `form-input-text` | Aceitacao e tamanho devem ser informados antes do envio. |
| `select` | `form-select` | Opcao vazia deve explicar a acao esperada. |
| `textarea` | `form-textarea` | Use limite de caracteres quando houver regra de negocio. |
| `checkbox` | `form-check-input` | Use `form-check` e texto clicavel. |
| `radio` | `form-radio-input` | Agrupe em `fieldset` e `form-radio-group`. |
| `range` | `form-range` | Mostre valor atual e limites de forma textual. |

## Checklist de contrato

| Verificacao | Resultado esperado |
| --- | --- |
| Raiz | Todo formulario do Backoffice usa `.fc-form`. |
| Controle | Inputs, selects e textareas ficam dentro de `.fc-form` ou usam classe de componente. |
| Label | Cada controle tem `label`, `aria-label` ou pertence a `fieldset` com `legend`. |
| Erro | Campo recebe `aria-invalid`, mensagem vinculada e resumo quando houver falha. |
| Loading | Formulario fica `aria-busy`, botao de envio fica desabilitado e exibe spinner. |
| CSS | Nenhuma pagina ou mockup cria `style` ou `<style>` para componentes de formulario. |
| Mockup | A matriz de estados permanece atualizada em `mockups/forms.html`. |

## Governanca

Qualquer novo controle deve ser adicionado ao CSS compartilhado, documentado neste arquivo e demonstrado em [forms.html](../../public/backoffice/assets/css/mockups/forms.html). Nao criar CSS paralelo em paginas ou mockups. Uma alteracao visual global exige revisao das paginas consumidoras e validacao desktop, tablet e celular. A matriz visual do mockup deve ser atualizada junto com novos estados.
