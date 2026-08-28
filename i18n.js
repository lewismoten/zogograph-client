/* Small dependency-free localization layer. Catalogs are inert JSON and can be
 * extended by signed packages only through the future package catalog bridge. */
const I18N_BASE = "en";
const I18N_LOCALES = ["en", "en-US", "en-GB", "es", "hi", "zh", "ar"];
const I18N_RTL = new Set(["ar"]);
const i18n = { locale: I18N_BASE, catalog: {}, fallback: {}, keyMode: false };

function i18nRequestedLocale() {
  const saved = localStorage.getItem("zogograph.locale");
  const choices = saved ? [saved] : (navigator.languages || [navigator.language || I18N_BASE]);
  for (const choice of choices) {
    const normal = String(choice).replace("_", "-");
    if (I18N_LOCALES.includes(normal)) return normal;
    const base = normal.split("-")[0]; if (I18N_LOCALES.includes(base)) return base;
  }
  return I18N_BASE;
}

function i18nFormat(value, kind) {
  if (kind === "number") return new Intl.NumberFormat(i18n.locale).format(Number(value));
  if (kind === "date") return new Intl.DateTimeFormat(i18n.locale, { dateStyle: "medium" }).format(new Date(value));
  if (kind === "time") return new Intl.DateTimeFormat(i18n.locale, { timeStyle: "short" }).format(new Date(value));
  return String(value ?? "");
}

function t(key, values = {}) {
  const fallback = i18n.fallback[key] || key;
  const text = i18n.catalog[key] || fallback;
  if (i18n.keyMode) return key;
  return text.replace(/\{\{([\w.-]+)(?:,\s*(number|date|time))?\}\}/g, (_, name, kind) => i18nFormat(values[name], kind));
}

async function i18nLoad(locale = i18nRequestedLocale()) {
  const get = async name => { const r = await fetch(`./locales/${name}.json`, { cache: "no-store" }); return r.ok ? r.json() : {}; };
  i18n.fallback = await get(I18N_BASE);
  i18n.catalog = locale === I18N_BASE ? i18n.fallback : { ...i18n.fallback, ...await get(locale) };
  i18n.locale = locale; document.documentElement.lang = locale;
  document.documentElement.dir = I18N_RTL.has(locale.split("-")[0]) ? "rtl" : "ltr";
  document.querySelectorAll("[data-i18n]").forEach(node => {
    const key = node.dataset.i18n; node.textContent = t(key); node.title = i18n.keyMode ? i18n.fallback[key] || key : "";
  });
  const picker = document.querySelector("#languagePicker"); if (picker) picker.value = locale;
}

window.ZogographI18n = { t, load: i18nLoad, setKeyMode: async enabled => { i18n.keyMode = enabled; await i18nLoad(i18n.locale); } };
document.addEventListener("DOMContentLoaded", () => {
  document.querySelector("#languagePicker")?.addEventListener("change", event => { localStorage.setItem("zogograph.locale", event.target.value); i18nLoad(event.target.value); });
  document.querySelector("#languageKeys")?.addEventListener("change", event => ZogographI18n.setKeyMode(event.target.checked));
  i18nLoad();
});
