(function () {
    const page = document.querySelector(".product-page");
    const form = document.querySelector("#product-interest-form");
    if (!page || !form) return;
    const product = page.dataset.product;
    const modules = {
        law: ["Contatos", "Processos", "Tarefas", "Audiências", "Expedientes"],
        lead: ["Clientes", "Leads", "Imóveis", "Empreendimentos", "Funil", "Relatório de Transação Imobiliária", "Relatórios de Gestão"],
    };
    const profiles = {
        law: ["Advocacia", "Cartório", "Órgão público", "Departamento jurídico", "Outro"],
        lead: ["Corretor autônomo", "Grupo ou equipe", "Imobiliária", "Outro"],
    };
    const productFields = document.querySelector("#interest-products");
    const profileFields = document.querySelector("#interest-profiles");
    const moduleFields = document.querySelector("#interest-modules");
    const feedback = document.querySelector("#product-form-feedback");
    const stateField = document.querySelector("#interest-state");
    const states = [["AC", "Acre"], ["AL", "Alagoas"], ["AP", "Amapá"], ["AM", "Amazonas"], ["BA", "Bahia"], ["CE", "Ceará"], ["DF", "Distrito Federal"], ["ES", "Espírito Santo"], ["GO", "Goiás"], ["MA", "Maranhão"], ["MT", "Mato Grosso"], ["MS", "Mato Grosso do Sul"], ["MG", "Minas Gerais"], ["PA", "Pará"], ["PB", "Paraíba"], ["PR", "Paraná"], ["PE", "Pernambuco"], ["PI", "Piauí"], ["RJ", "Rio de Janeiro"], ["RN", "Rio Grande do Norte"], ["RS", "Rio Grande do Sul"], ["RO", "Rondônia"], ["RR", "Roraima"], ["SC", "Santa Catarina"], ["SP", "São Paulo"], ["SE", "Sergipe"], ["TO", "Tocantins"]];
    if (stateField && stateField.tagName === "INPUT") {
        const select = document.createElement("select");
        select.id = stateField.id;
        select.name = stateField.name;
        select.innerHTML = '<option value="">Selecione</option>' + states.map(([value, label]) => `<option value="${value}">${label}</option>`).join("");
        stateField.replaceWith(select);
    }
    const selected = (name) => [...form.querySelectorAll(`[name=\"${name}\"]:checked`)].map((field) => field.value);
    const renderOptions = () => {
        const selectedProducts = selected("products[]");
        const activeProducts = selectedProducts.length ? selectedProducts : [product];
        profileFields.innerHTML = activeProducts.flatMap((key) => profiles[key].map((label) => `<label class="check-option"><input type="checkbox" name="profiles[]" value="${label}"> <span>${label}</span></label>`)).join("");
        moduleFields.innerHTML = activeProducts.flatMap((key) => modules[key].map((label) => `<label class="check-option"><input type="checkbox" name="modules[]" value="${label}"> <span>${label}</span></label>`)).join("");
    };
    productFields.querySelector(`input[value="${product}"]`).checked = true;
    productFields.addEventListener("change", renderOptions);
    renderOptions();
    const track = (event, params = {}) => window.FokusAnalytics?.track(event, { product, ...params });
    document.querySelectorAll('[data-analytics="interest_cta_click"]').forEach((link) => link.addEventListener("click", () => track("interest_cta_click")));
    form.addEventListener("focusin", () => { if (!form.dataset.started) { form.dataset.started = "true"; track("interest_form_start"); } }, { once: true });
    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        feedback.className = "product-form-feedback";
        feedback.textContent = "Enviando suas informações...";
        const button = form.querySelector("button[type=submit]");
        button.disabled = true;
        const payload = Object.fromEntries(new FormData(form).entries());
        payload.products = selected("products[]");
        payload.profiles = selected("profiles[]");
        payload.modules = selected("modules[]");
        try {
            const csrf = await fetch("/api/csrf-token", { credentials: "same-origin" }).then((response) => response.json());
            const response = await fetch("/api/product-interests", { method: "POST", headers: { "Accept": "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": csrf.token }, credentials: "same-origin", body: JSON.stringify(payload) });
            const body = await response.json();
            if (!response.ok) throw new Error(body.message || Object.values(body.errors || {}).flat()[0] || "Revise os campos e tente novamente.");
            feedback.className = "product-form-feedback is-success";
            feedback.textContent = "Interesse registrado. Obrigado por ajudar a orientar a evolução do produto; manteremos suas informações para contato enquanto houver essa finalidade.";
            form.reset(); productFields.querySelector(`input[value="${product}"]`).checked = true; renderOptions(); track("interest_form_submit_success", { products: payload.products });
        } catch (error) { feedback.className = "product-form-feedback is-error"; feedback.textContent = error.message; track("interest_form_submit_error"); }
        button.disabled = false;
    });
})();
