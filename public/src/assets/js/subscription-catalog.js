window.FokusCatalogReady = window.FokusApi.request(
  `/catalog/${document.body.dataset.product}`,
).then((catalog) => {
  if (!catalog || !Array.isArray(catalog.modules) || !Array.isArray(catalog.plans)) {
    throw new Error("Catálogo inválido");
  }
  window.FokusCatalog = { [document.body.dataset.product]: catalog };
  return catalog;
});
