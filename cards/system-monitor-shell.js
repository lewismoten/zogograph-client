/* Core card package: static shell copy for monitor diagnostics. */
window.ZogographSystemMonitorShellCard = {
  manifest: Object.freeze({
    id: 'core-system-monitor-shell',
    locales: ['core-system-monitor-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-monitor-shell.title': 'The monitor itself',
        'core-system-monitor-shell.download_diagnostic': 'Download diagnostic',
        'core-system-monitor-shell.help':
          'A disk monitor should be able to answer for its own disk use, and should never stop collecting without saying so. Neither is visible anywhere else on this site: a database quietly growing to a gigabyte looks exactly like one that is fine, and a cron job that stopped last Tuesday just makes every chart look quiet.',
        'core-system-monitor-shell.sign_in':
          "Sign in to see the monitor's own state.",
        'core-system-monitor-shell.ago': '{{duration}} ago',
        'core-system-monitor-shell.never': 'never',
        'core-system-monitor-shell.usually_every':
          ' · usually every {{duration}}',
        'core-system-monitor-shell.has_not_run': 'has not run',
        'core-system-monitor-shell.not_running_check_crontab':
          'not running — check the crontab',
        'core-system-monitor-shell.later_than_usual': 'later than usual',
        'core-system-monitor-shell.is_still_collecting':
          'Is it still collecting?',
        'core-system-monitor-shell.what_it_is_using': 'What it is using',
        'core-system-monitor-shell.collection_dial_help':
          'The ring fills as the next run approaches. A ring that has filled and is pulsing means one is due about now; a dashed grey one means nothing is arriving and the schedule is a guess from the gaps between the last few runs, not from a crontab this account can read.',
        'core-system-monitor-shell.retention_days': '{{days, number}} days',
        'core-system-monitor-shell.retention_indefinitely': 'kept indefinitely',
        'core-system-monitor-shell.retention_title': 'How long it keeps things',
        'core-system-monitor-shell.retention_readings': 'Readings, full detail',
        'core-system-monitor-shell.retention_five_minutes':
          'Rolled up to 5 minutes',
        'core-system-monitor-shell.retention_hour': 'Rolled up to an hour',
        'core-system-monitor-shell.retention_day': 'Rolled up to a day',
        'core-system-monitor-shell.retention_alerts': 'Alerts',
        'core-system-monitor-shell.retention_action_log': 'The action log',
        'core-system-monitor-shell.retention_incident_evidence':
          'Incident evidence',
        'core-system-monitor-shell.retention_scans': 'Scans',
      }),
      es: Object.freeze({
        'core-system-monitor-shell.title': 'El propio monitor',
        'core-system-monitor-shell.download_diagnostic':
          'Descargar diagnóstico',
        'core-system-monitor-shell.help':
          'Un monitor de disco debe poder responder sobre su propio uso del disco y nunca dejar de recopilar sin decirlo. Nada de eso es visible en otro lugar de este sitio: una base de datos que crece silenciosamente hasta un gigabyte parece exactamente igual que una que está bien, y un trabajo cron que se detuvo el martes pasado solo hace que todos los gráficos parezcan tranquilos.',
        'core-system-monitor-shell.sign_in':
          'Inicie sesión para ver el estado del propio monitor.',
        'core-system-monitor-shell.ago': 'hace {{duration}}',
        'core-system-monitor-shell.never': 'nunca',
        'core-system-monitor-shell.usually_every':
          ' · normalmente cada {{duration}}',
        'core-system-monitor-shell.has_not_run': 'no se ha ejecutado',
        'core-system-monitor-shell.not_running_check_crontab':
          'no se está ejecutando; revise el crontab',
        'core-system-monitor-shell.later_than_usual':
          'más tarde de lo habitual',
        'core-system-monitor-shell.is_still_collecting': '¿Sigue recopilando?',
        'core-system-monitor-shell.what_it_is_using': 'Qué está usando',
        'core-system-monitor-shell.collection_dial_help':
          'El anillo se llena al acercarse la próxima ejecución. Un anillo lleno y pulsante significa que una ejecución corresponde aproximadamente ahora; uno gris discontinuo significa que no llega nada y el horario es una estimación de los intervalos entre las últimas ejecuciones, no de un crontab que esta cuenta pueda leer.',
        'core-system-monitor-shell.retention_days': '{{days, number}} días',
        'core-system-monitor-shell.retention_indefinitely':
          'se conserva indefinidamente',
        'core-system-monitor-shell.retention_title':
          'Cuánto tiempo conserva las cosas',
        'core-system-monitor-shell.retention_readings':
          'Lecturas, detalle completo',
        'core-system-monitor-shell.retention_five_minutes':
          'Agrupado a 5 minutos',
        'core-system-monitor-shell.retention_hour': 'Agrupado a una hora',
        'core-system-monitor-shell.retention_day': 'Agrupado a un día',
        'core-system-monitor-shell.retention_alerts': 'Alertas',
        'core-system-monitor-shell.retention_action_log':
          'El registro de acciones',
        'core-system-monitor-shell.retention_incident_evidence':
          'Evidencia del incidente',
        'core-system-monitor-shell.retention_scans': 'Análisis',
      }),
      hi: Object.freeze({
        'core-system-monitor-shell.title': 'मॉनिटर स्वयं',
        'core-system-monitor-shell.download_diagnostic':
          'डायग्नोस्टिक डाउनलोड करें',
        'core-system-monitor-shell.help':
          'डिस्क मॉनिटर को अपने डिस्क उपयोग का उत्तर दे पाना चाहिए और बताए बिना कभी संग्रह बंद नहीं करना चाहिए। यह दोनों कहीं और दिखाई नहीं देते: चुपचाप एक गीगाबाइट तक बढ़ता डेटाबेस ठीक डेटाबेस जैसा ही दिखता है और पिछले मंगलवार बंद हुआ cron जॉब हर चार्ट को शांत दिखाता है।',
        'core-system-monitor-shell.sign_in':
          'मॉनिटर की अपनी स्थिति देखने के लिए साइन इन करें।',
        'core-system-monitor-shell.ago': '{{duration}} पहले',
        'core-system-monitor-shell.never': 'कभी नहीं',
        'core-system-monitor-shell.usually_every':
          ' · आम तौर पर हर {{duration}}',
        'core-system-monitor-shell.has_not_run': 'नहीं चला है',
        'core-system-monitor-shell.not_running_check_crontab':
          'नहीं चल रहा — crontab जाँचें',
        'core-system-monitor-shell.later_than_usual': 'सामान्य से देर से',
        'core-system-monitor-shell.is_still_collecting':
          'क्या यह अभी भी एकत्र कर रहा है?',
        'core-system-monitor-shell.what_it_is_using': 'यह क्या उपयोग कर रहा है',
        'core-system-monitor-shell.collection_dial_help':
          'अगला रन नज़दीक आने पर रिंग भरती है। भरी और धड़कती रिंग का मतलब है कि एक रन अभी होना चाहिए; धूसर टूटी रिंग का मतलब है कि कुछ नहीं आ रहा और शेड्यूल अंतिम कुछ रनों के बीच के अंतर से अनुमान है, उस crontab से नहीं जिसे यह खाता पढ़ सकता है।',
        'core-system-monitor-shell.retention_days': '{{days, number}} दिन',
        'core-system-monitor-shell.retention_indefinitely':
          'अनिश्चित काल तक रखा जाता है',
        'core-system-monitor-shell.retention_title':
          'यह चीज़ों को कितने समय तक रखता है',
        'core-system-monitor-shell.retention_readings': 'रीडिंग, पूरा विवरण',
        'core-system-monitor-shell.retention_five_minutes': '5 मिनट तक रोल अप',
        'core-system-monitor-shell.retention_hour': 'एक घंटे तक रोल अप',
        'core-system-monitor-shell.retention_day': 'एक दिन तक रोल अप',
        'core-system-monitor-shell.retention_alerts': 'चेतावनियाँ',
        'core-system-monitor-shell.retention_action_log': 'कार्य लॉग',
        'core-system-monitor-shell.retention_incident_evidence': 'घटना प्रमाण',
        'core-system-monitor-shell.retention_scans': 'स्कैन',
      }),
      ar: Object.freeze({
        'core-system-monitor-shell.title': 'المراقب نفسه',
        'core-system-monitor-shell.download_diagnostic': 'نزّل التشخيص',
        'core-system-monitor-shell.help':
          'ينبغي لمراقب القرص أن يجيب عن استخدامه للقرص، وألا يتوقف عن الجمع من دون أن يقول ذلك. لا يظهر أي منهما في موضع آخر في هذا الموقع: فقاعدة بيانات تنمو بهدوء إلى غيغابايت تبدو تمامًا كواحدة سليمة، ومهمة cron توقفت الثلاثاء الماضي تجعل كل مخطط يبدو هادئًا فقط.',
        'core-system-monitor-shell.sign_in':
          'سجّل الدخول لرؤية حالة المراقب نفسه.',
        'core-system-monitor-shell.ago': 'منذ {{duration}}',
        'core-system-monitor-shell.never': 'أبدًا',
        'core-system-monitor-shell.usually_every': ' · عادةً كل {{duration}}',
        'core-system-monitor-shell.has_not_run': 'لم يعمل',
        'core-system-monitor-shell.not_running_check_crontab':
          'لا يعمل — افحص crontab',
        'core-system-monitor-shell.later_than_usual': 'متأخر عن المعتاد',
        'core-system-monitor-shell.is_still_collecting': 'هل ما زال يجمع؟',
        'core-system-monitor-shell.what_it_is_using': 'ما الذي يستخدمه',
        'core-system-monitor-shell.collection_dial_help':
          'تمتلئ الحلقة مع اقتراب التشغيل التالي. الحلقة الممتلئة والنابضة تعني أن تشغيلًا مستحق تقريبًا الآن؛ والحلقة الرمادية المتقطعة تعني أن لا شيء يصل وأن الجدول تخمين من الفجوات بين آخر تشغيلات قليلة، وليس من crontab يستطيع هذا الحساب قراءته.',
        'core-system-monitor-shell.retention_days': '{{days, number}} يومًا',
        'core-system-monitor-shell.retention_indefinitely':
          'يُحتفظ به إلى أجل غير مسمى',
        'core-system-monitor-shell.retention_title': 'مدة احتفاظه بالأشياء',
        'core-system-monitor-shell.retention_readings':
          'القراءات، التفاصيل الكاملة',
        'core-system-monitor-shell.retention_five_minutes':
          'مُجمّعة إلى 5 دقائق',
        'core-system-monitor-shell.retention_hour': 'مُجمّعة إلى ساعة',
        'core-system-monitor-shell.retention_day': 'مُجمّعة إلى يوم',
        'core-system-monitor-shell.retention_alerts': 'التنبيهات',
        'core-system-monitor-shell.retention_action_log': 'سجل الإجراءات',
        'core-system-monitor-shell.retention_incident_evidence': 'دليل الحادثة',
        'core-system-monitor-shell.retention_scans': 'الفحوصات',
      }),
      zh: Object.freeze({
        'core-system-monitor-shell.title': '监控器自身',
        'core-system-monitor-shell.download_diagnostic': '下载诊断信息',
        'core-system-monitor-shell.help':
          '磁盘监控器应能说明自身的磁盘使用情况，并且绝不能悄悄停止收集。这两点在此站点其他位置都看不到：悄然增长到一千兆字节的数据库看起来与正常数据库完全一样，而上周二停止的 cron 任务只会让每张图表看起来都很平静。',
        'core-system-monitor-shell.sign_in': '登录后可查看监控器自身状态。',
        'core-system-monitor-shell.ago': '{{duration}} 前',
        'core-system-monitor-shell.never': '从未',
        'core-system-monitor-shell.usually_every': ' · 通常每 {{duration}}',
        'core-system-monitor-shell.has_not_run': '尚未运行',
        'core-system-monitor-shell.not_running_check_crontab':
          '未运行 — 请检查 crontab',
        'core-system-monitor-shell.later_than_usual': '比通常更晚',
        'core-system-monitor-shell.is_still_collecting': '它仍在收集吗？',
        'core-system-monitor-shell.what_it_is_using': '它正在使用什么',
        'core-system-monitor-shell.collection_dial_help':
          '随着下一次运行临近，圆环会逐渐填满。填满且脉动的圆环表示现在大约应当运行一次；虚线灰环表示没有内容到达，而计划是根据最近几次运行之间的间隔推测的，并非来自此账户能够读取的 crontab。',
        'core-system-monitor-shell.retention_days': '{{days, number}} 天',
        'core-system-monitor-shell.retention_indefinitely': '无限期保留',
        'core-system-monitor-shell.retention_title': '保留内容的时长',
        'core-system-monitor-shell.retention_readings': '读数，完整详细信息',
        'core-system-monitor-shell.retention_five_minutes': '汇总为 5 分钟',
        'core-system-monitor-shell.retention_hour': '汇总为一小时',
        'core-system-monitor-shell.retention_day': '汇总为一天',
        'core-system-monitor-shell.retention_alerts': '警报',
        'core-system-monitor-shell.retention_action_log': '操作日志',
        'core-system-monitor-shell.retention_incident_evidence': '事件证据',
        'core-system-monitor-shell.retention_scans': '扫描',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-system-monitor-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-system-monitor-shell.${key}`],
    );
  },
  render() {},
};
