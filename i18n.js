/* Small dependency-free localization layer. Catalogs are inert JSON and can be
 * extended by signed packages only through the future package catalog bridge. */
const I18N_BASE = 'en';
const I18N_DEFAULT_BRAND = 'Zogograph';
const I18N_LOCALES = ['en', 'en-US', 'en-GB', 'es', 'hi', 'zh', 'ar'];
const I18N_RTL = new Set(['ar']);
const i18n = {
  locale: I18N_BASE,
  catalog: {},
  fallback: {},
  keyMode: false,
  highlight: localStorage.getItem('zogograph.i18n-highlight') === '1',
  extensions: {},
};
let i18nBrand = String(
  window.ZogographBrand ||
    document.querySelector('[data-brand]')?.textContent ||
    I18N_DEFAULT_BRAND,
).trim();

function i18nSetBrand(name) {
  const candidate = String(name || '').trim();
  // eslint-disable-next-line no-control-regex -- rejects unprintable brand names.
  if (!candidate || candidate.length > 80 || /[\x00-\x1f\x7f]/.test(candidate))
    return;
  i18nBrand = candidate;
  document.querySelectorAll('[data-brand]').forEach((node) => {
    node.textContent = candidate;
  });
  document.querySelectorAll('[data-brand-template]').forEach((node) => {
    const text = String(node.dataset.brandTemplate || '').replaceAll(
      '{{brand}}',
      candidate,
    );
    if (node.tagName === 'META') node.setAttribute('content', text);
    else node.textContent = text;
  });
  document
    .querySelector('meta[name="application-name"]')
    ?.setAttribute('content', candidate);
}

function i18nRequestedLocale() {
  const saved = localStorage.getItem('zogograph.locale');
  const choices = saved
    ? [saved]
    : navigator.languages || [navigator.language || I18N_BASE];
  for (const choice of choices) {
    const normal = String(choice).replace('_', '-');
    if (I18N_LOCALES.includes(normal)) return normal;
    const base = normal.split('-')[0];
    if (I18N_LOCALES.includes(base)) return base;
  }
  return I18N_BASE;
}

function i18nFormat(value, kind, name, values) {
  try {
    if (kind === 'number')
      return new Intl.NumberFormat(i18n.locale).format(Number(value));
    if (kind === 'latin-number')
      return new Intl.NumberFormat(i18n.locale, {
        numberingSystem: 'latn',
      }).format(Number(value));
    // Arabic content can require either Arabic-Indic (٠١٢) or Eastern
    // Arabic-Indic (۰۱۲) digits regardless of the surrounding locale. Keep
    // these explicit; `number` remains the ordinary locale-native choice.
    if (kind === 'arabic-number')
      return new Intl.NumberFormat(i18n.locale, {
        numberingSystem: 'arab',
      }).format(Number(value));
    if (kind === 'eastern-arabic-number')
      return new Intl.NumberFormat(i18n.locale, {
        numberingSystem: 'arabext',
      }).format(Number(value));
    if (kind === 'compact')
      return new Intl.NumberFormat(i18n.locale, { notation: 'compact' }).format(
        Number(value),
      );
    if (kind === 'percent')
      return new Intl.NumberFormat(i18n.locale, { style: 'percent' }).format(
        Number(value),
      );
    if (kind === 'unit')
      return new Intl.NumberFormat(i18n.locale, {
        style: 'unit',
        unit: values[`${name}Unit`] || 'byte',
        unitDisplay: values[`${name}UnitDisplay`] || 'short',
      }).format(Number(value));
    if (kind === 'currency')
      return new Intl.NumberFormat(i18n.locale, {
        style: 'currency',
        currency: values[`${name}Currency`] || 'USD',
      }).format(Number(value));
    if (kind === 'date')
      return new Intl.DateTimeFormat(i18n.locale, {
        dateStyle: 'medium',
      }).format(new Date(value));
    if (kind === 'time')
      return new Intl.DateTimeFormat(i18n.locale, {
        timeStyle: 'short',
      }).format(new Date(value));
    if (kind === 'date-time')
      return new Intl.DateTimeFormat(i18n.locale, {
        dateStyle: 'medium',
        timeStyle: 'short',
      }).format(new Date(value));
  } catch {
    return String(value ?? '');
  }
  return String(value ?? '');
}

function t(key, values = {}, fallbackText = '') {
  const fallback = i18n.fallback[key] || fallbackText || key;
  // Existing core catalogs predate configurable branding and contain the
  // default display name in ordinary prose. Treat that literal like the
  // {{brand}} token at the single rendering boundary; protocol names, paths,
  // and server-side identifiers never enter this catalog path.
  const text = (i18n.catalog[key] || fallback).replaceAll(
    I18N_DEFAULT_BRAND,
    '{{brand}}',
  );
  if (i18n.keyMode) return key;
  const allValues = { brand: i18nBrand, ...values };
  return text.replace(
    /\{\{([\w.-]+)(?:,\s*(number|latin-number|arabic-number|eastern-arabic-number|compact|percent|unit|currency|date|time|date-time))?\}\}/g,
    (_, name, kind) => i18nFormat(allValues[name], kind, name, allValues),
  );
}

