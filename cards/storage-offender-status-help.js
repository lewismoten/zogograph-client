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
        'core-storage-offender-status-help.still':
          'Still on disk — no longer among the largest',
        'core-storage-offender-status-help.still_detail':
          'The file has not gone anywhere. Something else grew past it, so it dropped out of the paths this scan records.',
        'core-storage-offender-status-help.deleted_here':
          'Deleted from here {{at}}',
        'core-storage-offender-status-help.deleted_here_detail':
          'Deleted through this dashboard, from the action log.',
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
        'core-storage-offender-status-help.still':
          'Sigue en disco; ya no está entre los mayores',
        'core-storage-offender-status-help.still_detail':
          'El archivo no se ha ido. Algo más creció y lo sacó de las rutas que registra este análisis.',
        'core-storage-offender-status-help.deleted_here':
          'Eliminado aquí {{at}}',
        'core-storage-offender-status-help.deleted_here_detail':
          'Eliminado desde este panel, según el registro de acciones.',
      }),
      hi: Object.freeze({
        'core-storage-offender-status-help.gone':
          'अब डिस्क पर नहीं है और इस डैशबोर्ड से नहीं हटाई गई; किसी और ने हटाई: लॉग रोटेशन, क्रॉन जॉब या व्यक्ति।',
        'core-storage-offender-status-help.new':
          'यह पथ पहले कभी दर्ज नहीं हुआ।',
        'core-storage-offender-status-help.back_in_list': 'सूची में वापस',
        'core-storage-offender-status-help.back_in_list_detail':
          'पहले दर्ज था, पिछले स्कैन में अनुपस्थित था और अब वापस है: यह दर्ज होने लायक बढ़ा है।',
        'core-storage-offender-status-help.still':
          'डिस्क पर है — अब सबसे बड़ी फ़ाइलों में नहीं',
        'core-storage-offender-status-help.still_detail':
          'फ़ाइल कहीं नहीं गई। कुछ और बढ़ गया, इसलिए यह इस स्कैन में दर्ज पथों से बाहर हो गई।',
        'core-storage-offender-status-help.deleted_here':
          'यहाँ से {{at}} हटाई गई',
        'core-storage-offender-status-help.deleted_here_detail':
          'कार्रवाई लॉग के अनुसार इस डैशबोर्ड से हटाई गई।',
      }),
      ar: Object.freeze({
        'core-storage-offender-status-help.gone':
          'لم يعد على القرص ولم تحذفه لوحة المعلومات هذه؛ أزاله شيء آخر: تدوير سجل أو مهمة cron أو شخص.',
        'core-storage-offender-status-help.new': 'لم يُسجل هذا المسار من قبل.',
        'core-storage-offender-status-help.back_in_list': 'عاد إلى القائمة',
        'core-storage-offender-status-help.back_in_list_detail':
          'سُجل سابقًا وغاب عن الفحص السابق وعاد الآن: نما بما يكفي ليسجل بدلًا من أن يكون قد أُنشئ.',
        'core-storage-offender-status-help.still':
          'ما زال على القرص — لم يعد من الأكبر',
        'core-storage-offender-status-help.still_detail':
          'لم يذهب الملف إلى مكان. نما شيء آخر فتراجع عن المسارات التي يسجلها هذا الفحص.',
        'core-storage-offender-status-help.deleted_here': 'حُذف هنا {{at}}',
        'core-storage-offender-status-help.deleted_here_detail':
          'حُذف عبر لوحة المعلومات هذه وفق سجل الإجراءات.',
      }),
      zh: Object.freeze({
        'core-storage-offender-status-help.gone':
          '它已不在磁盘上，也不是由此仪表板删除；其他内容移除了它：日志轮换、cron 任务或人员。',
        'core-storage-offender-status-help.new': '此前从未记录过此路径。',
        'core-storage-offender-status-help.back_in_list': '重新出现在列表中',
        'core-storage-offender-status-help.back_in_list_detail':
          '以前记录过，上次扫描时缺失，现在又回来了：它增长到足以被记录。',
        'core-storage-offender-status-help.still':
          '仍在磁盘上——不再属于最大的项目',
        'core-storage-offender-status-help.still_detail':
          '文件没有消失。其他内容增长并使其不再出现在此次扫描记录的路径中。',
        'core-storage-offender-status-help.deleted_here':
          '已于 {{at}} 在此删除',
        'core-storage-offender-status-help.deleted_here_detail':
          '根据操作日志，此仪表板已将其删除。',
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
