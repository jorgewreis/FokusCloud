/* Lightweight, dependency-free behavior for the shared design system. */

const qs = (selector, root = document) => [...root.querySelectorAll(selector)];

const setExpanded = (trigger, expanded) => {
    trigger.setAttribute("aria-expanded", String(expanded));
};

function initCollapse() {
    qs("[data-collapse-target]").forEach((trigger) => {
        const panel = document.querySelector(trigger.dataset.collapseTarget);
        if (!panel) return;
        trigger.addEventListener("click", () => {
            const open = panel.hidden;
            panel.hidden = !open;
            setExpanded(trigger, open);
        });
    });
}

function initDropdowns() {
    qs("[data-dropdown-toggle]").forEach((trigger) => {
        const menu = document.querySelector(trigger.dataset.dropdownToggle);
        if (!menu) return;
        const close = () => { menu.hidden = true; setExpanded(trigger, false); };
        trigger.addEventListener("click", () => {
            const open = menu.hidden;
            qs("[data-dropdown-menu]:not([hidden])").forEach((item) => { item.hidden = true; });
            menu.hidden = !open;
            setExpanded(trigger, open);
        });
        menu.addEventListener("click", (event) => {
            if (event.target.closest(".dropdown-item")) close();
        });
        document.addEventListener("click", (event) => {
            if (!trigger.closest(".dropdown")?.contains(event.target)) close();
        });
        trigger.addEventListener("keydown", (event) => {
            if (event.key === "Escape") { close(); trigger.focus(); }
        });
    });
}

function initOffcanvas() {
    qs("[data-offcanvas-toggle]").forEach((trigger) => {
        const panel = document.querySelector(trigger.dataset.offcanvasToggle);
        if (!panel) return;
        const close = () => {
            panel.classList.remove("is-open");
            panel.setAttribute("aria-hidden", "true");
            document.body.classList.remove("has-offcanvas-open");
            setExpanded(trigger, false);
        };
        const open = () => {
            panel.classList.add("is-open");
            panel.setAttribute("aria-hidden", "false");
            document.body.classList.add("has-offcanvas-open");
            setExpanded(trigger, true);
        };
        trigger.addEventListener("click", () => panel.classList.contains("is-open") ? close() : open());
        qs("[data-offcanvas-close]", panel).forEach((button) => button.addEventListener("click", close));
        panel.addEventListener("keydown", (event) => { if (event.key === "Escape") close(); });
    });
}

function initTabs() {
    qs('[role="tablist"]').forEach((list) => {
        const tabs = qs('[role="tab"]', list);
        tabs.forEach((tab, index) => tab.addEventListener("click", () => {
            tabs.forEach((item) => item.setAttribute("aria-selected", String(item === tab)));
            const target = document.getElementById(tab.getAttribute("aria-controls"));
            if (target) target.hidden = false;
            tabs.filter((item) => item !== tab).forEach((item) => {
                const panel = document.getElementById(item.getAttribute("aria-controls"));
                if (panel) panel.hidden = true;
            });
        }));
        list.addEventListener("keydown", (event) => {
            const index = tabs.indexOf(document.activeElement);
            if (index < 0 || !["ArrowRight", "ArrowLeft", "Home", "End"].includes(event.key)) return;
            event.preventDefault();
            const next = event.key === "Home" ? 0 : event.key === "End" ? tabs.length - 1 : (index + (event.key === "ArrowRight" ? 1 : -1) + tabs.length) % tabs.length;
            tabs[next].focus();
        });
    });
}

function initToggles() {
    qs(".form-switch-input").forEach((input) => {
        const sync = () => input.closest(".form-switch")?.setAttribute("aria-checked", String(input.checked));
        input.addEventListener("change", sync);
        sync();
    });
}

function initRangeValues() {
    qs("[data-range-output]").forEach((range) => {
        const output = document.querySelector(range.dataset.rangeOutput);
        if (!output) return;
        const sync = () => { output.textContent = range.value; };
        range.addEventListener("input", sync);
        sync();
    });
}

function initValidation() {
    qs("form[data-validate]").forEach((form) => {
        form.addEventListener("submit", (event) => {
            form.classList.add("was-validated");
            const invalid = qs(":invalid", form);
            invalid.forEach((field) => field.setAttribute("aria-invalid", "true"));
            if (!form.checkValidity()) {
                event.preventDefault();
                invalid[0]?.focus();
            }
        });
        qs("input, select, textarea", form).forEach((field) => field.addEventListener("input", () => {
            if (field.checkValidity()) field.removeAttribute("aria-invalid");
        }));
    });
}

function init() {
    initCollapse();
    initDropdowns();
    initOffcanvas();
    initTabs();
    initToggles();
    initRangeValues();
    initValidation();
}

if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
else init();

export { init };
