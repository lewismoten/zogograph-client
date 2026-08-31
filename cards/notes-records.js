/* Core card package: localized labels for the saved-notes table. */
window.ZogographNotesRecordsCard = {
  manifest: Object.freeze({
    id: 'core-notes-records',
    locales: ['core-notes-records.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-notes-records.table_time': 'Time',
        'core-notes-records.table_icons': 'Icons',
        'core-notes-records.table_note': 'Note',
        'core-notes-records.table_size': 'Size',
        'core-notes-records.somebody': 'somebody',
      }),
      es: Object.freeze({
        'core-notes-records.table_time': 'Hora',
        'core-notes-records.table_icons': 'Iconos',
        'core-notes-records.table_note': 'Nota',
        'core-notes-records.table_size': 'Tamaño',
        'core-notes-records.somebody': 'alguien',
      }),
      hi: Object.freeze({
        'core-notes-records.table_time': 'समय',
        'core-notes-records.table_icons': 'चिह्न',
        'core-notes-records.table_note': 'नोट',
        'core-notes-records.table_size': 'आकार',
        'core-notes-records.somebody': 'किसी ने',
      }),
      ar: Object.freeze({
        'core-notes-records.table_time': 'الوقت',
        'core-notes-records.table_icons': 'الأيقونات',
        'core-notes-records.table_note': 'ملاحظة',
        'core-notes-records.table_size': 'الحجم',
        'core-notes-records.somebody': 'شخص ما',
      }),
      zh: Object.freeze({
        'core-notes-records.table_time': '时间',
        'core-notes-records.table_icons': '图标',
        'core-notes-records.table_note': '注释',
        'core-notes-records.table_size': '大小',
        'core-notes-records.somebody': '某人',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-notes-records.${key}`,
      values,
      this.manifest.catalogs.en[`core-notes-records.${key}`],
    );
  },
  render() {},
};
