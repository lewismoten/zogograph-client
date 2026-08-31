/* Core card package: individual path deletion state and result notices. */
window.ZogographStorageDeleteResultsCard = {
  manifest: Object.freeze({
    id: 'core-storage-delete-results',
    locales: ['core-storage-delete-results.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-delete-results.deleted': 'deleted',
        'core-storage-delete-results.deleted_row_help':
          'Deleted {{at}}. Still listed because the last scan found it; the next scan will drop it.',
        'core-storage-delete-results.deleted_not_freed':
          'Deleted, but nothing was freed: {{links}} still point at those {{size}}.',
        'core-storage-delete-results.removed_directory':
          'Removed {{size}} in {{files}}.',
        'core-storage-delete-results.removed_directory_with_skipped':
          'Removed {{size}} in {{files}}. {{skipped}} could not be removed.',
        'core-storage-delete-results.removed_file': 'Removed {{size}}.',
      }),
      es: Object.freeze({
        'core-storage-delete-results.deleted': 'eliminado',
        'core-storage-delete-results.deleted_row_help':
          'Se eliminó {{at}}. Aún aparece porque lo encontró el último análisis; el siguiente lo quitará.',
        'core-storage-delete-results.deleted_not_freed':
          'Se eliminó, pero no se liberó espacio: {{links}} aún apuntan a esos {{size}}.',
        'core-storage-delete-results.removed_directory':
          'Se eliminaron {{size}} en {{files}}.',
        'core-storage-delete-results.removed_directory_with_skipped':
          'Se eliminaron {{size}} en {{files}}. No se pudieron eliminar {{skipped}}.',
        'core-storage-delete-results.removed_file': 'Se eliminaron {{size}}.',
      }),
      hi: Object.freeze({
        'core-storage-delete-results.deleted': 'हटाई गई',
        'core-storage-delete-results.deleted_row_help':
          '{{at}} को हटाई गई। यह अभी भी सूची में है क्योंकि पिछले स्कैन में मिली थी; अगला स्कैन इसे हटा देगा।',
        'core-storage-delete-results.deleted_not_freed':
          'हटा दिया गया, लेकिन कोई जगह खाली नहीं हुई: {{links}} अभी भी उन {{size}} की ओर इशारा करते हैं।',
        'core-storage-delete-results.removed_directory':
          '{{files}} में {{size}} हटाया गया।',
        'core-storage-delete-results.removed_directory_with_skipped':
          '{{files}} में {{size}} हटाया गया। {{skipped}} हटाया नहीं जा सका।',
        'core-storage-delete-results.removed_file': '{{size}} हटाया गया।',
      }),
      ar: Object.freeze({
        'core-storage-delete-results.deleted': 'محذوف',
        'core-storage-delete-results.deleted_row_help':
          'حُذف {{at}}. ما زال مُدرجًا لأن آخر فحص وجده؛ وسيزيله الفحص التالي.',
        'core-storage-delete-results.deleted_not_freed':
          'حُذف، لكن لم تُفرغ مساحة: ما زال {{links}} يشير إلى {{size}}.',
        'core-storage-delete-results.removed_directory':
          'أُزيل {{size}} في {{files}}.',
        'core-storage-delete-results.removed_directory_with_skipped':
          'أُزيل {{size}} في {{files}}. تعذر إزالة {{skipped}}.',
        'core-storage-delete-results.removed_file': 'أُزيل {{size}}.',
      }),
      zh: Object.freeze({
        'core-storage-delete-results.deleted': '已删除',
        'core-storage-delete-results.deleted_row_help':
          '已于 {{at}} 删除。它仍在列表中，因为上次扫描找到了它；下次扫描会将其移除。',
        'core-storage-delete-results.deleted_not_freed':
          '已删除，但未释放空间：{{links}} 仍指向这 {{size}}。',
        'core-storage-delete-results.removed_directory':
          '已删除 {{files}} 个文件中的 {{size}}。',
        'core-storage-delete-results.removed_directory_with_skipped':
          '已删除 {{files}} 个文件中的 {{size}}。无法删除 {{skipped}}。',
        'core-storage-delete-results.removed_file': '已删除 {{size}}。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const fallback =
      this.manifest.catalogs.en[`core-storage-delete-results.${key}`];
    return i18n.t(`core-storage-delete-results.${key}`, values, fallback);
  },
};
