/* Core card package: static filters and guidance for host capabilities. */
window.ZogographCapabilitiesShellCard = {
  manifest: Object.freeze({
    id: 'core-capabilities-shell',
    locales: ['core-capabilities-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-capabilities-shell.title': 'Capabilities',
        'core-capabilities-shell.show': 'Show',
        'core-capabilities-shell.filter_key': 'The ones worth having',
        'core-capabilities-shell.filter_gap': 'Not available here',
        'core-capabilities-shell.filter_all': 'Everything',
        'core-capabilities-shell.ask_host': 'Ask the host',
        'core-capabilities-shell.help':
          'What this account can actually run, and what the machine has that it cannot reach. Those are the same question on ordinary hosting and different ones here — see below.',
        'core-capabilities-shell.checked':
          '{{total, number}} programs checked in {{seconds, number}} seconds.',
        'core-capabilities-shell.checked_changes':
          '{{total, number}} programs checked in {{seconds, number}} seconds; {{changes, number}} changed since last time.',
        'core-capabilities-shell.host_requested':
          'Requested. The host collector will check on its next run.',
      }),
      es: Object.freeze({
        'core-capabilities-shell.title': 'Capacidades',
        'core-capabilities-shell.show': 'Mostrar',
        'core-capabilities-shell.filter_key': 'Las que vale la pena tener',
        'core-capabilities-shell.filter_gap': 'No disponibles aquí',
        'core-capabilities-shell.filter_all': 'Todo',
        'core-capabilities-shell.ask_host': 'Preguntar al host',
        'core-capabilities-shell.help':
          'Lo que esta cuenta puede ejecutar realmente y lo que la máquina tiene pero no puede alcanzar. En un alojamiento normal ambas son la misma pregunta; aquí son distintas. Véase abajo.',
        'core-capabilities-shell.checked':
          'Se comprobaron {{total, number}} programas en {{seconds, number}} segundos.',
        'core-capabilities-shell.checked_changes':
          'Se comprobaron {{total, number}} programas en {{seconds, number}} segundos; {{changes, number}} cambiaron desde la última vez.',
        'core-capabilities-shell.host_requested':
          'Solicitud enviada. El recopilador del host lo comprobará en su próxima ejecución.',
      }),
      hi: Object.freeze({
        'core-capabilities-shell.title': 'क्षमताएँ',
        'core-capabilities-shell.show': 'दिखाएँ',
        'core-capabilities-shell.filter_key': 'जो रखना सार्थक है',
        'core-capabilities-shell.filter_gap': 'यहाँ उपलब्ध नहीं',
        'core-capabilities-shell.filter_all': 'सब कुछ',
        'core-capabilities-shell.ask_host': 'होस्ट से पूछें',
        'core-capabilities-shell.help':
          'यह खाता वास्तव में क्या चला सकता है और मशीन में ऐसा क्या है जिस तक यह पहुँच नहीं सकता। सामान्य होस्टिंग पर ये एक ही प्रश्न हैं, यहाँ अलग—नीचे देखें।',
        'core-capabilities-shell.checked':
          '{{total, number}} प्रोग्राम की जाँच {{seconds, number}} सेकंड में हुई।',
        'core-capabilities-shell.checked_changes':
          '{{total, number}} प्रोग्राम की जाँच {{seconds, number}} सेकंड में हुई; पिछली बार से {{changes, number}} बदले हैं।',
        'core-capabilities-shell.host_requested':
          'अनुरोध भेज दिया गया है। होस्ट कलेक्टर अपने अगले रन में जाँच करेगा।',
      }),
      ar: Object.freeze({
        'core-capabilities-shell.title': 'القدرات',
        'core-capabilities-shell.show': 'اعرض',
        'core-capabilities-shell.filter_key': 'الجديرة بالوجود',
        'core-capabilities-shell.filter_gap': 'غير متاح هنا',
        'core-capabilities-shell.filter_all': 'كل شيء',
        'core-capabilities-shell.ask_host': 'اسأل المضيف',
        'core-capabilities-shell.help':
          'ما يمكن لهذا الحساب تشغيله فعلًا، وما لدى الآلة مما لا يستطيع الوصول إليه. هذان السؤالان واحد في الاستضافة العادية ومختلفان هنا — انظر أدناه.',
        'core-capabilities-shell.checked':
          'فُحص {{total, number}} برنامجًا خلال {{seconds, number}} ثانية.',
        'core-capabilities-shell.checked_changes':
          'فُحص {{total, number}} برنامجًا خلال {{seconds, number}} ثانية؛ تغيّر {{changes, number}} منذ المرة الماضية.',
        'core-capabilities-shell.host_requested':
          'أُرسل الطلب. سيتحقق جامع بيانات المضيف في تشغيله التالي.',
      }),
      zh: Object.freeze({
        'core-capabilities-shell.title': '功能能力',
        'core-capabilities-shell.show': '显示',
        'core-capabilities-shell.filter_key': '值得拥有的功能',
        'core-capabilities-shell.filter_gap': '此处不可用',
        'core-capabilities-shell.filter_all': '全部',
        'core-capabilities-shell.ask_host': '询问主机',
        'core-capabilities-shell.help':
          '此账户实际上能运行什么，以及机器具备但它无法访问什么。在普通主机上这是同一个问题，在这里则是两个问题——请看下方。',
        'core-capabilities-shell.checked':
          '已在 {{seconds, number}} 秒内检查 {{total, number}} 个程序。',
        'core-capabilities-shell.checked_changes':
          '已在 {{seconds, number}} 秒内检查 {{total, number}} 个程序；自上次以来有 {{changes, number}} 个变化。',
        'core-capabilities-shell.host_requested':
          '已请求。主机采集器将在下次运行时检查。',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-capabilities-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-capabilities-shell.${key}`],
    );
  },
  render() {},
};
