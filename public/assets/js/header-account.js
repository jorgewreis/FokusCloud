(() => {
  const adminButtons = document.querySelectorAll("[data-header-admin]");
  const userLabels = document.querySelectorAll("[data-header-user]");
  const contactButtons = document.querySelectorAll("[data-header-contact]");
  const logoutButtons = document.querySelectorAll("[data-header-logout]");

  if (!adminButtons.length || !userLabels.length) return;

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
        userLabels.forEach((label) => {
          label.textContent = name;
          label.hidden = !name;
          label.style.display = name ? "flex" : "none";
        });
        contactButtons.forEach((button) => {
          button.hidden = Boolean(name);
          button.style.display = name ? "none" : "";
        });
        logoutButtons.forEach((button) => {
          button.hidden = !name;
          button.style.display = name ? "flex" : "none";
        });
      })
      .catch(() => {});
  };

  logoutButtons.forEach((button) => {
    button.onclick = async () => {
      button.disabled = true;
      try {
        const csrfResponse = await fetch("/api/csrf-token", { credentials: "same-origin" });
        const { token } = await csrfResponse.json();
        const response = await fetch("/api/auth/logout", {
          method: "POST",
          credentials: "same-origin",
          headers: { Accept: "application/json", "X-CSRF-TOKEN": token },
        });
        if (!response.ok) throw new Error("Não foi possível encerrar a sessão.");
        window.location.assign("/");
      } catch (_) {
        button.disabled = false;
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
