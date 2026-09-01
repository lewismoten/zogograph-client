/* Core card package: symlink-sweep result-table labels. */
window.ZogographSymlinkSweepTableLabelsCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sweep-table-labels',
    locales: ['core-symlink-sweep-table-labels.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sweep-table-labels.why_not': 'Why not',
        'core-symlink-sweep-table-labels.pointed_at': 'Pointed at',
        'core-symlink-sweep-table-labels.link': 'Link',
      }),
      es: Object.freeze({
        'core-symlink-sweep-table-labels.why_not': 'Por qué no',
        'core-symlink-sweep-table-labels.pointed_at': 'Apuntaba a',
        'core-symlink-sweep-table-labels.link': 'Enlace',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-table-labels.why_not': 'क्यों नहीं',
        'core-symlink-sweep-table-labels.pointed_at': 'इंगित था',
        'core-symlink-sweep-table-labels.link': 'लिंक',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-table-labels.why_not': 'لماذا لا',
        'core-symlink-sweep-table-labels.pointed_at': 'كان يشير إلى',
        'core-symlink-sweep-table-labels.link': 'الرابط',
      }),
      zh: Object.freeze({
        'core-symlink-sweep-table-labels.why_not': '原因',
        'core-symlink-sweep-table-labels.pointed_at': '原指向',
        'core-symlink-sweep-table-labels.link': '链接',
      }),
    }),
  }),
  render(input, helpers) {
    const { label } = input;
    const { i18n } = helpers;
    const fallbacks = {
      link: 'Link',
      pointed_at: 'Pointed at',
      why_not: 'Why not',
    };
    return i18n.t(
      `core-symlink-sweep-table-labels.${label}`,
      {},
      fallbacks[label],
    );
  },
};
