(function () {
  const path = window.location.pathname;
  const links = [
    ['/portal', 'Visão geral'],
    ['/portal/assinaturas', 'Assinaturas'],
    ['/portal/usuarios', 'Usuários'],
    ['/portal/empresas', 'Empresas'],
    ['/portal/perfil', 'Meu perfil']
  ];
  function mount() {
    document.documentElement.dataset.role = 'user';
    document.documentElement.dataset.theme = 'light';
    document.body.classList.add('portal-page');
    const main = document.querySelector('main');
    if (!main || main.dataset.portalMounted) return;
    main.dataset.portalMounted = 'true';
    const shell = document.createElement('div'); shell.className = 'portal-shell';
    const aside = document.createElement('aside'); aside.className = 'portal-sidebar';
    aside.innerHTML = `<a class="portal-brand" href="/portal">Fokus<span>Cloud</span></a><div class="portal-context" id="portal-context">Empresa ativa<strong>Carregando...</strong></div><nav class="portal-nav" aria-label="Navegação principal">${links.map(([href, label]) => `<a href="${href}" class="${path === href ? 'active' : ''}">${label}</a>`).join('')}</nav><button type="button" id="portal-logout" class="portal-logout">Sair</button>`;
    const content = document.createElement('div'); content.className = 'portal-main';
    const inner = document.createElement('div'); inner.className = 'portal-content';
    const header = document.createElement('header'); header.className = 'portal-header';
    header.innerHTML = `<div><p class="portal-eyebrow">Portal do cliente</p><h1>${document.title.split('|')[0].trim()}</h1></div><p id="portal-user-name">Carregando conta...</p>`;
    inner.append(header, main); content.append(inner); shell.append(aside, content); document.body.append(shell);
    document.querySelector('#portal-logout').onclick = async () => { try { await FokusApi.request('/auth/logout', { method: 'POST' }); } finally { location.assign('/'); } };
    if (window.FokusApi) window.FokusApi.request('/auth/me').then((data) => { const company = data.companies?.find((item) => item.id === data.active_company_id); document.querySelector('#portal-user-name').textContent = data.user?.name || ''; document.querySelector('#portal-context strong').textContent = company?.name || 'Nenhuma empresa selecionada'; }).catch(() => {});
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mount); else mount();
})();
