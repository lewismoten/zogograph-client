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
      }),
      es: Object.freeze({
        'core-symlink-sweep-table-labels.why_not': 'Por qué no',
        'core-symlink-sweep-table-labels.pointed_at': 'Apuntaba a',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-table-labels.why_not': 'क्यों नहीं',
        'core-symlink-sweep-table-labels.pointed_at': 'इंगित था',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-table-labels.why_not': 'لماذا لا',
        'core-symlink-sweep-table-labels.pointed_at': 'كان يشير إلى',
      }),
      zh: Object.freeze({
        'core-symlink-sweep-table-labels.why_not': '原因',
        'core-symlink-sweep-table-labels.pointed_at': '原指向',
      }),
    }),
  }),
  render(input, helpers) {
    const { label } = input;
    const { i18n } = helpers;
    const fallbacks = { pointed_at: 'Pointed at', why_not: 'Why not' };
    return i18n.t(
      `core-symlink-sweep-table-labels.${label}`,
      {},
      fallbacks[label],
    );
  },
};
