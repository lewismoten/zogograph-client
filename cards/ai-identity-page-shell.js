/* Core card package: static shell for the assistant identity page. */
window.ZogographAiIdentityPageShellCard = {
  manifest: Object.freeze({
    id: 'core-ai-identity-page-shell',
    locales: ['core-ai-identity-page-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-identity-page-shell.title': '{{assistant}}',
        'core-ai-identity-page-shell.help':
          '{{assistant}} is a model you configure, given a fixed set of read-only tools for looking at this machine — the name is the job: somebody on watch who tells you what they can see. It is off until you set it up, and when it is on this tool sends parts of your logs, your disk figures and your process list to whichever service you point it at — which is worth deciding deliberately rather than discovering. A local model needs no key and nothing leaves the machine.',
        'core-ai-identity-page-shell.security':
          'The model can never write a query. Every tool is a fixed statement with typed, enumerated parameters bound to it, and anything outside those bounds is refused before it reaches a database. The rest of the settings are on the pages under this one.',
      }),
      es: Object.freeze({
        'core-ai-identity-page-shell.title': '{{assistant}}',
        'core-ai-identity-page-shell.help':
          '{{assistant}} es un modelo que usted configura y al que se le da un conjunto fijo de herramientas de solo lectura para mirar esta máquina: el nombre es el trabajo, alguien de guardia que le dice lo que puede ver. Está apagado hasta que lo configure y, cuando está activado, esta herramienta envía partes de sus registros, cifras de disco y lista de procesos al servicio que usted indique; es mejor decidirlo deliberadamente que descubrirlo después. Un modelo local no necesita clave y nada sale de la máquina.',
        'core-ai-identity-page-shell.security':
          'El modelo nunca puede escribir una consulta. Cada herramienta es una instrucción fija con parámetros tipados y enumerados vinculados a ella; todo lo que quede fuera de esos límites se rechaza antes de llegar a una base de datos. El resto de ajustes está en las páginas inferiores.',
      }),
      hi: Object.freeze({
        'core-ai-identity-page-shell.title': '{{assistant}}',
        'core-ai-identity-page-shell.help':
          '{{assistant}} एक मॉडल है जिसे आप कॉन्फ़िगर करते हैं और जिसे इस मशीन को देखने के लिए केवल-पढ़ने योग्य टूल का तय सेट मिलता है—नाम ही काम है: निगरानी पर कोई जो बताता है कि वह क्या देख सकता है। इसे सेट करने तक यह बंद है और चालू होने पर यह टूल आपके लॉग, डिस्क आंकड़े और प्रोसेस सूची के भाग उस सेवा को भेजता है जिस पर आप इसे इंगित करते हैं—इसे बाद में पता चलने के बजाय सोच-समझकर तय करना बेहतर है। स्थानीय मॉडल को कोई कुंजी नहीं चाहिए और मशीन से कुछ बाहर नहीं जाता।',
        'core-ai-identity-page-shell.security':
          'मॉडल कभी क्वेरी लिख नहीं सकता। हर टूल उससे जुड़े टाइप किए हुए, गणना किए गए पैरामीटर वाला निश्चित कथन है और उन सीमाओं से बाहर की हर चीज़ डेटाबेस तक पहुँचने से पहले अस्वीकार हो जाती है। बाकी सेटिंग नीचे के पृष्ठों पर हैं।',
      }),
      ar: Object.freeze({
        'core-ai-identity-page-shell.title': '{{assistant}}',
        'core-ai-identity-page-shell.help':
          '{{assistant}} نموذج تضبطه، يُمنح مجموعة ثابتة من أدوات القراءة فقط للنظر إلى هذه الآلة — الاسم هو العمل: شخص مناوب يخبرك بما يراه. يكون معطلاً حتى تعده، وعند تشغيله ترسل هذه الأداة أجزاء من سجلاتك وأرقام قرصك وقائمة عملياتك إلى الخدمة التي توجهه إليها، وهو قرار يستحق أن تتخذه عمدًا لا أن تكتشفه لاحقًا. لا يحتاج النموذج المحلي مفتاحًا ولا يغادر شيء الآلة.',
        'core-ai-identity-page-shell.security':
          'لا يمكن للنموذج أن يكتب استعلامًا أبدًا. كل أداة عبارة ثابتة مرتبطة بمعلمات مُحددة النوع ومعدودة، وأي شيء خارج تلك الحدود يُرفض قبل وصوله إلى قاعدة بيانات. بقية الإعدادات في الصفحات أسفل هذه الصفحة.',
      }),
      zh: Object.freeze({
        'core-ai-identity-page-shell.title': '{{assistant}}',
        'core-ai-identity-page-shell.help':
          '{{assistant}} 是一个由您配置的模型，拥有一组用于查看此机器的固定只读工具——名称就是它的工作：值守人员，告诉您它能看到什么。在您完成设置之前它处于关闭状态；开启后，此工具会将您的部分日志、磁盘数据和进程列表发送到您指定的服务，因此应当有意识地决定，而不是事后才发现。本地模型不需要密钥，任何内容都不会离开机器。',
        'core-ai-identity-page-shell.security':
          '模型永远不能写入查询。每个工具都是绑定了带类型、枚举参数的固定语句，超出这些边界的任何内容在到达数据库前都会被拒绝。其余设置位于此页下面的页面中。',
      }),
    }),
  }),
  render(input, helpers) {
    const assistant = '\uE000assistant\uE001';
    const t = (key, fallback, values = {}) =>
      helpers.i18n.t(`core-ai-identity-page-shell.${key}`, values, fallback);
    return {
      assistant: input.assistant,
      title: t('title', '{{assistant}}', { assistant }),
      help: t(
        'help',
        '{{assistant}} is a model you configure, given a fixed set of read-only tools for looking at this machine — the name is the job: somebody on watch who tells you what they can see. It is off until you set it up, and when it is on this tool sends parts of your logs, your disk figures and your process list to whichever service you point it at — which is worth deciding deliberately rather than discovering. A local model needs no key and nothing leaves the machine.',
        { assistant },
      ),
      security: t(
        'security',
        'The model can never write a query. Every tool is a fixed statement with typed, enumerated parameters bound to it, and anything outside those bounds is refused before it reaches a database. The rest of the settings are on the pages under this one.',
      ),
    };
  },
};
