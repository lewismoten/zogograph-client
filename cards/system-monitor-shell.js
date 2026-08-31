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
      }),
      es: Object.freeze({
        'core-system-monitor-shell.title': 'El propio monitor',
        'core-system-monitor-shell.help':
          'Un monitor de disco debe poder responder sobre su propio uso del disco y nunca dejar de recopilar sin decirlo. Nada de eso es visible en otro lugar de este sitio: una base de datos que crece silenciosamente hasta un gigabyte parece exactamente igual que una que está bien, y un trabajo cron que se detuvo el martes pasado solo hace que todos los gráficos parezcan tranquilos.',
      }),
      hi: Object.freeze({
        'core-system-monitor-shell.title': 'मॉनिटर स्वयं',
        'core-system-monitor-shell.help':
          'डिस्क मॉनिटर को अपने डिस्क उपयोग का उत्तर दे पाना चाहिए और बताए बिना कभी संग्रह बंद नहीं करना चाहिए। यह दोनों कहीं और दिखाई नहीं देते: चुपचाप एक गीगाबाइट तक बढ़ता डेटाबेस ठीक डेटाबेस जैसा ही दिखता है और पिछले मंगलवार बंद हुआ cron जॉब हर चार्ट को शांत दिखाता है।',
      }),
      ar: Object.freeze({
        'core-system-monitor-shell.title': 'المراقب نفسه',
        'core-system-monitor-shell.help':
          'ينبغي لمراقب القرص أن يجيب عن استخدامه للقرص، وألا يتوقف عن الجمع من دون أن يقول ذلك. لا يظهر أي منهما في موضع آخر في هذا الموقع: فقاعدة بيانات تنمو بهدوء إلى غيغابايت تبدو تمامًا كواحدة سليمة، ومهمة cron توقفت الثلاثاء الماضي تجعل كل مخطط يبدو هادئًا فقط.',
      }),
      zh: Object.freeze({
        'core-system-monitor-shell.title': '监控器自身',
        'core-system-monitor-shell.help':
          '磁盘监控器应能说明自身的磁盘使用情况，并且绝不能悄悄停止收集。这两点在此站点其他位置都看不到：悄然增长到一千兆字节的数据库看起来与正常数据库完全一样，而上周二停止的 cron 任务只会让每张图表看起来都很平静。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-monitor-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-monitor-shell.${key}`],
    );
  },
  render() {},
};
