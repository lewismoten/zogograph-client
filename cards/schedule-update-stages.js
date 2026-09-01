/* Core card package: system-update stage selector. */
window.ZogographScheduleUpdateStagesCard = {
  manifest: Object.freeze({
    id: 'core-schedule-update-stages',
    locales: ['core-schedule-update-stages.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-schedule-update-stages.stages': 'Update stages',
        'core-schedule-update-stages.none': 'None',
        'core-schedule-update-stages.none_help':
          'Turn off every automatic update stage',
        'core-schedule-update-stages.detect': 'Detect',
        'core-schedule-update-stages.notify': 'Notify',
        'core-schedule-update-stages.download': 'Download',
        'core-schedule-update-stages.auto': 'Auto-Update',
      }),
      es: Object.freeze({
        'core-schedule-update-stages.stages': 'Fases de actualización',
        'core-schedule-update-stages.none': 'Ninguna',
        'core-schedule-update-stages.none_help':
          'Desactivar todas las fases de actualización automática',
        'core-schedule-update-stages.detect': 'Detectar',
        'core-schedule-update-stages.notify': 'Notificar',
        'core-schedule-update-stages.download': 'Descargar',
        'core-schedule-update-stages.auto': 'Actualización automática',
      }),
      hi: Object.freeze({
        'core-schedule-update-stages.stages': 'अपडेट चरण',
        'core-schedule-update-stages.none': 'कोई नहीं',
        'core-schedule-update-stages.none_help':
          'हर स्वचालित अपडेट चरण बंद करें',
        'core-schedule-update-stages.detect': 'पता लगाएँ',
        'core-schedule-update-stages.notify': 'सूचित करें',
        'core-schedule-update-stages.download': 'डाउनलोड',
        'core-schedule-update-stages.auto': 'स्वचालित अपडेट',
      }),
      ar: Object.freeze({
        'core-schedule-update-stages.stages': 'مراحل التحديث',
        'core-schedule-update-stages.none': 'لا شيء',
        'core-schedule-update-stages.none_help':
          'أوقف كل مراحل التحديث التلقائي',
        'core-schedule-update-stages.detect': 'اكتشاف',
        'core-schedule-update-stages.notify': 'إشعار',
        'core-schedule-update-stages.download': 'تنزيل',
        'core-schedule-update-stages.auto': 'تحديث تلقائي',
      }),
      zh: Object.freeze({
        'core-schedule-update-stages.stages': '更新阶段',
        'core-schedule-update-stages.none': '无',
        'core-schedule-update-stages.none_help': '关闭所有自动更新阶段',
        'core-schedule-update-stages.detect': '检测',
        'core-schedule-update-stages.notify': '通知',
        'core-schedule-update-stages.download': '下载',
        'core-schedule-update-stages.auto': '自动更新',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-update-stages.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
