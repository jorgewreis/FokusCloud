(() => {
  const userMenus = document.querySelectorAll("[data-header-user]");
  const clientAccessButtons = document.querySelectorAll("[data-header-client-access]");
  const contactButtons = document.querySelectorAll("[data-header-contact]");
  const backofficeAccessButtons = document.querySelectorAll("[data-header-backoffice-access]");

  if (!userMenus.length && !backofficeAccessButtons.length) return;

  const displayName = (name) => {
    const parts = (name || "").trim().split(/\s+/).filter(Boolean);
    if (parts.length < 2) return parts[0] || "";
    return `${parts[0]} ${parts[parts.length - 1]}`;
  };

  const maskedCpf = (cpf) => {
    const digits = String(cpf || "").replace(/\D/g, "");
    if (digits.length !== 11) return "Conta Fokus Cloud";
    return `CPF •••.•••.***-${digits.slice(-2)}`;
  };

  const toggleVisibility = (elements, visible) => {
    elements.forEach((element) => {
      if (visible && element.hidden) element.hidden = false;
      if (visible) element.style.display = "";
      requestAnimationFrame(() => element.classList.toggle("is-visible", visible));
      if (!visible) {
        window.setTimeout(() => {
          if (!element.classList.contains("is-visible")) element.hidden = true;
        }, 700);
      }
    });
  };

  const setupBackofficeAccess = () => {
    if (!backofficeAccessButtons.length) return;

    document.body.insertAdjacentHTML("beforeend", `
      <div class="access-modal" data-backoffice-modal hidden>
        <div class="access-modal-backdrop" data-backoffice-modal-close></div>
        <section class="access-modal-card" role="dialog" aria-modal="true" aria-labelledby="backoffice-modal-title">
          <button class="access-modal-close" data-backoffice-modal-close type="button" aria-label="Fechar acesso administrativo">×</button>
          <div class="card-heading">
            <p class="section-kicker">Acesso restrito</p>
            <h2 class="card-title" id="backoffice-modal-title">Backoffice</h2>
            <p class="card-description">Use exclusivamente suas credenciais internas da Fokus Cloud.</p>
          </div>
          <form data-backoffice-login>
            <label class="form-field"><span class="form-label">E-mail interno</span><input class="form-control field-size-md" name="email" type="email" autocomplete="username" required></label>
            <label class="form-field"><span class="form-label">Senha</span><input class="form-control field-size-md" name="password" type="password" autocomplete="current-password" required></label>
            <div class="login-actions"><button class="btn btn-green" type="submit">Continuar</button></div>
          </form>
          <form data-backoffice-mfa hidden>
            <div class="card-heading">
              <p class="section-kicker">Código enviado</p>
              <p class="card-description">Informe o código de seis dígitos enviado ao seu e-mail para concluir o acesso.</p>
            </div>
            <label class="form-field"><span class="form-label">Código</span><input class="form-control field-size-sm" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required></label>
            <div class="login-actions"><button class="btn btn-green" type="submit">Validar acesso</button></div>
          </form>
          <p class="form-message" data-backoffice-message role="status"></p>
        </section>
      </div>
    `);

    const modal = document.querySelector("[data-backoffice-modal]");
    const login = modal.querySelector("[data-backoffice-login]");
    const mfa = modal.querySelector("[data-backoffice-mfa]");
    const message = modal.querySelector("[data-backoffice-message]");
    let opener = null;
    let csrfToken = null;

    const request = async (path, body) => {
      if (!csrfToken) {
        const response = await fetch("/api/csrf-token", { credentials: "same-origin" });
        csrfToken = (await response.json()).token;
      }
      const response = await fetch(`/api${path}`, {
        method: "POST",
        credentials: "same-origin",
        headers: { Accept: "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
        body: JSON.stringify(body),
      });
      const payload = await response.json();
      if (!response.ok) throw new Error(payload?.message || "Não foi possível concluir a solicitação.");
      return payload;
    };

    const showMessage = (text, type = "") => {
      message.textContent = text;
      message.classList.toggle("is-error", type === "error");
      message.classList.toggle("is-success", type === "success");
    };

    const reset = () => {
      login.hidden = false;
      mfa.hidden = true;
      login.reset();
      mfa.reset();
      showMessage("");
    };

    const close = () => {
      modal.hidden = true;
      document.body.style.overflow = "";
      reset();
      opener?.focus();
    };

    const open = (button) => {
      opener = button;
      modal.hidden = false;
      document.body.style.overflow = "hidden";
      modal.querySelector("input[name=email]").focus();
    };

    backofficeAccessButtons.forEach((button) => button.addEventListener("click", (event) => {
      event.preventDefault();
      open(button);
    }));

    modal.querySelectorAll("[data-backoffice-modal-close]").forEach((button) => button.addEventListener("click", close));
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape" && !modal.hidden) close();
    });

    login.addEventListener("submit", async (event) => {
      event.preventDefault();
      try {
        const response = await request("/backoffice/auth/login", Object.fromEntries(new FormData(login)));
        if (!response.mfa_required) throw new Error("Não foi possível iniciar a verificação por código.");
        login.hidden = true;
        mfa.hidden = false;
        showMessage("");
        mfa.elements.code.focus();
      } catch (error) {
        showMessage(error.message, "error");
      }
    });

    mfa.addEventListener("submit", async (event) => {
      event.preventDefault();
      try {
        await request("/backoffice/auth/verify-mfa", Object.fromEntries(new FormData(mfa)));
        window.location.assign("/backoffice");
      } catch (error) {
        showMessage(error.message, "error");
      }
    });
  };

  const syncAccount = () => {
    fetch("/api/auth/me", {
      credentials: "same-origin",
      headers: { Accept: "application/json" },
    })
      .then((response) => (response.ok ? response.json() : null))
      .then((data) => {
        const name = displayName(data?.user?.name);
        userMenus.forEach((menu) => {
          const label = menu.querySelector("[data-header-user-name]");
          const accountDocument = menu.querySelector("[data-header-user-document]");
          if (label) label.textContent = name || "Conta";
          if (accountDocument) accountDocument.textContent = maskedCpf(data?.user?.cpf);
        });
        toggleVisibility(userMenus, Boolean(name));
        toggleVisibility(clientAccessButtons, !name);
        toggleVisibility(contactButtons, !name);
      })
      .catch(() => {
        toggleVisibility(userMenus, false);
        toggleVisibility(clientAccessButtons, true);
        toggleVisibility(contactButtons, true);
      });
  };

  userMenus.forEach((menu) => {
    const toggle = menu.querySelector("[data-header-user-toggle]");
    const options = menu.querySelector("[data-header-user-options]");
    const logout = menu.querySelector("[data-header-user-logout]");
    toggle?.addEventListener("click", () => {
      const open = options?.hidden;
      userMenus.forEach((other) => {
        const otherOptions = other.querySelector("[data-header-user-options]");
        other.classList.remove("is-open");
        if (otherOptions) otherOptions.hidden = true;
      });
      if (options) options.hidden = !open;
      menu.classList.toggle("is-open", Boolean(open));
      toggle.setAttribute("aria-expanded", String(Boolean(open)));
    });
    logout?.addEventListener("click", async () => {
      logout.disabled = true;
      try {
        const csrfResponse = await fetch("/api/csrf-token", { credentials: "same-origin" });
        const { token } = await csrfResponse.json();
        const response = await fetch("/api/auth/logout", { method: "POST", credentials: "same-origin", headers: { Accept: "application/json", "X-CSRF-TOKEN": token } });
        if (!response.ok) throw new Error("Não foi possível encerrar a sessão.");
        window.location.assign("/");
      } catch (_) {
        logout.disabled = false;
      }
    });
  });

  document.addEventListener("click", (event) => {
    if (event.target.closest("[data-header-user]")) return;
    userMenus.forEach((menu) => {
      const options = menu.querySelector("[data-header-user-options]");
      const toggle = menu.querySelector("[data-header-user-toggle]");
      menu.classList.remove("is-open");
      if (options) options.hidden = true;
      toggle?.setAttribute("aria-expanded", "false");
    });
  });

  setupBackofficeAccess();
  syncAccount();
  window.addEventListener("pageshow", syncAccount);
  window.addEventListener("focus", syncAccount);
  window.addEventListener("fokus:session-changed", syncAccount);
})();
