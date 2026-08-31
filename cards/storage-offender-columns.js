/* Core card package: offender-table core column labels. */
window.ZogographStorageOffenderColumnsCard = {
  manifest: Object.freeze({
    id: 'core-storage-offender-columns',
    locales: ['core-storage-offender-columns.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-offender-columns.path': 'Path',
        'core-storage-offender-columns.kind': 'Kind',
        'core-storage-offender-columns.directory': 'directory',
        'core-storage-offender-columns.file': 'file',
        'core-storage-offender-columns.category': 'Category',
        'core-storage-offender-columns.files': 'Files',
      }),
      es: Object.freeze({
        'core-storage-offender-columns.path': 'Ruta',
        'core-storage-offender-columns.kind': 'Tipo',
        'core-storage-offender-columns.directory': 'directorio',
        'core-storage-offender-columns.file': 'archivo',
        'core-storage-offender-columns.category': 'Categoría',
        'core-storage-offender-columns.files': 'Archivos',
      }),
      hi: Object.freeze({
        'core-storage-offender-columns.path': 'पथ',
        'core-storage-offender-columns.kind': 'प्रकार',
        'core-storage-offender-columns.directory': 'डायरेक्टरी',
        'core-storage-offender-columns.file': 'फ़ाइल',
        'core-storage-offender-columns.category': 'श्रेणी',
        'core-storage-offender-columns.files': 'फ़ाइलें',
      }),
      ar: Object.freeze({
        'core-storage-offender-columns.path': 'المسار',
        'core-storage-offender-columns.kind': 'النوع',
        'core-storage-offender-columns.directory': 'مجلد',
        'core-storage-offender-columns.file': 'ملف',
        'core-storage-offender-columns.category': 'الفئة',
        'core-storage-offender-columns.files': 'الملفات',
      }),
      zh: Object.freeze({
        'core-storage-offender-columns.path': '路径',
        'core-storage-offender-columns.kind': '类型',
        'core-storage-offender-columns.directory': '目录',
        'core-storage-offender-columns.file': '文件',
        'core-storage-offender-columns.category': '类别',
        'core-storage-offender-columns.files': '文件',
      }),
    }),
  }),
  render({ key }, { i18n }) {
    return i18n.t(
      `core-storage-offender-columns.${key}`,
      {},
      this.manifest.catalogs.en[`core-storage-offender-columns.${key}`],
    );
  },
};
