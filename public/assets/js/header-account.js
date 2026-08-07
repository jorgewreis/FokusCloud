(() => {
  const userMenus = document.querySelectorAll("[data-header-user]");
  const contactButtons = document.querySelectorAll("[data-header-contact]");

  if (!userMenus.length) return;

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
      if (!visible && element.matches("[data-header-user]")) {
        window.setTimeout(() => {
          if (!element.classList.contains("is-visible")) element.hidden = true;
        }, 700);
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
        toggleVisibility(contactButtons, !name);
      })
      .catch(() => {});
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

  syncAccount();
  window.addEventListener("pageshow", syncAccount);
  window.addEventListener("focus", syncAccount);
  window.addEventListener("fokus:session-changed", syncAccount);
})();
