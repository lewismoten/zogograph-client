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
        'core-storage-offender-status-help.back_in_list': 'Back in the list',
        'core-storage-offender-status-help.back_in_list_detail':
          'Recorded before, absent from the previous scan, and back now — it grew past the size that gets a path recorded rather than being created.',
      }),
      es: Object.freeze({
        'core-storage-offender-status-help.gone':
          'Ya no está en disco y este panel no lo eliminó; lo quitó otra cosa: rotación de registros, una tarea cron o una persona.',
        'core-storage-offender-status-help.new':
          'Esta ruta nunca se había registrado.',
        'core-storage-offender-status-help.back_in_list':
          'De vuelta en la lista',
        'core-storage-offender-status-help.back_in_list_detail':
          'Se registró antes, faltó en el análisis anterior y volvió ahora: creció lo suficiente para registrarse.',
      }),
      hi: Object.freeze({
        'core-storage-offender-status-help.gone':
          'अब डिस्क पर नहीं है और इस डैशबोर्ड से नहीं हटाई गई; किसी और ने हटाई: लॉग रोटेशन, क्रॉन जॉब या व्यक्ति।',
        'core-storage-offender-status-help.new':
          'यह पथ पहले कभी दर्ज नहीं हुआ।',
        'core-storage-offender-status-help.back_in_list': 'सूची में वापस',
        'core-storage-offender-status-help.back_in_list_detail':
          'पहले दर्ज था, पिछले स्कैन में अनुपस्थित था और अब वापस है: यह दर्ज होने लायक बढ़ा है।',
      }),
      ar: Object.freeze({
        'core-storage-offender-status-help.gone':
          'لم يعد على القرص ولم تحذفه لوحة المعلومات هذه؛ أزاله شيء آخر: تدوير سجل أو مهمة cron أو شخص.',
        'core-storage-offender-status-help.new': 'لم يُسجل هذا المسار من قبل.',
        'core-storage-offender-status-help.back_in_list': 'عاد إلى القائمة',
        'core-storage-offender-status-help.back_in_list_detail':
          'سُجل سابقًا وغاب عن الفحص السابق وعاد الآن: نما بما يكفي ليسجل بدلًا من أن يكون قد أُنشئ.',
      }),
      zh: Object.freeze({
        'core-storage-offender-status-help.gone':
          '它已不在磁盘上，也不是由此仪表板删除；其他内容移除了它：日志轮换、cron 任务或人员。',
        'core-storage-offender-status-help.new': '此前从未记录过此路径。',
        'core-storage-offender-status-help.back_in_list': '重新出现在列表中',
        'core-storage-offender-status-help.back_in_list_detail':
          '以前记录过，上次扫描时缺失，现在又回来了：它增长到足以被记录。',
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
