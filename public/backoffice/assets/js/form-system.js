(function (window, document) {
    "use strict";

    const controls = "input, select, textarea";
    const requiredControls = "input[required], select[required], textarea[required]";

    function getField(form, name) {
        return form.querySelector(`[name="${CSS.escape(name)}"]`);
    }

    function feedbackId(field) {
        return `${field.id || field.name}-error`;
    }

    function clearField(field) {
        document.getElementById(feedbackId(field))?.remove();
        field.removeAttribute("aria-invalid");
        field.removeAttribute("aria-describedby");
        field.closest(".form-field")?.classList.remove("is-invalid", "is-valid");
    }

    function setFeedback(field, message, type = "error") {
        if (!field) return;
        clearField(field);
        const feedback = document.createElement("small");
        feedback.id = feedbackId(field);
        feedback.className = `form-${type}`;
        feedback.setAttribute("role", type === "error" ? "alert" : "status");
        feedback.textContent = message;
        field.insertAdjacentElement("afterend", feedback);
        field.setAttribute("aria-invalid", type === "error" ? "true" : "false");
        field.setAttribute("aria-describedby", feedback.id);
        field.closest(".form-field")?.classList.add(type === "error" ? "is-invalid" : "is-valid");
    }

    function clearSummary(form) { form.querySelector(".form-error-summary")?.remove(); }

    function renderSummary(form, errors) {
        clearSummary(form);
        const entries = Object.entries(errors).filter(([, message]) => message);
        if (!entries.length) return;
        const summary = document.createElement("div");
        summary.className = "form-error-summary";
        summary.setAttribute("role", "alert");
        summary.innerHTML = "<strong>Revise os campos destacados.</strong>";
        const list = document.createElement("ul");
        entries.forEach(([name, message]) => {
            const field = getField(form, name);
            const item = document.createElement("li");
            const link = document.createElement("a");
            link.href = field?.id ? `#${field.id}` : "#";
            link.textContent = message;
            item.appendChild(link);
            list.appendChild(item);
        });
        summary.appendChild(list);
        form.prepend(summary);
    }

    function validate(form) {
        const errors = {};
        form.classList.add("is-validated");
        form.querySelectorAll(controls).forEach((field) => {
            clearField(field);
            if (!field.checkValidity()) {
                errors[field.name || field.id] = field.validationMessage;
                setFeedback(field, field.validationMessage);
            }
        });
        renderSummary(form, errors);
        form.querySelector('[aria-invalid="true"]')?.focus();
        return Object.keys(errors).length === 0;
    }

    function mapServerErrors(form, errors) {
        const normalized = {};
        Object.entries(errors || {}).forEach(([name, messages]) => {
            const message = Array.isArray(messages) ? messages[0] : messages;
            if (!message) return;
            normalized[name] = message;
            setFeedback(getField(form, name), message);
        });
        renderSummary(form, normalized);
        form.querySelector('[aria-invalid="true"]')?.focus();
    }

    function setLoading(form, loading, button = form.querySelector("button[type=submit]")) {
        form.toggleAttribute("aria-busy", loading);
        button?.classList.toggle("is-loading", loading);
        if (button) button.disabled = loading;
    }

    function clear(form) {
        clearSummary(form);
        form.classList.remove("is-validated");
        form.querySelectorAll(controls).forEach(clearField);
    }

    function markRequiredFields(root = document) {
        const fields = root.matches?.(".form-field")
            ? [root, ...root.querySelectorAll(".form-field")]
            : [...root.querySelectorAll(".form-field")];

        fields.forEach((field) => {
            const required = field.querySelector(requiredControls);

            if (!required) {
                return;
            }

            const label = required.id
                ? field.querySelector(`label[for="${CSS.escape(required.id)}"]`)
                : field.querySelector("label, legend, .form-label");

            if (!label) {
                return;
            }

            label.classList.add("form-label-required");
            label.setAttribute("data-required", "true");
        });
    }

    function enhance(root = document) {
        markRequiredFields(root);
    }

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType !== Node.ELEMENT_NODE) {
                    return;
                }

                if (node.matches?.(".form-field, form, .fc-form")) {
                    enhance(node);
                    return;
                }

                if (node.querySelector?.(".form-field")) {
                    enhance(node);
                }
            });
        });
    });

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => enhance(document), { once: true });
    } else {
        enhance(document);
    }

    observer.observe(document.documentElement, { childList: true, subtree: true });

    window.FokusForm = { clear, clearField, enhance, mapServerErrors, markRequiredFields, setFeedback, setLoading, validate };
})(window, document);
