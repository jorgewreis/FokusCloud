(() => {
    const storageKey = "fokus-styles-theme";
    const root = document.documentElement;
    const validThemes = new Set(["light", "dark"]);

    const readTheme = () => {
        try {
            const savedTheme = localStorage.getItem(storageKey);
            return validThemes.has(savedTheme) ? savedTheme : (root.dataset.theme || "dark");
        } catch {
            return root.dataset.theme || "dark";
        }
    };

    const applyTheme = (theme) => {
        root.dataset.theme = theme;
        root.style.colorScheme = theme;
        document.querySelectorAll("[data-theme-toggle]").forEach((toggle) => {
            const nextTheme = theme === "dark" ? "light" : "dark";
            toggle.setAttribute("aria-label", `Ativar tema ${nextTheme === "light" ? "claro" : "escuro"}`);
            toggle.setAttribute("title", `Ativar tema ${nextTheme === "light" ? "claro" : "escuro"}`);
            toggle.setAttribute("aria-pressed", String(theme === "dark"));
        });
    };

    applyTheme(readTheme());

    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll("[data-theme-toggle]").forEach((toggle) => {
            toggle.addEventListener("click", () => {
                const nextTheme = root.dataset.theme === "dark" ? "light" : "dark";
                try { localStorage.setItem(storageKey, nextTheme); } catch { /* theme still changes for this page */ }
                applyTheme(nextTheme);
            });
        });
        applyTheme(root.dataset.theme);
    }, { once: true });
})();
