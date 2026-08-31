/* Core card package: static shell copy for monitor diagnostics. */
window.ZogographSystemMonitorShellCard = {
  manifest: Object.freeze({
    id: 'core-system-monitor-shell',
    locales: ['core-system-monitor-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-monitor-shell.title': 'The monitor itself',
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
      }),
      es: Object.freeze({
        'core-system-monitor-shell.title': 'El propio monitor',
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
      }),
      hi: Object.freeze({
        'core-system-monitor-shell.title': 'मॉनिटर स्वयं',
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
      }),
      ar: Object.freeze({
        'core-system-monitor-shell.title': 'المراقب نفسه',
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
      }),
      zh: Object.freeze({
        'core-system-monitor-shell.title': '监控器自身',
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
