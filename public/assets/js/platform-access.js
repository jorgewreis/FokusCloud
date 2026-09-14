(() => {
  const opener = document.querySelector("[data-platform-access]");
  const card = document.querySelector("[data-platform-access-card]");
  if (!opener || !card || !window.FokusApi) return;

  const login = card.querySelector("[data-platform-access-login]");
  const mfa = card.querySelector("[data-platform-access-mfa]");
  const message = card.querySelector("[data-platform-access-message]");
  const closeButton = card.querySelector("[data-platform-access-close]");

  const showMessage = (text, error = false) => {
    message.textContent = text;
    message.classList.toggle("is-error", error);
  };

  const reset = () => {
    login.hidden = false;
    mfa.hidden = true;
    login.reset();
    mfa.reset();
    showMessage("");
  };

  const open = () => {
    card.hidden = false;
    opener.setAttribute("aria-expanded", "true");
    card.querySelector("input").focus();
  };

  const close = () => {
    card.hidden = true;
    opener.setAttribute("aria-expanded", "false");
    reset();
    opener.focus();
  };

  opener.addEventListener("click", open);
  closeButton.addEventListener("click", close);
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !card.hidden) close();
  });

  login.addEventListener("submit", async (event) => {
    event.preventDefault();
    showMessage("Verificando acesso...");
    try {
      const result = await FokusApi.request("/backoffice/auth/login", {
        method: "POST",
        body: Object.fromEntries(new FormData(login)),
      });
      if (!result?.mfa_required) throw new Error("Não foi possível iniciar a verificação por código.");
      login.hidden = true;
      mfa.hidden = false;
      showMessage(result.message || "Informe o código enviado ao seu e-mail.");
      mfa.querySelector("input").focus();
    } catch (error) {
      showMessage(error.message || "Não foi possível iniciar o acesso.", true);
    }
  });

  mfa.addEventListener("submit", async (event) => {
    event.preventDefault();
    showMessage("Validando código...");
    try {
      await FokusApi.request("/backoffice/auth/verify-mfa", {
        method: "POST",
        body: Object.fromEntries(new FormData(mfa)),
      });
      window.location.assign("/backoffice");
    } catch (error) {
      showMessage(error.message || "Código inválido.", true);
    }
  });

  if (new URLSearchParams(window.location.search).get("acesso") === "administrativo") {
    const url = new URL(window.location.href);
    url.searchParams.delete("acesso");
    window.history.replaceState({}, "", `${url.pathname}${url.search}${url.hash}`);
    window.requestAnimationFrame(open);
  }
})();
