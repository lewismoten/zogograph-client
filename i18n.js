/* Small dependency-free localization layer. Catalogs are inert JSON and can be
 * extended by signed packages only through the future package catalog bridge. */
const I18N_BASE = "en";
const I18N_LOCALES = ["en", "en-US", "en-GB", "es", "hi", "zh", "ar"];
const I18N_RTL = new Set(["ar"]);
const i18n = { locale: I18N_BASE, catalog: {}, fallback: {}, keyMode: false, extensions: {} };

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

function i18nFormat(value, kind, name, values) {
  try {
    if (kind === "number") return new Intl.NumberFormat(i18n.locale).format(Number(value));
    if (kind === "latin-number") return new Intl.NumberFormat(i18n.locale, { numberingSystem: "latn" }).format(Number(value));
    if (kind === "compact") return new Intl.NumberFormat(i18n.locale, { notation: "compact" }).format(Number(value));
    if (kind === "percent") return new Intl.NumberFormat(i18n.locale, { style: "percent" }).format(Number(value));
    if (kind === "unit") return new Intl.NumberFormat(i18n.locale, { style: "unit", unit: values[`${name}Unit`] || "byte", unitDisplay: values[`${name}UnitDisplay`] || "short" }).format(Number(value));
    if (kind === "currency") return new Intl.NumberFormat(i18n.locale, { style: "currency", currency: values[`${name}Currency`] || "USD" }).format(Number(value));
    if (kind === "date") return new Intl.DateTimeFormat(i18n.locale, { dateStyle: "medium" }).format(new Date(value));
    if (kind === "time") return new Intl.DateTimeFormat(i18n.locale, { timeStyle: "short" }).format(new Date(value));
    if (kind === "date-time") return new Intl.DateTimeFormat(i18n.locale, { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
  } catch { return String(value ?? ""); }
  return String(value ?? "");
}

function t(key, values = {}, fallbackText = "") {
  const fallback = i18n.fallback[key] || fallbackText || key;
  const text = i18n.catalog[key] || fallback;
  if (i18n.keyMode) return key;
  return text.replace(/\{\{([\w.-]+)(?:,\s*(number|latin-number|compact|percent|unit|currency|date|time|date-time))?\}\}/g, (_, name, kind) => i18nFormat(values[name], kind, name, values));
}

async function i18nLoad(locale = i18nRequestedLocale()) {
  const get = async name => { const r = await fetch(`./locales/${name}.json`, { cache: "no-store" }); return r.ok ? r.json() : {}; };
  i18n.fallback = await get(I18N_BASE);
  i18n.catalog = { ...i18n.fallback, ...(locale === I18N_BASE ? {} : await get(locale)), ...(i18n.extensions[I18N_BASE] || {}), ...(i18n.extensions[locale] || {}) };
  i18n.locale = locale; document.documentElement.lang = locale;
  document.documentElement.dir = I18N_RTL.has(locale.split("-")[0]) ? "rtl" : "ltr";
  document.querySelectorAll("[data-i18n]").forEach(node => {
    const key = node.dataset.i18n; node.textContent = t(key); node.title = i18n.keyMode ? i18n.fallback[key] || key : "";
  });
  for (const [attribute, data] of [["placeholder", "i18nPlaceholder"], ["title", "i18nTitle"], ["aria-label", "i18nAriaLabel"]]) {
    document.querySelectorAll(`[data-${data.replace(/[A-Z]/g, letter => `-${letter.toLowerCase()}`)}]`).forEach(node => {
      const key = node.dataset[data]; node.setAttribute(attribute, t(key));
      node.title = i18n.keyMode ? i18n.fallback[key] || key : "";
    });
  }
  const picker = document.querySelector("#languagePicker"); if (picker) picker.value = locale;
  document.dispatchEvent(new Event("zogograph-i18n-load"));
}

function i18nRegisterCatalog(locale, catalog, reload = true) {
  if (!/^[a-z]{2,3}(?:-[A-Z]{2})?$/.test(locale) || !catalog || Array.isArray(catalog) || typeof catalog !== "object") return false;
  const clean = {};
  for (const [key, value] of Object.entries(catalog)) if (/^[a-z][\w.-]{0,119}$/i.test(key) && typeof value === "string" && value.length <= 4000) clean[key] = value;
  i18n.extensions[locale] = { ...(i18n.extensions[locale] || {}), ...clean };
  if (!I18N_LOCALES.includes(locale)) I18N_LOCALES.push(locale);
  const picker = document.querySelector("#languagePicker");
  if (picker && ![...picker.options].some(option => option.value === locale)) { const option = document.createElement("option"); option.value = locale; option.textContent = locale; picker.appendChild(option); }
  if (reload && (locale === i18n.locale || locale === I18N_BASE)) i18nLoad(i18n.locale);
  return true;
}
let i18nPackageCatalogsLoaded = false;
async function i18nLoadPackageCatalogs() {
  if (i18nPackageCatalogsLoaded) return;
  try {
    const response = await fetch("./api.php?action=package-i18n", { cache: "no-store", credentials: "same-origin" }); if (!response.ok) return;
    const data = await response.json(); let changed = false;
    for (const catalogs of Object.values(data.catalogs || {})) for (const [locale, catalog] of Object.entries(catalogs || {})) changed = i18nRegisterCatalog(locale, catalog, false) || changed;
    i18nPackageCatalogsLoaded = true; if (changed) await i18nLoad(i18n.locale);
  } catch { /* Package translations are optional and cannot block the shell. */ }
}
window.ZogographI18n = { t, load: i18nLoad, loadPackageCatalogs: i18nLoadPackageCatalogs, registerCatalog: i18nRegisterCatalog, setKeyMode: async enabled => { i18n.keyMode = enabled; await i18nLoad(i18n.locale); } };
document.addEventListener("DOMContentLoaded", () => {
  document.querySelector("#languagePicker")?.addEventListener("change", event => { localStorage.setItem("zogograph.locale", event.target.value); i18nLoad(event.target.value); });
  document.querySelector("#languageKeys")?.addEventListener("change", event => ZogographI18n.setKeyMode(event.target.checked));
  i18nLoad();
});
