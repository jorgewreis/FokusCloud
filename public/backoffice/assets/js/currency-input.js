(function (window, document) {
    "use strict";

    const formatter = new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: "BRL",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    function parse(value) {
        const raw = String(value ?? "").trim();

        if (!raw) {
            return null;
        }

        const compact = raw.replace(/[^\d,.-]/g, "");

        if (!compact) {
            return null;
        }

        const comma = compact.lastIndexOf(",");
        const dot = compact.lastIndexOf(".");
        const normalized = comma > dot
            ? compact.replace(/\./g, "").replace(",", ".")
            : compact.replace(/,/g, "");
        const number = Number(normalized);

        return Number.isFinite(number) ? Number(number.toFixed(2)) : null;
    }

    function format(value) {
        const number = typeof value === "number" ? value : parse(value);

        return number === null || !Number.isFinite(number) ? "" : formatter.format(number);
    }

    function mask(field) {
        const digits = String(field.value ?? "").replace(/\D/g, "");

        field.value = digits ? formatter.format(Number(digits) / 100) : "";
    }

    function bind(root = document) {
        root.querySelectorAll?.("[data-currency-input]").forEach((field) => {
            if (field.dataset.currencyBound === "true") {
                return;
            }

            field.dataset.currencyBound = "true";
            field.value = format(field.value);
            field.addEventListener("input", () => mask(field));
        });
    }

    window.FokusCurrency = { bind, format, parse };
    bind(document);
})(window, document);
