/* Core card package: status messages for account database maintenance. */
window.ZogographStorageOperationsCard = {
  manifest: Object.freeze({
    id: 'core-storage-operations',
    locales: ['core-storage-operations.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-operations.preparing_export':
          'Preparing the safe database export…',
        'core-storage-operations.rollup_result':
          '{{removed, number}} readings merged into {{compacted, number}}.',
        'core-storage-operations.rollup_protected':
          '{{count, number}} left at full detail because they sit beside an alert.',
        'core-storage-operations.rollup_space':
          'The space they used is inside the file until you reclaim it.',
      }),
      es: Object.freeze({
        'core-storage-operations.preparing_export':
          'Preparando la exportación segura de la base de datos…',
        'core-storage-operations.rollup_result':
          '{{removed, number}} lecturas se agruparon en {{compacted, number}}.',
        'core-storage-operations.rollup_protected':
          '{{count, number}} se mantienen con detalle completo porque están junto a una alerta.',
        'core-storage-operations.rollup_space':
          'El espacio que usaban permanece dentro del archivo hasta que lo recupere.',
      }),
      hi: Object.freeze({
        'core-storage-operations.preparing_export':
          'सुरक्षित डेटाबेस निर्यात तैयार किया जा रहा है…',
        'core-storage-operations.rollup_result':
          '{{removed, number}} रीडिंग को {{compacted, number}} में समेट दिया गया।',
        'core-storage-operations.rollup_protected':
          '{{count, number}} को पूर्ण विवरण में रखा गया क्योंकि वे किसी चेतावनी के पास हैं।',
        'core-storage-operations.rollup_space':
          'उनके उपयोग का स्थान फ़ाइल में तब तक रहेगा जब तक आप उसे वापस नहीं लेते।',
      }),
      ar: Object.freeze({
        'core-storage-operations.preparing_export':
          'جارٍ إعداد تصدير قاعدة البيانات الآمن…',
        'core-storage-operations.rollup_result':
          'دُمجت {{removed, number}} قراءة في {{compacted, number}}.',
        'core-storage-operations.rollup_protected':
          'أُبقيت {{count, number}} بتفاصيل كاملة لأنها بجانب تنبيه.',
        'core-storage-operations.rollup_space':
          'تبقى المساحة التي كانت تستخدمها داخل الملف إلى أن تستعيدها.',
      }),
      zh: Object.freeze({
        'core-storage-operations.preparing_export': '正在准备安全的数据库导出…',
        'core-storage-operations.rollup_result':
          '{{removed, number}} 条读数已汇总为 {{compacted, number}} 条。',
        'core-storage-operations.rollup_protected':
          '有 {{count, number}} 条读数因紧邻警报而保留完整详情。',
        'core-storage-operations.rollup_space':
          '它们所占用的空间会保留在文件中，直到您将其回收。',
      }),
    }),
  }),
  text(key, values, helpers) {
    const id = `core-storage-operations.${key}`;
    return helpers.i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
  render() {},
};
