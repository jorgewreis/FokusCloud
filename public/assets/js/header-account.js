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
          menu.replaceChildren(
            new Option(name || "Conta", ""),
            new Option("Meu painel", "/portal"),
            new Option("Sair", "logout"),
          );
          menu.selectedIndex = 0;
          menu.hidden = !name;
          menu.style.display = name ? "inline-flex" : "none";
        });
        contactButtons.forEach((button) => {
          button.hidden = Boolean(name);
          button.style.display = name ? "none" : "";
        });
      })
      .catch(() => {});
  };

  userMenus.forEach((menu) => {
    menu.onchange = async () => {
      const action = menu.value;
      menu.selectedIndex = 0;
      if (action === "/portal") return window.location.assign(action);
      if (action !== "logout") return;
      menu.disabled = true;
      try {
        const csrfResponse = await fetch("/api/csrf-token", { credentials: "same-origin" });
        const { token } = await csrfResponse.json();
        const response = await fetch("/api/auth/logout", { method: "POST", credentials: "same-origin", headers: { Accept: "application/json", "X-CSRF-TOKEN": token } });
        if (!response.ok) throw new Error("Não foi possível encerrar a sessão.");
        window.location.assign("/");
      } catch (_) {
        menu.disabled = false;
      }
    };
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
