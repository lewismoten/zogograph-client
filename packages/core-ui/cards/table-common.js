/* Shared table copy primitive. */
const TableCommonCard = {
  manifest: Object.freeze({
    id: 'core-table-common',
    locales: ['core-table-common.'],
    owner: 'core-ui',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-table-common.nothing_to_show': 'Nothing to show.',
        'core-table-common.download_rows':
          'Download {{count, number}} rows as {{format}}',
        'core-table-common.sort_by': 'Sort by {{label}}',
        'core-table-common.rows_total': '{{count, number}} rows',
        'core-table-common.rows_shown':
          'Showing {{shown, number}} of {{total, number}} rows',
        'core-table-common.show_more': 'Show {{count, number}} more',
        'core-table-common.size': 'Size',
        'core-table-common.change': 'Change',
        'core-table-common.name': 'Name',
        'core-table-common.value': 'Value',
        'core-table-common.header': 'Header',
        'core-table-common.says': 'Says',
        'core-table-common.domain': 'Domain',
        'core-table-common.means': 'Means',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-ui/locales/es/table-common.json',
      hi: 'packages/core-ui/locales/hi/table-common.json',
      ar: 'packages/core-ui/locales/ar/table-common.json',
      zh: 'packages/core-ui/locales/zh/table-common.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const cardId = 'core-table-common.' + key;
    return i18n.t(cardId, values, this.manifest.catalogs.en[cardId]);
  },
};
Zogograph.cards.register(TableCommonCard);
