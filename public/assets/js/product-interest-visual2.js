(function () {
    const stateField = document.querySelector("#interest-state");
    if (!stateField || stateField.tagName !== "INPUT") return;
    const states = [["AC", "Acre"], ["AL", "Alagoas"], ["AP", "Amapá"], ["AM", "Amazonas"], ["BA", "Bahia"], ["CE", "Ceará"], ["DF", "Distrito Federal"], ["ES", "Espírito Santo"], ["GO", "Goiás"], ["MA", "Maranhão"], ["MT", "Mato Grosso"], ["MS", "Mato Grosso do Sul"], ["MG", "Minas Gerais"], ["PA", "Pará"], ["PB", "Paraíba"], ["PR", "Paraná"], ["PE", "Pernambuco"], ["PI", "Piauí"], ["RJ", "Rio de Janeiro"], ["RN", "Rio Grande do Norte"], ["RS", "Rio Grande do Sul"], ["RO", "Rondônia"], ["RR", "Roraima"], ["SC", "Santa Catarina"], ["SP", "São Paulo"], ["SE", "Sergipe"], ["TO", "Tocantins"]];
    const select = document.createElement("select");
    select.id = stateField.id;
    select.name = stateField.name;
    select.innerHTML = '<option value="">Selecione</option>' + states.map(([value, label]) => `<option value="${value}">${label}</option>`).join("");
    stateField.replaceWith(select);
})();
