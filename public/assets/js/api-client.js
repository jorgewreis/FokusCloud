window.FokusApi = (() => {
  let csrfToken = null;

  async function csrf() {
    if (csrfToken) return csrfToken;
    const response = await fetch('/api/csrf-token', { credentials: 'same-origin' });
    csrfToken = (await response.json()).token;
    return csrfToken;
  }

  async function request(path, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const headers = { Accept: 'application/json', ...(options.headers || {}) };
    if (method !== 'GET') headers['X-CSRF-TOKEN'] = await csrf();
    let body = options.body;
    if (body && typeof body === 'object' && !(body instanceof FormData)) body = JSON.stringify(body);
    if (body && !headers['Content-Type'] && !(body instanceof FormData)) headers['Content-Type'] = 'application/json';
    const response = await fetch(`/api${path}`, { ...options, body, method, headers, credentials: 'same-origin' });
    const payload = response.status === 204 ? null : await response.json();
    if (!response.ok) throw new Error(payload?.message || 'Não foi possível concluir a solicitação.');
    return payload;
  }

  return { request };
})();
