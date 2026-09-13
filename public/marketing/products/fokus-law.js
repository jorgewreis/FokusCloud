(() => {
  const form = document.querySelector('[data-law-login-form]');
  if (form) {
    const email = form.elements.email, system = form.elements.system, profile = form.elements.profile, password = form.elements.password;
    const submit = form.querySelector('button[type="submit"]'), status = form.querySelector('[data-law-login-status]');
    const profiles = { law_juridico: [['juridico_admin', 'Administrador jurídico'], ['juridico_operador', 'Operador jurídico'], ['juridico_viewer', 'Visualizador']], law_publico: [['publico_chefe', 'Chefe / escrivão'], ['publico_operador', 'Servidor operacional'], ['publico_viewer', 'Visualizador']], law_advocacia: [['advocacia_admin', 'Administrador do escritório'], ['advocacia_operador', 'Operador'], ['advocacia_viewer', 'Visualizador']] };
    let lookupTimer;
    submit.textContent = 'Entrar';
    const companyLabels = { law_juridico: 'Jurídico - Primeira Vara Crime de Ilhéus', law_publico: 'Setor Público - Primeira Vara Crime de Ilhéus', law_advocacia: 'Advocacia - Érica Reis de Menezes' };
    const systemLabelObserver = new MutationObserver(() => { const option = system.options[0]; if (option && companyLabels[system.value]) option.textContent = companyLabels[system.value]; });
    systemLabelObserver.observe(system, { childList: true });
    const reset = () => { system.disabled = true; system.innerHTML = '<option value="">Consultando seu cadastro…</option>'; profile.disabled = true; profile.innerHTML = '<option value="">Selecione o sistema primeiro</option>'; password.disabled = true; password.value = ''; submit.disabled = true; };
    email.addEventListener('input', () => { clearTimeout(lookupTimer); reset(); if (!email.value.trim()) { system.innerHTML = '<option value="">Aguardando identificação</option>'; status.textContent = 'A identificação começa ao informar seu e-mail.'; return; } status.textContent = 'Consultando os sistemas vinculados ao e-mail…'; lookupTimer = setTimeout(() => { const value = email.value.toLowerCase(); const selected = value.includes('vara') || value.includes('publico') || value.includes('cartorio') ? 'law_publico' : value.includes('juridico') ? 'law_juridico' : 'law_advocacia'; const labels = { law_juridico: 'Fokus Law · Setor Jurídico', law_publico: 'Fokus Law · Setor Público', law_advocacia: 'Fokus Law · Advocacia' }; system.innerHTML = `<option value="${selected}">${labels[selected]}</option>`; system.value = selected; system.disabled = false; profile.innerHTML = '<option value="">Escolha seu perfil</option>' + profiles[selected].map(([value, label]) => `<option value="${value}">${label}</option>`).join(''); profile.disabled = false; status.textContent = 'Sistema localizado. Escolha o perfil para liberar a senha.'; }, 420); });
    profile.addEventListener('change', () => { password.disabled = !profile.value; submit.disabled = !profile.value; status.textContent = profile.value ? 'Perfil confirmado. A senha está liberada.' : 'Escolha seu perfil para continuar.'; if (!profile.value) password.value = ''; });
    profile.addEventListener('change', () => { if (profile.value) status.textContent = 'Perfil confirmado. Digite sua senha.'; });
    form.addEventListener('submit', (event) => { event.preventDefault(); status.textContent = 'O acesso contextual será ativado junto à publicação do ambiente Fokus Law.'; });
  }
  const livePlans = document.querySelector('[data-law-live-plans]'), grid = document.querySelector('[data-law-live-plan-grid]');
  if (livePlans && grid && window.FokusApi) window.FokusApi.request('/catalog/law').then((catalog) => { const plans = catalog.plans || []; if (!plans.length) return; grid.innerHTML = plans.map((plan) => `<article class="law-live-plan"><strong>${plan.name}</strong><span>A partir de R$ ${Number(plan.monthly_amount).toLocaleString('pt-BR', { minimumFractionDigits: 2 })} / mês</span></article>`).join(''); livePlans.hidden = false; }).catch(() => {});
})();
