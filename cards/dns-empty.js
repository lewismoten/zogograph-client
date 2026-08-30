window.ZogographDnsEmptyCard = {
  manifest: Object.freeze({
    id: 'core-dns-empty',
    locales: ['core-dns-empty.'],
    helpers: ['target', 'note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-dns-empty.message': 'Nothing to look up.' }),
      es: Object.freeze({
        'core-dns-empty.message': 'No hay nada que consultar.',
      }),
      hi: Object.freeze({
        'core-dns-empty.message': 'देखने के लिए कुछ नहीं है।',
      }),
      ar: Object.freeze({
        'core-dns-empty.message': 'لا يوجد شيء للبحث عنه.',
      }),
      zh: Object.freeze({ 'core-dns-empty.message': '没有可查询的内容。' }),
    }),
  }),
  render(data, helpers) {
    if (data.rows.length) return;
    helpers.target.replaceChildren(
      helpers.note(
        helpers.i18n.t('core-dns-empty.message', {}, 'Nothing to look up.'),
      ),
    );
  },
};