function i18nMarkSource(node, key, fallback = '') {
  node.dataset.i18n = key;
  node.dataset.i18nFallback = fallback;
  node.classList.toggle('i18n-catalog', key in i18n.catalog);
  node.classList.toggle('i18n-hardcoded', !(key in i18n.catalog));
  return t(key, {}, fallback);
}

function i18nNodeValues(node) {
  const raw = node.dataset.i18nValues;
  if (!raw) return {};
  try {
    const values = JSON.parse(raw);
    return values && !Array.isArray(values) && typeof values === 'object'
      ? values
      : {};
  } catch {
    return {};
  }
}

/* Rich locale strings are intentionally narrow: catalogs provide text and
 * ordering only, while the document provides every element that can be
 * inserted. A translator can move {{name}} tokens, but can never introduce a
 * link, executable markup, or an unsafe URL. */
function i18nApplyRichNode(node) {
  if (!node._i18nRich) {
    const tags = {};
    for (const child of node.querySelectorAll('[data-i18n-tag]')) {
      const name = child.dataset.i18nTag;
      if (!/^[a-z][\w-]{0,31}$/i.test(name || '') || tags[name]) continue;
      tags[name] = child.cloneNode(true);
      child.replaceWith(document.createTextNode(`{{${name}}}`));
    }
    node._i18nRich = { tags, fallback: node.textContent };
  }
  const { tags, fallback } = node._i18nRich;
  const values = {};
  const markers = {};
  Object.keys(tags).forEach((name, index) => {
    const marker = `\uE000${index}\uE001`;
    values[name] = marker;
    markers[marker] = name;
  });
  const rendered = t(node.dataset.i18nRich, values, fallback);
  node.replaceChildren();
  const parts = rendered.split(/(\uE000\d+\uE001)/g);
  for (const part of parts) {
    const name = markers[part];
    node.append(
      name ? tags[name].cloneNode(true) : document.createTextNode(part),
    );
  }
  node.title = i18n.keyMode
    ? i18n.fallback[node.dataset.i18nRich] || fallback || node.dataset.i18nRich
    : '';
  node.classList.toggle('i18n-catalog', node.dataset.i18nRich in i18n.catalog);
  node.classList.toggle(
    'i18n-hardcoded',
    !(node.dataset.i18nRich in i18n.catalog),
  );
}

async function i18nLoad(locale = i18nRequestedLocale()) {
  const get = async (name) => {
    const r = await fetch(`./locales/${name}.json`, { cache: 'no-store' });
    return r.ok ? r.json() : {};
  };
  i18n.fallback = await get(I18N_BASE);
  const localized = locale === I18N_BASE ? {} : await get(locale);
  const catalog = { ...i18n.fallback, ...localized };
  // Package catalogs are translation extensions, not authority to rewrite the
  // shell. In particular, a package must not be able to change security,
  // consent, navigation, or action text that is supplied by the core locale.
  // A locale-specific package value can still replace that package's English
  // fallback because both remain absent from the core catalog.
  const extensions = {
    ...(i18n.extensions[I18N_BASE] || {}),
    ...(i18n.extensions[locale] || {}),
  };
  for (const [key, value] of Object.entries(extensions))
    if (!(key in catalog)) catalog[key] = value;
  i18n.catalog = catalog;
  i18n.locale = locale;
  document.documentElement.lang = locale;
  document.documentElement.dir = I18N_RTL.has(locale.split('-')[0])
    ? 'rtl'
    : 'ltr';
  document.querySelectorAll('[data-i18n-rich]').forEach(i18nApplyRichNode);
  document.querySelectorAll('[data-i18n]').forEach((node) => {
    const key = node.dataset.i18n;
    const fallback = node.dataset.i18nFallback ?? node.textContent;
    node.dataset.i18nFallback = fallback;
    node.textContent = t(key, i18nNodeValues(node), fallback);
    node.title = i18n.keyMode ? i18n.fallback[key] || fallback || key : '';
    node.classList.toggle('i18n-catalog', key in catalog);
    node.classList.toggle('i18n-hardcoded', !(key in catalog));
  });
  for (const [attribute, data] of [
    ['placeholder', 'i18nPlaceholder'],
    ['title', 'i18nTitle'],
    ['aria-label', 'i18nAriaLabel'],
    ['alt', 'i18nAlt'],
  ]) {
    document
      .querySelectorAll(
        `[data-${data.replace(/[A-Z]/g, (letter) => `-${letter.toLowerCase()}`)}]`,
      )
      .forEach((node) => {
        const key = node.dataset[data];
        node.setAttribute(attribute, t(key));
        node.title = i18n.keyMode ? i18n.fallback[key] || key : '';
      });
  }
  const picker = document.querySelector('#languagePicker');
  if (picker) picker.value = locale;
  document.body.classList.toggle('i18n-highlight', i18n.highlight);
  const highlight = document.querySelector('#languageHighlight');
  if (highlight) highlight.checked = i18n.highlight;
  document.dispatchEvent(new Event('zogograph-i18n-load'));
}

