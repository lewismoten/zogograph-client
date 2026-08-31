/* Core card package: offender-list new and gone explanations. */
window.ZogographStorageOffenderStatusHelpCard = {
  manifest: Object.freeze({
    id: 'core-storage-offender-status-help',
    locales: ['core-storage-offender-status-help.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-offender-status-help.gone':
          'Not on disk any more, and not deleted through this dashboard — so something else removed it: log rotation, a cron job, or a person.',
        'core-storage-offender-status-help.new':
          'This path has never been recorded before.',
      }),
      es: Object.freeze({
        'core-storage-offender-status-help.gone':
          'Ya no está en disco y este panel no lo eliminó; lo quitó otra cosa: rotación de registros, una tarea cron o una persona.',
        'core-storage-offender-status-help.new':
          'Esta ruta nunca se había registrado.',
      }),
      hi: Object.freeze({
        'core-storage-offender-status-help.gone':
          'अब डिस्क पर नहीं है और इस डैशबोर्ड से नहीं हटाई गई; किसी और ने हटाई: लॉग रोटेशन, क्रॉन जॉब या व्यक्ति।',
        'core-storage-offender-status-help.new':
          'यह पथ पहले कभी दर्ज नहीं हुआ।',
      }),
      ar: Object.freeze({
        'core-storage-offender-status-help.gone':
          'لم يعد على القرص ولم تحذفه لوحة المعلومات هذه؛ أزاله شيء آخر: تدوير سجل أو مهمة cron أو شخص.',
        'core-storage-offender-status-help.new': 'لم يُسجل هذا المسار من قبل.',
      }),
      zh: Object.freeze({
        'core-storage-offender-status-help.gone':
          '它已不在磁盘上，也不是由此仪表板删除；其他内容移除了它：日志轮换、cron 任务或人员。',
        'core-storage-offender-status-help.new': '此前从未记录过此路径。',
      }),
    }),
  }),
  render({ key }, { i18n }) {
    return i18n.t(
      `core-storage-offender-status-help.${key}`,
      {},
      this.manifest.catalogs.en[`core-storage-offender-status-help.${key}`],
    );
  },
};
