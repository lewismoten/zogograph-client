/* Core card package: log viewer labels and line metadata. */
window.ZogographStorageLogCard = {
  manifest: Object.freeze({
    id: 'core-storage-log',
    locales: ['core-storage-log.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-log.last_line': 'Last line',
        'core-storage-log.empty': 'Empty',
        'core-storage-log.line_number': 'Line {{number, number}}: {{line}}',
        'core-storage-log.title': 'Log',
      }),
      es: Object.freeze({
        'core-storage-log.last_line': 'Última línea',
        'core-storage-log.empty': 'Vacía',
        'core-storage-log.line_number': 'Línea {{number, number}}: {{line}}',
        'core-storage-log.title': 'Registro',
      }),
      hi: Object.freeze({
        'core-storage-log.last_line': 'अंतिम पंक्ति',
        'core-storage-log.empty': 'खाली',
        'core-storage-log.line_number': 'पंक्ति {{number, number}}: {{line}}',
        'core-storage-log.title': 'लॉग',
      }),
      ar: Object.freeze({
        'core-storage-log.last_line': 'آخر سطر',
        'core-storage-log.empty': 'فارغ',
        'core-storage-log.line_number': 'السطر {{number, number}}: {{line}}',
        'core-storage-log.title': 'السجل التشغيلي',
      }),
      zh: Object.freeze({
        'core-storage-log.last_line': '最后一行',
        'core-storage-log.empty': '空',
        'core-storage-log.line_number': '第 {{number, number}} 行：{{line}}',
        'core-storage-log.title': '日志',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const fallback = this.manifest.catalogs.en[`core-storage-log.${key}`];
    return i18n.t(`core-storage-log.${key}`, values, fallback);
  },
};
