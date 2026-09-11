(function () {
    const consentKey = "fokus-analytics-consent";
    const queue = [];
    const load = async () => {
        if (window.gtag || localStorage.getItem(consentKey) !== "accepted") return;
        const response = await fetch("/api/analytics/config", { headers: { Accept: "application/json" } });
        const config = await response.json();
        if (!config.measurement_id) return;
        const script = document.createElement("script"); script.async = true; script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(config.measurement_id)}`; document.head.appendChild(script);
        window.dataLayer = window.dataLayer || []; window.gtag = function () { window.dataLayer.push(arguments); }; window.gtag("js", new Date()); window.gtag("config", config.measurement_id, { anonymize_ip: true }); queue.splice(0).forEach(([name, params]) => window.gtag("event", name, params));
    };
    window.FokusAnalytics = { track(name, params = {}) { if (window.gtag) window.gtag("event", name, params); else queue.push([name, params]); } };
    if (localStorage.getItem(consentKey) === "accepted") load();
    const banner = document.querySelector("#analytics-consent");
    if (banner && !localStorage.getItem(consentKey)) banner.hidden = false;
    document.querySelector("#accept-analytics")?.addEventListener("click", () => { localStorage.setItem(consentKey, "accepted"); banner.hidden = true; load(); });
    document.querySelector("#decline-analytics")?.addEventListener("click", () => { localStorage.setItem(consentKey, "declined"); banner.hidden = true; });
})();
