/* Core card package: offender-table size and hard-link context. */
window.ZogographStorageOffenderSizeCard = {
  manifest: Object.freeze({
    id: 'core-storage-offender-size',
    locales: ['core-storage-offender-size.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-offender-size.last_seen': 'Size when last seen',
        'core-storage-offender-size.link_count': ' · {{count, number}} links',
        'core-storage-offender-size.link_count_help':
          'This file has {{count, number}} names. Removing one frees nothing until the last one goes.',
      }),
      es: Object.freeze({
        'core-storage-offender-size.last_seen':
          'Tamaño al verse por última vez',
        'core-storage-offender-size.link_count': ' · {{count, number}} enlaces',
        'core-storage-offender-size.link_count_help':
          'Este archivo tiene {{count, number}} nombres. Quitar uno no libera nada hasta que se quite el último.',
      }),
      hi: Object.freeze({
        'core-storage-offender-size.last_seen': 'अंतिम बार दिखने पर आकार',
        'core-storage-offender-size.link_count': ' · {{count, number}} लिंक',
        'core-storage-offender-size.link_count_help':
          'इस फ़ाइल के {{count, number}} नाम हैं। अंतिम नाम हटने तक एक को हटाने से कुछ नहीं खाली होता।',
      }),
      ar: Object.freeze({
        'core-storage-offender-size.last_seen': 'الحجم عند آخر ظهور',
        'core-storage-offender-size.link_count': ' · {{count, number}} روابط',
        'core-storage-offender-size.link_count_help':
          'لهذا الملف {{count, number}} أسماء. لا يحرر حذف واحد مساحة حتى يزول الأخير.',
      }),
      zh: Object.freeze({
        'core-storage-offender-size.last_seen': '上次看到时的大小',
        'core-storage-offender-size.link_count': ' · {{count, number}} 个链接',
        'core-storage-offender-size.link_count_help':
          '此文件有 {{count, number}} 个名称。删除一个不会释放空间，直到最后一个也被删除。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-storage-offender-size.${key}`,
      values,
      this.manifest.catalogs.en[`core-storage-offender-size.${key}`],
    );
  },
};
