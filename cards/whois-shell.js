/* Core card package: static controls and guidance for Whois lookups. */
window.ZogographWhoisShellCard = {
  manifest: Object.freeze({
    id: 'core-whois-shell',
    locales: ['core-whois-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-whois-shell.title': 'Look a name up',
        'core-whois-shell.help':
          'A one-off lookup for a name you are not watching — whether it is taken, who holds it, when it expires. Nothing is stored and no domain is added to the list; there is a button for that if the answer makes you want one.',
        'core-whois-shell.ask': 'Ask',
        'core-whois-shell.sign_in_lookup': 'Sign in to run a lookup.',
        'core-whois-shell.type_name': 'Type a name above.',
        'core-whois-shell.asking_registry':
          'Asking the registry… this can take a few seconds.',
        'core-whois-shell.host_pending':
          "WHOIS is not available to this account, so root's collector has been asked to run it. It answers on its next run — the countdown is at the top of the page, and this page will have the answer once it lands.",
        'core-whois-shell.host_pending_with_error':
          "WHOIS is not available to this account, so root's collector has been asked to run it. It answers on its next run — the countdown is at the top of the page, and this page will have the answer once it lands. The last attempt on the host said: {{error}}",
        'core-whois-shell.lookup_failed': 'The lookup failed.',
        'core-whois-shell.host_answered':
          "Answered by root's collector {{when}}, because WHOIS is not available to this account.",
        'core-whois-shell.available_name':
          '{{name}} appears to be unregistered. The registry has no record of it, which is what an available name looks like — though a registry that is simply not answering can look the same, so check with a registrar before celebrating.',
        'core-whois-shell.no_recognized_date':
          'The answer contained no date this dashboard recognizes',
        'core-whois-shell.registered': 'Registered',
        'core-whois-shell.record_last_changed': 'Record last changed',
        'core-whois-shell.status': 'Status',
        'core-whois-shell.nameservers': 'Nameservers',
        'core-whois-shell.auto_renew': 'Auto renew',
        'core-whois-shell.registry_facts': 'What the registry says',
        'core-whois-shell.lock_meanings': 'What those locks mean',
        'core-whois-shell.registry_wording': 'As the registry writes it',
        'core-whois-shell.watch': 'Watch this domain',
        'core-whois-shell.now_watching': 'Now watching {{domain}}.',
      }),
      es: Object.freeze({
        'core-whois-shell.title': 'Buscar un nombre',
        'core-whois-shell.help':
          'Una consulta puntual de un nombre que no está vigilando: si está ocupado, quién lo posee y cuándo vence. No se almacena nada ni se añade ningún dominio a la lista; hay un botón para ello si la respuesta hace que quiera añadirlo.',
        'core-whois-shell.ask': 'Consultar',
        'core-whois-shell.sign_in_lookup':
          'Inicie sesión para realizar una consulta.',
        'core-whois-shell.type_name': 'Escriba un nombre arriba.',
        'core-whois-shell.asking_registry':
          'Consultando al registro… puede tardar unos segundos.',
        'core-whois-shell.host_pending':
          'WHOIS no está disponible para esta cuenta, así que se ha pedido al recopilador de root que lo ejecute. Responderá en su próxima ejecución: la cuenta atrás está en la parte superior de la página y esta página tendrá la respuesta cuando llegue.',
        'core-whois-shell.host_pending_with_error':
          'WHOIS no está disponible para esta cuenta, así que se ha pedido al recopilador de root que lo ejecute. Responderá en su próxima ejecución: la cuenta atrás está en la parte superior de la página y esta página tendrá la respuesta cuando llegue. El último intento en el host dijo: {{error}}',
        'core-whois-shell.lookup_failed': 'La consulta falló.',
        'core-whois-shell.host_answered':
          'Respondido por el recopilador de root {{when}}, porque WHOIS no está disponible para esta cuenta.',
        'core-whois-shell.available_name':
          '{{name}} parece no estar registrado. El registro no tiene constancia de él, que es el aspecto de un nombre disponible; aunque un registro que simplemente no responde puede parecer igual, así que compruébelo con un registrador antes de celebrarlo.',
        'core-whois-shell.no_recognized_date':
          'La respuesta no contiene una fecha que este panel reconozca',
        'core-whois-shell.registered': 'Registrado',
        'core-whois-shell.record_last_changed': 'Último cambio del registro',
        'core-whois-shell.status': 'Estado',
        'core-whois-shell.nameservers': 'Servidores de nombres',
        'core-whois-shell.auto_renew': 'Renovación automática',
        'core-whois-shell.registry_facts': 'Lo que dice el registro',
        'core-whois-shell.lock_meanings': 'Qué significan esos bloqueos',
        'core-whois-shell.registry_wording': 'Como lo escribe el registro',
        'core-whois-shell.watch': 'Vigilar este dominio',
        'core-whois-shell.now_watching': 'Ahora se vigila {{domain}}.',
      }),
      hi: Object.freeze({
        'core-whois-shell.title': 'नाम खोजें',
        'core-whois-shell.help':
          'ऐसे नाम के लिए एक बार की खोज जिस पर आप नज़र नहीं रख रहे—क्या वह लिया गया है, किसके पास है और कब समाप्त होता है। कुछ भी संग्रहीत नहीं होता और कोई डोमेन सूची में नहीं जोड़ा जाता; यदि जवाब से आप उसे जोड़ना चाहें तो उसके लिए बटन है।',
        'core-whois-shell.ask': 'पूछें',
        'core-whois-shell.sign_in_lookup': 'लुकअप चलाने के लिए साइन इन करें।',
        'core-whois-shell.type_name': 'ऊपर एक नाम लिखें।',
        'core-whois-shell.asking_registry':
          'रजिस्ट्री से पूछा जा रहा है… इसमें कुछ सेकंड लग सकते हैं।',
        'core-whois-shell.host_pending':
          'WHOIS इस खाते के लिए उपलब्ध नहीं है, इसलिए root के कलेक्टर से इसे चलाने का अनुरोध किया गया है। यह अपने अगले रन पर जवाब देगा — उलटी गिनती पृष्ठ के शीर्ष पर है और उत्तर आने पर इस पृष्ठ पर दिखेगा।',
        'core-whois-shell.host_pending_with_error':
          'WHOIS इस खाते के लिए उपलब्ध नहीं है, इसलिए root के कलेक्टर से इसे चलाने का अनुरोध किया गया है। यह अपने अगले रन पर जवाब देगा — उलटी गिनती पृष्ठ के शीर्ष पर है और उत्तर आने पर इस पृष्ठ पर दिखेगा। होस्ट पर पिछले प्रयास में कहा गया: {{error}}',
        'core-whois-shell.lookup_failed': 'लुकअप विफल हुआ।',
        'core-whois-shell.host_answered':
          'root के कलेक्टर ने {{when}} उत्तर दिया, क्योंकि WHOIS इस खाते के लिए उपलब्ध नहीं है।',
        'core-whois-shell.available_name':
          '{{name}} पंजीकृत नहीं लगता है। रजिस्ट्री में इसका कोई रिकॉर्ड नहीं है, जो उपलब्ध नाम जैसा दिखता है — हालांकि जो रजिस्ट्री जवाब नहीं दे रही हो वह भी ऐसी दिख सकती है, इसलिए उत्सव मनाने से पहले रजिस्ट्रार से जाँचें।',
        'core-whois-shell.no_recognized_date':
          'उत्तर में ऐसी कोई तारीख नहीं थी जिसे यह डैशबोर्ड पहचानता है',
        'core-whois-shell.registered': 'पंजीकृत',
        'core-whois-shell.record_last_changed': 'रिकॉर्ड अंतिम बार बदला',
        'core-whois-shell.status': 'स्थिति',
        'core-whois-shell.nameservers': 'नेमसर्वर',
        'core-whois-shell.auto_renew': 'स्वतः नवीनीकरण',
        'core-whois-shell.registry_facts': 'रजिस्ट्री क्या कहती है',
        'core-whois-shell.lock_meanings': 'इन लॉक का अर्थ',
        'core-whois-shell.registry_wording': 'जैसा रजिस्ट्री लिखती है',
        'core-whois-shell.watch': 'इस डोमेन की निगरानी करें',
        'core-whois-shell.now_watching': 'अब {{domain}} की निगरानी हो रही है।',
      }),
      ar: Object.freeze({
        'core-whois-shell.title': 'ابحث عن اسم',
        'core-whois-shell.help':
          'بحث لمرة واحدة عن اسم لا تراقبه: هل حُجز، ومن يملكه، ومتى ينتهي. لا يُخزن شيء ولا يُضاف أي نطاق إلى القائمة؛ يوجد زر لذلك إذا جعلتك الإجابة ترغب في إضافته.',
        'core-whois-shell.ask': 'اسأل',
        'core-whois-shell.sign_in_lookup': 'سجّل الدخول لإجراء بحث.',
        'core-whois-shell.type_name': 'اكتب اسمًا أعلاه.',
        'core-whois-shell.asking_registry':
          'يجري سؤال السجل… قد يستغرق ذلك بضع ثوانٍ.',
        'core-whois-shell.host_pending':
          'WHOIS غير متاح لهذا الحساب، لذلك طُلِب من جامع root تشغيله. سيجيب في تشغيله التالي — العد التنازلي أعلى الصفحة، وستحصل هذه الصفحة على الإجابة عند وصولها.',
        'core-whois-shell.host_pending_with_error':
          'WHOIS غير متاح لهذا الحساب، لذلك طُلِب من جامع root تشغيله. سيجيب في تشغيله التالي — العد التنازلي أعلى الصفحة، وستحصل هذه الصفحة على الإجابة عند وصولها. قالت المحاولة الأخيرة على المضيف: {{error}}',
        'core-whois-shell.lookup_failed': 'فشل البحث.',
        'core-whois-shell.host_answered':
          'أجاب جامع root {{when}} لأن WHOIS غير متاح لهذا الحساب.',
        'core-whois-shell.available_name':
          'يبدو أن {{name}} غير مسجّل. لا يملك السجل أي قيد له، وهذا ما يبدو عليه الاسم المتاح — لكن سجلًا لا يجيب قد يبدو كذلك أيضًا، لذا تحقق مع مسجّل قبل الاحتفال.',
        'core-whois-shell.no_recognized_date':
          'لم تتضمن الإجابة تاريخًا تتعرف عليه لوحة المعلومات هذه',
        'core-whois-shell.registered': 'مسجّل',
        'core-whois-shell.record_last_changed': 'آخر تغيير للسجل',
        'core-whois-shell.status': 'الحالة',
        'core-whois-shell.nameservers': 'خوادم الأسماء',
        'core-whois-shell.auto_renew': 'التجديد التلقائي',
        'core-whois-shell.registry_facts': 'ما يقوله السجل',
        'core-whois-shell.lock_meanings': 'ما تعنيه هذه الأقفال',
        'core-whois-shell.registry_wording': 'كما يكتبه السجل',
        'core-whois-shell.watch': 'راقب هذا النطاق',
        'core-whois-shell.now_watching': 'تجري الآن مراقبة {{domain}}.',
      }),
      zh: Object.freeze({
        'core-whois-shell.title': '查询一个名称',
        'core-whois-shell.help':
          '对未监视名称进行一次性查询：它是否已被注册、由谁持有、何时到期。不存储任何内容，也不会将域名添加到列表中；若查询结果让您想添加它，可以使用相应按钮。',
        'core-whois-shell.ask': '查询',
        'core-whois-shell.sign_in_lookup': '登录以运行查询。',
        'core-whois-shell.type_name': '请在上方输入名称。',
        'core-whois-shell.asking_registry': '正在询问注册机构…可能需要几秒钟。',
        'core-whois-shell.host_pending':
          '此账户无法使用 WHOIS，因此已请求 root 采集器运行它。它将在下次运行时答复——倒计时位于页面顶部，答复到达后将显示在此页面上。',
        'core-whois-shell.host_pending_with_error':
          '此账户无法使用 WHOIS，因此已请求 root 采集器运行它。它将在下次运行时答复——倒计时位于页面顶部，答复到达后将显示在此页面上。主机上的上次尝试显示：{{error}}',
        'core-whois-shell.lookup_failed': '查询失败。',
        'core-whois-shell.host_answered':
          '由于此账户无法使用 WHOIS，root 采集器已于 {{when}} 答复。',
        'core-whois-shell.available_name':
          '{{name}} 似乎未注册。注册机构没有其记录，这正是可用名称的样子——不过未响应的注册机构也可能看起来相同，因此请先向注册商确认。',
        'core-whois-shell.no_recognized_date': '答复中没有此仪表板可识别的日期',
        'core-whois-shell.registered': '已注册',
        'core-whois-shell.record_last_changed': '记录上次更改',
        'core-whois-shell.status': '状态',
        'core-whois-shell.nameservers': '名称服务器',
        'core-whois-shell.auto_renew': '自动续订',
        'core-whois-shell.registry_facts': '注册机构的答复',
        'core-whois-shell.lock_meanings': '这些锁定的含义',
        'core-whois-shell.registry_wording': '注册机构的原文',
        'core-whois-shell.watch': '监控此域名',
        'core-whois-shell.now_watching': '现已监控 {{domain}}。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-whois-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-whois-shell.${key}`],
    );
  },
  render() {},
};
