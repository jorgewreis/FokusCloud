function normalizePublishedCatalog(catalog) {
  if (!catalog || catalog.contract_version !== "0.0.3") {
    throw new Error("Catálogo inválido");
  }

  const modules = (catalog.modules || []).map((module) => [
    module.code,
    module.name,
    module.description || module.commercial_content || module.technical_description || "",
    Number(module.monthly_amount || 0),
    module.usage || null,
    module.module_code,
    module.segment_code,
    module.context_code,
    module.variant_code,
    module.capabilities || [],
    module.dependencies || [],
    module.incompatibilities || [],
  ]);

  const plans = (catalog.plans || []).map((plan) => [
    plan.name,
    plan.module_codes || [],
    plan.code,
    Number(plan.monthly_amount || 0),
    Number(plan.annual_amount || 0),
  ]);

  if (!modules.length || !plans.length) {
    throw new Error("Catálogo indisponível");
  }

  return {
    contract_version: catalog.contract_version,
    published_version: catalog.published_version,
    published_at: catalog.published_at,
    name: catalog.name || catalog.product?.name,
    back: catalog.back,
    modules,
    plans,
  };
}

window.FokusCatalogReady = window.FokusApi.request(
  `/catalog/${document.body.dataset.product}`,
).then((catalog) => {
  const normalized = normalizePublishedCatalog(catalog);
  window.FokusCatalog = { [document.body.dataset.product]: normalized };
  return normalized;
});
