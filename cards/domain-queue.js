window.ZogographDomainQueueCard = {
  manifest: Object.freeze({
    id: 'core-domain-queue',
    locales: ['core-domain-queue.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-domain-queue.title': 'Domain names',
        'core-domain-queue.help':
          'A domain expiring is the one outage nothing else here can see coming. Disk, memory, CPU and the HTTP checks all stay green right up to the moment the name stops resolving, and then everything fails at once for a reason that is in no log. Checked weekly — a registration does not change between Tuesdays, and whois servers throttle anybody who asks more often.',
        'core-domain-queue.warn': 'warn',
        'core-domain-queue.days_before': 'days before',
        'core-domain-queue.watch': 'Watch it',
        'core-domain-queue.waiting':
          '{{due, number}} domains are due, with {{perRun, number}} looked up per run — registries rate-limit anyone who asks faster, and a blocked address reports every domain as unknown. The oldest waits least.',
        'core-domain-queue.empty': 'No domains are being watched.',
      }),
      es: Object.freeze({
        'core-domain-queue.title': 'Nombres de dominio',
        'core-domain-queue.help':
          'El vencimiento de un dominio es la avería que nada más aquí puede anticipar. Disco, memoria, CPU y comprobaciones HTTP siguen en verde hasta que el nombre deja de resolverse; entonces todo falla a la vez por una causa que no aparece en ningún registro. Se comprueba semanalmente: un registro no cambia entre martes y los servidores whois limitan a quien pregunta más a menudo.',
        'core-domain-queue.warn': 'avisar',
        'core-domain-queue.days_before': 'días antes',
        'core-domain-queue.watch': 'Vigilar',
        'core-domain-queue.waiting':
          'Hay {{due, number}} dominios pendientes y se consulta {{perRun, number}} en cada ejecución: los registros limitan las consultas rápidas y una dirección bloqueada informa de todos los dominios como desconocidos. Los más antiguos esperan menos.',
        'core-domain-queue.empty': 'No se está vigilando ningún dominio.',
      }),
      hi: Object.freeze({
        'core-domain-queue.title': 'डोमेन नाम',
        'core-domain-queue.help':
          'डोमेन की समाप्ति वह एक बाधा है जिसे यहाँ कुछ और आते नहीं देख सकता। डिस्क, मेमोरी, CPU और HTTP जाँच तब तक हरे रहते हैं जब तक नाम रिज़ॉल्व होना बंद नहीं करता; फिर हर चीज़ एक साथ विफल होती है, ऐसे कारण से जो किसी लॉग में नहीं है। साप्ताहिक जाँच होती है—पंजीकरण मंगलवारों के बीच नहीं बदलता और whois सर्वर अधिक बार पूछने वालों को सीमित करते हैं।',
        'core-domain-queue.warn': 'चेतावनी',
        'core-domain-queue.days_before': 'दिन पहले',
        'core-domain-queue.watch': 'निगरानी करें',
        'core-domain-queue.waiting':
          '{{due, number}} डोमेन देय हैं और हर रन में {{perRun, number}} देखे जाते हैं — रजिस्ट्रियाँ तेज़ी से पूछने वालों को सीमित करती हैं और ब्लॉक किया गया पता हर डोमेन को अज्ञात बताता है। सबसे पुराने को सबसे कम इंतज़ार है।',
        'core-domain-queue.empty': 'कोई डोमेन नहीं देखा जा रहा है।',
      }),
      ar: Object.freeze({
        'core-domain-queue.title': 'أسماء النطاقات',
        'core-domain-queue.help':
          'انتهاء نطاق هو العطل الوحيد الذي لا يرى شيء آخر هنا قدومه. يبقى القرص والذاكرة والمعالج وفحوص HTTP خضراء حتى تتوقف التسمية عن الحل، ثم يفشل كل شيء دفعة واحدة لسبب ليس في أي سجل. يُفحص أسبوعيًا؛ فالتسجيل لا يتغير بين يومي ثلاثاء، وخوادم whois تقيد من يسأل أكثر من ذلك.',
        'core-domain-queue.warn': 'حذّر',
        'core-domain-queue.days_before': 'أيام قبل',
        'core-domain-queue.watch': 'راقبه',
        'core-domain-queue.waiting':
          'هناك {{due, number}} نطاقات مستحقة، ويُبحث عن {{perRun, number}} في كل تشغيل — تقيد السجلات من يسأل بسرعة أكبر، والعنوان المحظور يبلغ عن كل نطاق بأنه غير معروف. الأقدم ينتظر أقل.',
        'core-domain-queue.empty': 'لا توجد نطاقات قيد المراقبة.',
      }),
      zh: Object.freeze({
        'core-domain-queue.title': '域名',
        'core-domain-queue.help':
          '域名到期是这里其他功能都无法预见的一种故障。磁盘、内存、CPU 和 HTTP 检查会一直保持绿色，直到名称停止解析；随后一切同时失败，原因却不在任何日志中。每周检查一次——注册信息不会在两个周二之间变化，而且 whois 服务器会限制频繁查询者。',
        'core-domain-queue.warn': '警告',
        'core-domain-queue.days_before': '天前',
        'core-domain-queue.watch': '监视',
        'core-domain-queue.waiting':
          '有 {{due, number}} 个域名待处理，每次运行查询 {{perRun, number}} 个——注册机构会限制查询过快的请求，而被阻止的地址会将每个域名报告为未知。最早的等待时间最短。',
        'core-domain-queue.empty': '尚未监测任何域名。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-domain-queue.${key}`,
      {},
      this.manifest.catalogs.en[`core-domain-queue.${key}`],
    );
  },
  render(data, helpers) {
    const wrap = document.createDocumentFragment();
    if (data.queue && data.queue.due > data.queue.per_run) {
      wrap.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-domain-queue.waiting',
            { due: data.queue.due, perRun: data.queue.per_run },
            '{{due, number}} domains are due, with {{perRun, number}} looked up per run.',
          ),
        ),
      );
    }
    if (!data.rows.length) {
      wrap.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-domain-queue.empty',
            {},
            'No domains are being watched.',
          ),
        ),
      );
    }
    return wrap;
  },
};
