(() => {
  const copyButtons = document.querySelectorAll("[data-copy-target]");

  copyButtons.forEach((button) => {
    button.addEventListener("click", async () => {
      const code = document.querySelector(`#${button.dataset.copyTarget} code`);
      const status = button.closest(".layout-code-block")?.querySelector(".layout-copy-status");
      if (!code || !status) return;

      try {
        await navigator.clipboard.writeText(code.textContent);
        status.textContent = "Código copiado.";
      } catch {
        status.textContent = "Não foi possível copiar automaticamente. Selecione o código manualmente.";
      }
    });
  });

  const links = [...document.querySelectorAll(".styles-sidebar a[href^='#']")];
  const sections = links
    .map((link) => document.querySelector(link.getAttribute("href")))
    .filter(Boolean);

  const setActive = (id) => {
    links.forEach((link) => link.classList.toggle("is-active", link.getAttribute("href") === `#${id}`));
  };

  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver((entries) => {
      const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
      if (visible) setActive(visible.target.id);
    }, { rootMargin: "-18% 0px -62% 0px", threshold: [0, 0.2, 0.6] });
    sections.forEach((section) => observer.observe(section));
  }
})();
