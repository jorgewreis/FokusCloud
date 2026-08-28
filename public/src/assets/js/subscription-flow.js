(() => {
  let catalog;
  const money = (value) =>
    value.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  const state = {
    step: 1,
    mode: "modules",
    cycle: "monthly",
    items: [],
    plan: null,
    account: "register",
    pendingCompany: null,
    usage: {
      oficios: 2500,
      partes: 5000,
      pessoas: 50,
      empreendimentos: 20,
      imoveis: 200,
      relatorios: 500,
    },
  };
  const root = document.querySelector("#subscription-app");
  const moduleById = (id) => catalog.modules.find((item) => item[0] === id);
  const usageFee = () =>
    state.items.reduce((sum, id) => {
      const config = moduleById(id)[4];
      return (
        sum +
        (config
          ? config.options.indexOf(state.usage[id] || config.options[0]) *
            config.step
          : 0)
      );
    }, 0);
  const monthly = () =>
    (state.items.reduce((sum, id) => sum + moduleById(id)[3], 0) + usageFee()) *
    (state.mode === "plan" ? 0.9 : 1);
  const annual = () => monthly() * 10;
  const period = () => (state.cycle === "annual" ? "ano" : "mês");
  const amount = () => (state.cycle === "annual" ? annual() : monthly());
  const subscriptionPath = () =>
    document.body.dataset.product === "lead"
      ? "/assinaturas/fokus-lead"
      : "/assinaturas/fokus-law";
  const digits = (value) => value.replace(/\D/g, "");
  const repeatedDigits = (value) => /^(\d)\1+$/.test(value);
  const validCpf = (value) => {
    const cpf = digits(value);
    if (cpf.length !== 11 || repeatedDigits(cpf)) return false;
    const digit = (length) => {
      const sum = cpf
        .slice(0, length)
        .split("")
        .reduce((total, number, index) => total + Number(number) * (length + 1 - index), 0);
      const remainder = (sum * 10) % 11;
      return remainder === 10 ? 0 : remainder;
    };
    return digit(9) === Number(cpf[9]) && digit(10) === Number(cpf[10]);
  };
  const validCnpj = (value) => {
    const cnpj = digits(value);
    if (cnpj.length !== 14 || repeatedDigits(cnpj)) return false;
    const digit = (length) => {
      let factor = length === 12 ? 5 : 6;
      const sum = cnpj.slice(0, length).split("").reduce((total, number) => {
        const result = total + Number(number) * factor;
        factor = factor === 2 ? 9 : factor - 1;
        return result;
      }, 0);
      const remainder = sum % 11;
      return remainder < 2 ? 0 : 11 - remainder;
    };
    return digit(12) === Number(cnpj[12]) && digit(13) === Number(cnpj[13]);
  };

  function accountView() {
    const isRegister = state.account === "register";
    const content = isRegister
      ? `<p>Cadastre a empresa e o administrador responsável. Após confirmar o e-mail, você retorna para concluir esta assinatura.</p><form id="subscription-register-form"><label class="form-field"><span class="form-label">Tipo de documento</span><select class="field-size-md" name="document_type" id="subscription-document-type"><option value="cnpj">CNPJ</option><option value="cpf">CPF</option></select></label><label class="form-field"><span class="form-label">CPF ou CNPJ</span><input class="field-size-md" name="document_number" id="subscription-company-document" inputmode="numeric" required><span class="field-error" id="subscription-company-document-error" role="alert"></span></label><label class="form-field"><span class="form-label" id="subscription-company-name-label">Razão social</span><input class="field-size-md" name="legal_name" required></label><label class="form-field"><span class="form-label">Nome completo do administrador</span><input class="field-size-md" name="name" autocomplete="name" required></label><label class="form-field"><span class="form-label">CPF do administrador</span><input class="field-size-md" name="cpf" id="subscription-admin-cpf" inputmode="numeric" autocomplete="username" required><span class="field-error" id="subscription-admin-cpf-error" role="alert"></span></label><label class="form-field"><span class="form-label">E-mail profissional</span><input class="field-size-md" name="email" type="email" autocomplete="email" required></label><label class="form-field"><span class="form-label">Senha</span><input class="field-size-md" name="password" type="password" autocomplete="new-password" minlength="12" required></label><label class="consent-field"><input type="checkbox" name="terms" required><span>Li e aceito os Termos de Uso.</span></label><label class="consent-field"><input type="checkbox" name="privacy" required><span>Li e aceito a Política de Privacidade.</span></label></form><div class="login-actions"><button class="btn btn-green" form="subscription-register-form" type="submit">Criar conta e continuar</button></div><p id="account-feedback" class="form-message" role="status"></p><small>Já tem conta? Use a aba <b>Entrar</b> para continuar com uma empresa existente.</small>`
      : `<p>Entre com CPF e senha para assinar este produto usando uma empresa já cadastrada.</p><form id="subscription-login-form"><label class="form-field"><span class="form-label">CPF</span><input class="field-size-md" id="subscription-login-cpf" name="cpf" inputmode="numeric" autocomplete="username" required><span class="field-error" id="subscription-login-cpf-error" role="alert"></span></label><label class="form-field"><span class="form-label">Senha</span><input class="field-size-md" name="password" type="password" autocomplete="current-password" required></label></form><div class="login-actions"><button class="btn btn-green" form="subscription-login-form" type="submit">Entrar e continuar</button></div><p id="account-feedback" class="form-message" role="status"></p><small>Se sua conta possuir mais de uma empresa, você escolherá qual delas será usada antes de continuar.</small>`;
    return `<div class="auth-card"><div class="tabs"><button data-account="register" class="${isRegister ? "selected" : ""}">Criar conta da empresa</button><button data-account="login" class="${!isRegister ? "selected" : ""}">Entrar</button></div>${content}</div>`;
  }
  function selectionView() {
    let options =
      state.mode === "modules"
        ? catalog.modules
            .map(
              (module) =>
                `<label class="module-choice"><input type="checkbox" value="${module[0]}" ${state.items.includes(module[0]) ? "checked" : ""}><span><b>${module[1]}</b><small>${module[2]}</small></span><strong>${money(state.cycle === "annual" ? module[3] * 10 : module[3])}/${period()}</strong></label>`,
            )
            .join("")
        : catalog.plans
            .map((plan) => {
              const monthlyPlan = plan[3];
              const planAmount =
                state.cycle === "annual" ? plan[4] : monthlyPlan;
              return `<label class="choice-card ${state.plan === plan[2] ? "selected" : ""}"><input type="radio" name="plan" value="${plan[2]}" ${state.plan === plan[2] ? "checked" : ""}><b>${plan[0]}</b><span>${plan[1].map((id) => moduleById(id)[1]).join(" · ")}</span><strong>${money(planAmount)}/${period()}</strong></label>`;
            })
            .join("");
    const limits = state.items
      .map((id) => {
        const item = moduleById(id),
          config = item[4];
        if (!config) return "";
        const current = state.usage[id] || config.options[0];
        return `<label class="usage-control"><span><b>${item[1]}</b><small>${config.label}</small></span><select data-usage="${id}">${config.options.map((value, index) => `<option value="${value}" ${value === current ? "selected" : ""}>${value.toLocaleString("pt-BR")} ${index ? `(+${money(index * config.step)}/mês)` : "(incluído)"}</option>`).join("")}</select></label>`;
      })
      .join("");
    options += limits
      ? `<div class="usage-controls"><p>Limites e upgrades de utilização</p>${limits}</div>`
      : "";
    const discountLabel =
      state.cycle === "annual"
        ? "Economia equivalente a 2 mensalidades"
        : state.mode === "plan"
          ? "Desconto total de 10% no conjunto"
          : "Selecione ao menos um módulo";
    return `<div class="selector"><div class="selector-top"><div><p class="eyebrow dark">Escolha sua assinatura</p><h2>Comece do seu jeito.</h2></div><div class="cycle"><button data-cycle="monthly" class="${state.cycle === "monthly" ? "selected" : ""}">Mensal</button><button data-cycle="annual" class="${state.cycle === "annual" ? "selected" : ""}">Anual</button></div></div><div class="mode-tabs"><button data-mode="modules" class="${state.mode === "modules" ? "selected" : ""}">Montar por módulos</button><button data-mode="plan" class="${state.mode === "plan" ? "selected" : ""}">Planos sugeridos</button></div><div class="choices">${options}</div><div class="selection-footer"><div><small>${discountLabel}</small><strong>${money(amount())}<em>/${period()}</em></strong></div><button class="btn btn-green" data-next ${state.items.length ? "" : "disabled"}>Revisar assinatura</button></div></div>`;
  }
  function reviewView() {
    const items = state.items
      .map((id) => {
        const item = moduleById(id),
          config = item[4];
        return `<li>${item[1]}${config ? ` — até ${(state.usage[id] || config.options[0]).toLocaleString("pt-BR")} ${config.summary || "itens"}` : ""}</li>`;
      })
      .join("");
    const annualNote =
      state.cycle === "annual"
        ? `<p class="review-saving">Economia equivalente a <b>2 mensalidades</b></p>`
        : "";
    return `<div class="review-card"><p class="eyebrow dark">Revisão do pedido</p><h2>Quase pronto.</h2><div class="review-grid"><div><small>PRODUTO</small><b>${catalog.name}</b></div><div><small>MODELO</small><b>${state.mode === "plan" ? `Plano ${state.plan}` : "Módulos personalizados"}</b></div><div><small>COBRANÇA</small><b>${state.cycle === "annual" ? "Anual" : "Mensal"}</b></div><div><small>TOTAL</small><b>${money(amount())} / ${period()}</b></div></div>${annualNote}<h3>Itens incluídos</h3><ul>${items}</ul><label class="form-field"><span class="form-label">Cupom de desconto (opcional)</span><input id="voucher-code" maxlength="64" autocomplete="off"></label><div class="review-actions"><button class="btn btn-outline" data-back>Voltar</button><button class="btn btn-green" type="button" data-checkout>Continuar para checkout</button></div><p id="checkout-feedback" role="status"></p><small class="payment-note">O checkout seguro é criado após a validação da empresa e do e-mail.</small></div>`;
  }
  function bind() {
    root.querySelectorAll("[data-account]").forEach(
      (button) =>
        (button.onclick = () => {
          state.account = button.dataset.account;
          render();
        }),
    );
    const registerForm = root.querySelector("#subscription-register-form");
    if (registerForm) {
      const documentType = root.querySelector("#subscription-document-type");
      const companyDocument = root.querySelector("#subscription-company-document");
      const adminCpf = root.querySelector("#subscription-admin-cpf");
      const companyNameLabel = root.querySelector("#subscription-company-name-label");
      const feedback = root.querySelector("#account-feedback");
      const validateDocument = (input, type, errorSelector) => {
        const error = root.querySelector(errorSelector);
        const value = digits(input.value);
        const valid = type === "cnpj" ? validCnpj(value) : validCpf(value);
        if (!value || valid) {
          input.value = value;
          input.removeAttribute("aria-invalid");
          error.textContent = "";
          error.classList.remove("is-visible");
          return true;
        }
        input.value = "";
        input.setAttribute("aria-invalid", "true");
        error.textContent = `${type.toUpperCase()} inválido. Confira os dígitos e informe novamente.`;
        error.classList.add("is-visible");
        return false;
      };
      documentType.onchange = () => {
        companyNameLabel.textContent = documentType.value === "cpf" ? "Nome completo" : "Razão social";
        companyDocument.value = "";
        companyDocument.removeAttribute("aria-invalid");
        root.querySelector("#subscription-company-document-error").classList.remove("is-visible");
      };
      companyDocument.onblur = () =>
        validateDocument(companyDocument, documentType.value, "#subscription-company-document-error");
      adminCpf.onblur = () =>
        validateDocument(adminCpf, "cpf", "#subscription-admin-cpf-error");
      registerForm.onsubmit = async (event) => {
        event.preventDefault();
        const companyValid = validateDocument(companyDocument, documentType.value, "#subscription-company-document-error");
        const cpfValid = validateDocument(adminCpf, "cpf", "#subscription-admin-cpf-error");
        if (!companyValid || !cpfValid) return;
        let values;
        try {
          values = Object.fromEntries(new FormData(registerForm));
          const payload = {
            ...values,
            terms_version: "1.0",
            privacy_version: "1.0",
            return_to: subscriptionPath(),
          };
          await FokusApi.request("/auth/register-company", {
            method: "POST",
            body: payload,
          });
          location.assign("/verificar-email");
        } catch (error) {
          if (error.message.includes("já possui conta")) {
            state.pendingCompany = {
              document_type: documentType.value,
              document_number: companyDocument.value,
              legal_name: values.legal_name,
              terms_version: "1.0",
              privacy_version: "1.0",
              return_to: subscriptionPath(),
            };
            state.account = "login";
            render();
          } else {
            feedback.textContent = error.message;
          }
        }
      };
    }
    const loginForm = root.querySelector("#subscription-login-form");
    if (loginForm) {
      const loginCpf = root.querySelector("#subscription-login-cpf");
      const loginCpfError = root.querySelector("#subscription-login-cpf-error");
      const validateLoginCpf = () => {
        const value = digits(loginCpf.value);
        if (validCpf(value)) {
          loginCpf.value = value;
          loginCpf.removeAttribute("aria-invalid");
          loginCpfError.textContent = "";
          loginCpfError.classList.remove("is-visible");
          return true;
        }
        loginCpf.value = "";
        loginCpf.setAttribute("aria-invalid", "true");
        loginCpfError.textContent = "CPF inválido. Confira os dígitos e informe novamente.";
        loginCpfError.classList.add("is-visible");
        return false;
      };
      loginCpf.onblur = () => {
        if (loginCpf.value.trim()) validateLoginCpf();
      };
      loginForm.onsubmit = async (event) => {
        event.preventDefault();
        const feedback = root.querySelector("#account-feedback");
        if (!validateLoginCpf()) return;
        try {
          const data = await FokusApi.request("/auth/login", {
            method: "POST",
            body: Object.fromEntries(new FormData(event.currentTarget)),
          });
          window.dispatchEvent(new Event("fokus:session-changed"));
          if (state.pendingCompany) {
            await FokusApi.request("/auth/register-company-for-current-user", {
              method: "POST",
              body: state.pendingCompany,
            });
            state.pendingCompany = null;
            if (!data.user.email_verified) {
              location.assign("/verificar-email");
              return;
            }
            state.step = 2;
            render();
          } else if (!data.user.email_verified) {
            location.href = "/verificar-email";
          } else if (!data.companies.length) {
            feedback.textContent = "Sua conta não possui uma empresa ativa. Solicite o vínculo ao administrador da empresa.";
          } else if (data.companies.length > 1) {
            location.assign(`/portal/empresas?retorno=${encodeURIComponent(subscriptionPath())}`);
          } else {
            state.step = 2;
            render();
          }
        } catch (error) {
          feedback.textContent = error.message;
        }
      };
    }
    root.querySelectorAll("[data-mode]").forEach(
      (button) =>
        (button.onclick = () => {
          state.mode = button.dataset.mode;
          state.items = [];
          state.plan = null;
          render();
        }),
    );
    root.querySelectorAll("[data-cycle]").forEach(
      (button) =>
        (button.onclick = () => {
          state.cycle = button.dataset.cycle;
          render();
        }),
    );
    root.querySelectorAll("input[type=checkbox]").forEach(
      (input) =>
        (input.onchange = () => {
          state.items = [
            ...root.querySelectorAll("input[type=checkbox]:checked"),
          ].map((item) => item.value);
          render();
        }),
    );
    root.querySelectorAll("input[name=plan]").forEach(
      (input) =>
        (input.onchange = () => {
          state.plan = input.value;
          state.items = catalog.plans.find((plan) => plan[2] === input.value)[1];
          render();
        }),
    );
    root.querySelectorAll("[data-usage]").forEach(
      (select) =>
        (select.onchange = () => {
          state.usage[select.dataset.usage] = Number(select.value);
          render();
        }),
    );
    root.querySelector("[data-next]")?.addEventListener("click", () => {
      state.step = 3;
      render();
    });
    root.querySelector("[data-back]")?.addEventListener("click", () => {
      state.step = 2;
      render();
    });
    const checkoutButton = root.querySelector("[data-checkout]");
    if (checkoutButton) {
      checkoutButton.onclick = async () => {
        const feedback = root.querySelector("#checkout-feedback");
        checkoutButton.disabled = true;
        try {
          const response = await FokusApi.request("/subscriptions/checkout", {
            method: "POST",
            body: {
              product_code: document.body.dataset.product,
              items: state.items.map((moduleCode) => ({
                module_code: moduleCode,
                quantity: 1,
                usage_limit: state.usage[moduleCode],
              })),
              cycle: state.cycle,
              selection_mode: state.mode,
              plan_code: state.plan,
              voucher_code: root.querySelector("#voucher-code")?.value.trim() || undefined,
            },
          });
          location.assign(response.checkout_url);
        } catch (error) {
          feedback.textContent = error.message === "Unauthenticated."
            ? "Sua sessão expirou. Entre novamente para continuar."
            : error.message;
          checkoutButton.disabled = false;
        }
      };
    }
  }
  function render() {
    const steps = ["Conta", "Assinatura", "Revisão"]
      .map(
        (label, index) =>
          `<span class="${state.step === index + 1 ? "active" : state.step > index + 1 ? "done" : ""}"><b>${index + 1}</b>${label}</span>`,
      )
      .join("");
    const content =
      state.step === 1
        ? accountView()
        : state.step === 2
          ? selectionView()
          : reviewView();
    root.innerHTML = `<div class="checkout-head"><a href="${catalog.back}">Voltar para ${catalog.name}</a><p>Assinatura independente</p><h1>${catalog.name}</h1><div class="checkout-steps">${steps}</div></div><div class="checkout-body">${content}</div>`;
    bind();
  }

  async function initialize() {
    root.innerHTML = `<div class="auth-card"><p>Preparando sua assinatura...</p></div>`;
    try {
      catalog = await window.FokusCatalogReady;
      const account = await FokusApi.request("/auth/me");
      if (!account.user.email_verified) {
        location.assign("/verificar-email");
        return;
      }
      if (!account.companies.length) {
        state.step = 1;
      } else if (account.companies.length > 1 && !account.active_company_id) {
        location.assign(`/portal/empresas?retorno=${encodeURIComponent(subscriptionPath())}`);
        return;
      } else {
        if (!account.active_company_id) {
          await FokusApi.request("/auth/select-company", {
            method: "POST",
            body: { company_id: account.companies[0].id },
          });
        }
        state.step = 2;
      }
    } catch (_) {
      root.innerHTML = `<div class="auth-card"><p>Não foi possível carregar o catálogo real. Tente novamente mais tarde.</p></div>`;
      return;
    }
    render();
  }

  initialize();
})();
