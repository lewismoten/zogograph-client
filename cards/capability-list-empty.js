window.ZogographCapabilityListEmptyCard = {
  manifest: Object.freeze({
    id: 'core-capability-list-empty',
    locales: ['core-capability-list-empty.'],
    helpers: ['target', 'note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-capability-list-empty.gap':
          'Everything in the catalogue is available here.',
        'core-capability-list-empty.default': 'Nothing matches.',
      }),
      es: Object.freeze({
        'core-capability-list-empty.gap':
          'Todo el catálogo está disponible aquí.',
        'core-capability-list-empty.default': 'Nada coincide.',
      }),
      hi: Object.freeze({
        'core-capability-list-empty.gap': 'कैटलॉग में सब कुछ यहाँ उपलब्ध है।',
        'core-capability-list-empty.default': 'कुछ भी मेल नहीं खाता।',
      }),
      ar: Object.freeze({
        'core-capability-list-empty.gap': 'كل ما في الفهرس متاح هنا.',
        'core-capability-list-empty.default': 'لا توجد نتائج مطابقة.',
      }),
      zh: Object.freeze({
        'core-capability-list-empty.gap': '目录中的所有内容在此均可用。',
        'core-capability-list-empty.default': '没有匹配项。',
      }),
    }),
  }),
  render(data, helpers) {
    helpers.target.replaceChildren(
      helpers.note(
        helpers.i18n.t(
          data.filter === 'gap'
            ? 'core-capability-list-empty.gap'
            : 'core-capability-list-empty.default',
          {},
          data.filter === 'gap'
            ? 'Everything in the catalogue is available here.'
            : 'Nothing matches.',
        ),
      ),
    );
  },
};
