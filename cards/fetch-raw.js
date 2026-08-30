window.ZogographFetchRawCard = {
  manifest: Object.freeze({
    id: 'core-fetch-raw',
    locales: ['core-fetch-raw.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-fetch-raw.empty': '(no body)' }),
      es: Object.freeze({ 'core-fetch-raw.empty': '(sin cuerpo)' }),
      hi: Object.freeze({ 'core-fetch-raw.empty': '(कोई मुख्य भाग नहीं)' }),
      ar: Object.freeze({ 'core-fetch-raw.empty': '(لا متن)' }),
      zh: Object.freeze({ 'core-fetch-raw.empty': '（无正文）' }),
    }),
  }),
  render(data, helpers) {
    const pre = document.createElement('pre');
    pre.className = 'paste raw-body';
    // textContent, never innerHTML: this is somebody else's page and putting
    // it into this document as markup would hand it the dashboard's session.
    pre.textContent =
      data.body || helpers.i18n.t('core-fetch-raw.empty', {}, '(no body)');
    return pre;
  },
};
