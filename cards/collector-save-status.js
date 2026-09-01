/* Core card package: confirmation shared by collector-backed settings forms. */
window.ZogographCollectorSaveStatusCard = {
  manifest: Object.freeze({
    id: 'core-collector-save-status',
    locales: ['core-collector-save-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-collector-save-status.save': 'Save',
        'core-collector-save-status.saved':
          'Saved. The next collector run will use these.',
      }),
      es: Object.freeze({
        'core-collector-save-status.save': 'Guardar',
        'core-collector-save-status.saved':
          'Guardado. La siguiente ejecución del recopilador usará estos valores.',
      }),
      hi: Object.freeze({
        'core-collector-save-status.save': 'सहेजें',
        'core-collector-save-status.saved':
          'सहेजा गया। अगला कलेक्टर रन इनका उपयोग करेगा।',
      }),
      ar: Object.freeze({
        'core-collector-save-status.save': 'حفظ',
        'core-collector-save-status.saved':
          'حُفظت. سيستخدمها تشغيل الجامع التالي.',
      }),
      zh: Object.freeze({
        'core-collector-save-status.save': '保存',
        'core-collector-save-status.saved':
          '已保存。下一次采集器运行将使用这些设置。',
      }),
    }),
  }),
  text(key, helpers) {
    const id = `core-collector-save-status.${key}`;
    return helpers.i18n.t(id, {}, this.manifest.catalogs.en[id]);
  },
  render() {},
};