function i18nRegisterCatalog(locale, catalog, reload = true, packageId = '') {
  if (
    !/^[a-z]{2,3}(?:-[A-Z]{2})?$/.test(locale) ||
    !catalog ||
    Array.isArray(catalog) ||
    typeof catalog !== 'object'
  )
    return false;
  const prefix = packageId === '' ? '' : `${packageId}.`;
  if (packageId !== '' && !/^[a-z][a-z0-9-]{2,62}$/.test(packageId))
    return false;
  const clean = {};
  // A package can extend only its own namespace. This mirrors the server-side
  // catalog validation, so a malformed API response cannot shadow shell text.
  for (const [key, value] of Object.entries(catalog))
    if (
      (prefix === '' || key.startsWith(prefix)) &&
      /^[a-z][\w.-]{0,119}$/i.test(key) &&
      typeof value === 'string' &&
      value.length <= 4000
    )
      clean[key] = value;
  i18n.extensions[locale] = { ...(i18n.extensions[locale] || {}), ...clean };
  // Core card packages register their inert catalogs before a render. Merge
  // the current language immediately as well, so the very first card render
  // uses it; a later locale change still rebuilds the complete catalog from
  // extensions in the normal way.
  if (!reload && (locale === I18N_BASE || locale === i18n.locale))
    Object.assign(i18n.catalog, clean);
  if (!I18N_LOCALES.includes(locale)) I18N_LOCALES.push(locale);
  const picker = document.querySelector('#languagePicker');
  if (
    picker &&
    ![...picker.options].some((option) => option.value === locale)
  ) {
    const option = document.createElement('option');
    option.value = locale;
    option.textContent = locale;
    picker.appendChild(option);
  }
  if (reload && (locale === i18n.locale || locale === I18N_BASE))
    i18nLoad(i18n.locale);
  return true;
}
let i18nPackageCatalogsLoaded = false;
async function i18nLoadPackageCatalogs(force = false) {
  if (i18nPackageCatalogsLoaded && !force) return;
  try {
    const response = await fetch('./api.php?action=package-i18n', {
      cache: 'no-store',
      credentials: 'same-origin',
    });
    if (!response.ok) return;
    const data = await response.json();
    let changed = false;
    // The endpoint exposes only enabled packages. A forced lifecycle refresh
    // therefore replaces, rather than accumulates, package-owned text so a
    // disabled or removed package cannot keep contributing to the shell.
    if (force) i18n.extensions = {};
    for (const [packageId, catalogs] of Object.entries(data.catalogs || {}))
      for (const [locale, catalog] of Object.entries(catalogs || {}))
        changed =
          i18nRegisterCatalog(locale, catalog, false, packageId) || changed;
    i18nPackageCatalogsLoaded = true;
    if (changed || force) await i18nLoad(i18n.locale);
  } catch {
    /* Package translations are optional and cannot block the shell. */
  }
}
function i18nClearPackageCatalogs() {
  if (!i18nPackageCatalogsLoaded && Object.keys(i18n.extensions).length === 0)
    return;
  i18nPackageCatalogsLoaded = false;
  i18n.extensions = {};
  i18nLoad(i18n.locale);
}

function i18nRegisterCoreCatalogs() {
  for (const cardPackage of [window.ZogographStorageLogCard]) {
    const manifest = cardPackage?.manifest;
    if (
      !manifest ||
      typeof manifest.id !== 'string' ||
      !manifest.catalogs ||
      typeof manifest.catalogs !== 'object'
    )
      continue;
    for (const [locale, catalog] of Object.entries(manifest.catalogs))
      i18nRegisterCatalog(locale, catalog, false, manifest.id);
  }
}

window.ZogographI18n = {
  t,
  markSource: i18nMarkSource,
  applyRich: i18nApplyRichNode,
  load: i18nLoad,
  loadPackageCatalogs: i18nLoadPackageCatalogs,
  clearPackageCatalogs: i18nClearPackageCatalogs,
  registerCatalog: i18nRegisterCatalog,
  setBrand: i18nSetBrand,
  setKeyMode: async (enabled) => {
    i18n.keyMode = enabled;
    await i18nLoad(i18n.locale);
  },
  setHighlight: async (enabled) => {
    i18n.highlight = enabled;
    localStorage.setItem('zogograph.i18n-highlight', enabled ? '1' : '0');
    await i18nLoad(i18n.locale);
  },
};
document.addEventListener('DOMContentLoaded', () => {
  // Static shell nodes translate during the first locale load. Register the
  // core cards that own such nodes before that pass, rather than keeping a
  // duplicate root-catalog key solely for the initial render.
  i18nRegisterCoreCatalogs();
  document
    .querySelector('#languagePicker')
    ?.addEventListener('change', (event) => {
      localStorage.setItem('zogograph.locale', event.target.value);
      i18nLoad(event.target.value);
    });
  document
    .querySelector('#languageKeys')
    ?.addEventListener('change', (event) =>
      ZogographI18n.setKeyMode(event.target.checked),
    );
  document
    .querySelector('#languageHighlight')
    ?.addEventListener('change', (event) =>
      ZogographI18n.setHighlight(event.target.checked),
    );
  i18nLoad();
});
