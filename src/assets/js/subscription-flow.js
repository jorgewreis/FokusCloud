(() => {
  const catalog = window.FokusCatalog[document.body.dataset.product];
  const money = value => value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  const state = { step: 1, mode: 'modules', cycle: 'monthly', items: [], plan: null, account: 'register', usage: { oficios: 2500, partes: 5000, pessoas: 50, empreendimentos: 20, imoveis: 200, relatorios: 500 } };
  const root = document.querySelector('#subscription-app');
  const moduleById = id => catalog.modules.find(item => item[0] === id);
  const usageFee = () => state.items.reduce((sum, id) => { const config = moduleById(id)[4]; return sum + (config ? config.options.indexOf(state.usage[id] || config.options[0]) * config.step : 0); }, 0);
  const monthly = () => state.items.reduce((sum, id) => sum + moduleById(id)[3], 0) * (state.mode === 'plan' ? .9 : 1) + usageFee();
  const annual = () => monthly() * 9;
  const period = () => state.cycle === 'annual' ? 'ano' : 'mês';
  const amount = () => state.cycle === 'annual' ? annual() : monthly();

  function accountView() {
    const isRegister = state.account === 'register';
    return `<div class="auth-card"><div class="tabs"><button data-account="register" class="${isRegister ? 'selected' : ''}">Criar conta da empresa</button><button data-account="login" class="${!isRegister ? 'selected' : ''}">Entrar</button></div><p>${isRegister ? 'O administrador será responsável pela assinatura e pelos acessos da empresa.' : 'Use os dados da sua empresa para continuar.'}</p><form id="account-form">${isRegister ? '<label>Nome da empresa<input required name="company" placeholder="Sua empresa ou cartório"></label><label>Nome do administrador<input required name="name" placeholder="Nome completo"></label>' : ''}<label>E-mail profissional<input required type="email" name="email" placeholder="voce@empresa.com.br"></label><label>Senha<input required type="password" name="password" minlength="6" placeholder="Mínimo de 6 caracteres"></label><button class="button button-green">Continuar <span>→</span></button></form><small>Protótipo visual: nenhum dado será salvo nesta etapa.</small></div>`;
  }
  function selectionView() {
    let options = state.mode === 'modules' ? catalog.modules.map(module => `<label class="module-choice"><input type="checkbox" value="${module[0]}" ${state.items.includes(module[0]) ? 'checked' : ''}><span><b>${module[1]}</b><small>${module[2]}</small></span><strong>${money(state.cycle === 'annual' ? module[3] * 9 : module[3])}/${period()}</strong></label>`).join('') : catalog.plans.map(plan => {
      const monthlyPlan = plan[1].reduce((sum, id) => sum + moduleById(id)[3], 0) * .9;
      const planAmount = state.cycle === 'annual' ? monthlyPlan * 9 : monthlyPlan;
      return `<label class="choice-card ${state.plan === plan[0] ? 'selected' : ''}"><input type="radio" name="plan" value="${plan[0]}" ${state.plan === plan[0] ? 'checked' : ''}><b>${plan[0]}</b><span>${plan[1].map(id => moduleById(id)[1]).join(' · ')}</span><strong>${money(planAmount)}/${period()}</strong></label>`;
    }).join('');
    const limits = state.items.map(id => { const item = moduleById(id), config = item[4]; if (!config) return ''; const current = state.usage[id] || config.options[0]; return `<label class="usage-control"><span><b>${item[1]}</b><small>${config.label}</small></span><select data-usage="${id}">${config.options.map((value,index) => `<option value="${value}" ${value === current ? 'selected' : ''}>${value.toLocaleString('pt-BR')} ${index ? `(+${money(index * config.step)}/mês)` : '(incluído)'}</option>`).join('')}</select></label>`; }).join('');
    options += limits ? `<div class="usage-controls"><p>Limites e upgrades de utilização</p>${limits}</div>` : '';
    const discountLabel = state.cycle === 'annual' ? 'Desconto total de 25%' : state.mode === 'plan' ? 'Desconto total de 10% no conjunto' : 'Selecione ao menos um módulo';
    return `<div class="selector"><div class="selector-top"><div><p class="eyebrow dark">Escolha sua assinatura</p><h2>Comece do seu jeito.</h2></div><div class="cycle"><button data-cycle="monthly" class="${state.cycle === 'monthly' ? 'selected' : ''}">Mensal</button><button data-cycle="annual" class="${state.cycle === 'annual' ? 'selected' : ''}">Anual</button></div></div><div class="mode-tabs"><button data-mode="modules" class="${state.mode === 'modules' ? 'selected' : ''}">Montar por módulos</button><button data-mode="plan" class="${state.mode === 'plan' ? 'selected' : ''}">Planos sugeridos</button></div><div class="choices">${options}</div><div class="selection-footer"><div><small>${discountLabel}</small><strong>${money(amount())}<em>/${period()}</em></strong></div><button class="button button-green" data-next ${state.items.length ? '' : 'disabled'}>Revisar assinatura <span>→</span></button></div></div>`;
  }
  function reviewView() {
    const items = state.items.map(id => { const item = moduleById(id), config = item[4]; return `<li>${item[1]}${config ? ` — até ${(state.usage[id] || config.options[0]).toLocaleString('pt-BR')} ${config.summary || 'itens'}` : ''}</li>`; }).join('');
    const annualNote = state.cycle === 'annual' ? `<p class="review-saving">Desconto total de <b>25%</b></p>` : '';
    return `<div class="review-card"><p class="eyebrow dark">Revisão do pedido</p><h2>Quase pronto.</h2><div class="review-grid"><div><small>PRODUTO</small><b>${catalog.name}</b></div><div><small>MODELO</small><b>${state.mode === 'plan' ? `Plano ${state.plan}` : 'Módulos personalizados'}</b></div><div><small>COBRANÇA</small><b>${state.cycle === 'annual' ? 'Anual' : 'Mensal'}</b></div><div><small>TOTAL</small><b>${money(amount())} / ${period()}</b></div></div>${annualNote}<h3>Itens incluídos</h3><ul>${items}</ul><div class="review-actions"><button class="button button-outline" data-back>Voltar</button><button class="button button-green" data-confirm>Confirmar interesse <span>→</span></button></div><small class="payment-note">O checkout do Mercado Pago será conectado aqui. Esta confirmação ainda não cria cobrança nem acesso.</small></div>`;
  }
  function bind() {
    root.querySelectorAll('[data-account]').forEach(button => button.onclick = () => { state.account = button.dataset.account; render(); });
    const form = root.querySelector('#account-form');
    if (form) form.onsubmit = event => { event.preventDefault(); state.step = 2; render(); };
    root.querySelectorAll('[data-mode]').forEach(button => button.onclick = () => { state.mode = button.dataset.mode; state.items = []; state.plan = null; render(); });
    root.querySelectorAll('[data-cycle]').forEach(button => button.onclick = () => { state.cycle = button.dataset.cycle; render(); });
    root.querySelectorAll('input[type=checkbox]').forEach(input => input.onchange = () => { state.items = [...root.querySelectorAll('input[type=checkbox]:checked')].map(item => item.value); render(); });
    root.querySelectorAll('input[name=plan]').forEach(input => input.onchange = () => { state.plan = input.value; state.items = catalog.plans.find(plan => plan[0] === input.value)[1]; render(); });
    root.querySelectorAll('[data-usage]').forEach(select => select.onchange = () => { state.usage[select.dataset.usage] = Number(select.value); render(); });
    root.querySelector('[data-next]')?.addEventListener('click', () => { state.step = 3; render(); });
    root.querySelector('[data-back]')?.addEventListener('click', () => { state.step = 2; render(); });
    root.querySelector('[data-confirm]')?.addEventListener('click', () => { root.querySelector('.review-card').innerHTML = `<p class="eyebrow dark">Pedido registrado</p><h2>Seu pedido está pronto para a próxima etapa.</h2><p>Quando a integração estiver ativa, você seguirá para o checkout seguro do Mercado Pago e o acesso será liberado após a confirmação do pagamento.</p><a class="button button-green" href="${catalog.back}">Voltar ao produto <span>→</span></a>`; });
  }
  function render() {
    const steps = ['Conta', 'Assinatura', 'Revisão'].map((label, index) => `<span class="${state.step === index + 1 ? 'active' : state.step > index + 1 ? 'done' : ''}"><b>${index + 1}</b>${label}</span>`).join('');
    const content = state.step === 1 ? accountView() : state.step === 2 ? selectionView() : reviewView();
    root.innerHTML = `<div class="checkout-head"><a href="${catalog.back}">← Voltar para ${catalog.name}</a><p>Assinatura independente</p><h1>${catalog.name}</h1><div class="checkout-steps">${steps}</div></div><div class="checkout-body">${content}</div>`;
    bind();
  }
  render();
})();
