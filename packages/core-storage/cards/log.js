/* Built-in storage package: log viewer labels and line metadata. */
const StorageLogCard = {
  manifest: Object.freeze({
    id: 'core-storage-log',
    locales: ['core-storage-log.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-log.last_line': 'Last line',
        'core-storage-log.empty': 'Empty',
        'core-storage-log.line_number': 'Line {{number, number}}: {{line}}',
        'core-storage-log.title': 'Log',
        'core-storage-log.matches_one': '1 match found',
        'core-storage-log.matches_many': '{{count, number}} matches found',
        'core-storage-log.lines_one': '1 line',
        'core-storage-log.lines_many': '{{count, number}} lines',
        'core-storage-log.page_back': 'page {{page, number}} back from the end',
        'core-storage-log.file_size': 'file is {{size}}',
        'core-storage-log.read_of_file': 'read {{scanned}} of it',
        'core-storage-log.search_truncated':
          'stopped searching before the start of the file — narrow the search or look at the file directly',
        'core-storage-log.beginning': 'this is the beginning of the file',
        'core-storage-log.no_matching_lines': 'No matching lines here.',
        'core-storage-log.search': 'Search',
        'core-storage-log.lines': 'Lines',
        'core-storage-log.newer': 'Newer',
        'core-storage-log.older': 'Older',
        'core-storage-log.search_placeholder': 'text to find',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/log.json',
      hi: 'packages/core-storage/locales/hi/log.json',
      ar: 'packages/core-storage/locales/ar/log.json',
      zh: 'packages/core-storage/locales/zh/log.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const fallback = this.manifest.catalogs.en[`core-storage-log.${key}`];
    return i18n.t(`core-storage-log.${key}`, values, fallback);
  },
};
Zogograph.cards.register(StorageLogCard);
