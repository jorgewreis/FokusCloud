(() => {
  const adminButtons = document.querySelectorAll("[data-header-admin]");
  const userMenus = document.querySelectorAll("[data-header-user]");
  const contactButtons = document.querySelectorAll("[data-header-contact]");

  if (!adminButtons.length || !userMenus.length) return;

  const displayName = (name) => {
    const parts = (name || "").trim().split(/\s+/).filter(Boolean);
    if (parts.length < 2) return parts[0] || "";
    return `${parts[0]} ${parts[parts.length - 1]}`;
  };

  const syncAccount = () => {
    fetch("/api/auth/me", {
      credentials: "same-origin",
      headers: { Accept: "application/json" },
    })
      .then((response) => (response.ok ? response.json() : null))
      .then((data) => {
        const name = displayName(data?.user?.name);
        adminButtons.forEach((button) => {
          button.hidden = Boolean(name);
          button.style.display = name ? "none" : "";
        });
        userMenus.forEach((menu) => {
          const label = menu.querySelector("[data-header-user-name]");
          if (label) label.textContent = name || "Conta";
          menu.hidden = !name;
          menu.style.display = name ? "block" : "none";
        });
        contactButtons.forEach((button) => {
          button.hidden = Boolean(name);
          button.style.display = name ? "none" : "";
        });
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
        if (otherOptions) otherOptions.hidden = true;
      });
      if (options) options.hidden = !open;
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
      if (options) options.hidden = true;
      toggle?.setAttribute("aria-expanded", "false");
    });
  });

  adminButtons.forEach((button) => {
    button.textContent = "Minha conta";
    button.onclick = () => window.location.assign("/acesso");
  });

  syncAccount();
  window.addEventListener("pageshow", syncAccount);
  window.addEventListener("focus", syncAccount);
  window.addEventListener("fokus:session-changed", syncAccount);
})();
