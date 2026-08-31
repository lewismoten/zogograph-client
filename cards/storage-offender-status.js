/* Core card package: offender-list status column labels. */
window.ZogographStorageOffenderStatusCard = {
  manifest: Object.freeze({
    id: 'core-storage-offender-status',
    locales: ['core-storage-offender-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-offender-status.what_happened': 'What happened',
        'core-storage-offender-status.first_seen': 'First seen',
        'core-storage-offender-status.gone': 'Gone',
        'core-storage-offender-status.new': 'New',
      }),
      es: Object.freeze({
        'core-storage-offender-status.what_happened': 'Qué ocurrió',
        'core-storage-offender-status.first_seen': 'Visto por primera vez',
        'core-storage-offender-status.gone': 'Desaparecido',
        'core-storage-offender-status.new': 'Nuevo',
      }),
      hi: Object.freeze({
        'core-storage-offender-status.what_happened': 'क्या हुआ',
        'core-storage-offender-status.first_seen': 'पहली बार देखा गया',
        'core-storage-offender-status.gone': 'गायब',
        'core-storage-offender-status.new': 'नया',
      }),
      ar: Object.freeze({
        'core-storage-offender-status.what_happened': 'ما الذي حدث',
        'core-storage-offender-status.first_seen': 'أول ظهور',
        'core-storage-offender-status.gone': 'اختفى',
        'core-storage-offender-status.new': 'جديد',
      }),
      zh: Object.freeze({
        'core-storage-offender-status.what_happened': '发生了什么',
        'core-storage-offender-status.first_seen': '首次发现',
        'core-storage-offender-status.gone': '已消失',
        'core-storage-offender-status.new': '新建',
      }),
    }),
  }),
  render({ key }, { i18n }) {
    const fallback =
      this.manifest.catalogs.en[`core-storage-offender-status.${key}`];
    return i18n.t(`core-storage-offender-status.${key}`, {}, fallback);
  },
};
