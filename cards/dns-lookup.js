/* Core card package: live DNS lookup progress and answer labels. */
window.ZogographDnsLookupCard = {
  manifest: Object.freeze({
    id: 'core-dns-lookup',
    locales: ['core-dns-lookup.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-dns-lookup.sign_in_lookup': 'Sign in to run name lookups.',
        'core-dns-lookup.query_wait':
          'Asking… a server that does not reply takes three seconds to give up on, so this can take a few.',
        'core-dns-lookup.every_type': 'Every type',
        'core-dns-lookup.asked_for_server':
          'Asked for {{name}} {{type}} at {{server}}',
        'core-dns-lookup.asked_for': 'Asked for {{name}} {{type}}',
        'core-dns-lookup.default_checks': 'The default checks',
        'core-dns-lookup.query_freshness':
          '{{asked}}, run {{at}}. These do not refresh on their own — each one sends a real query, and a page that repeated them every few seconds would be adding to the traffic it is measuring.',
        'core-dns-lookup.machine_nameserver':
          'This machine is listening on port 53, so it is a nameserver. Its own traffic will dwarf everything else here — see What is on the wire.',
        'core-dns-lookup.answered': 'Answered',
        'core-dns-lookup.asked_server': 'Asked {{server}}',
        'core-dns-lookup.ttl_value': 'TTL {{duration}}',
        'core-dns-lookup.look_up_all': 'Look it all up',
      }),
      es: Object.freeze({
        'core-dns-lookup.sign_in_lookup':
          'Inicie sesión para ejecutar consultas de nombres.',
        'core-dns-lookup.query_wait':
          'Consultando… un servidor que no responde tarda tres segundos en abandonarse, así que esto puede tardar unos segundos.',
        'core-dns-lookup.every_type': 'Todos los tipos',
        'core-dns-lookup.asked_for_server':
          'Consulta de {{name}} {{type}} en {{server}}',
        'core-dns-lookup.asked_for': 'Consulta de {{name}} {{type}}',
        'core-dns-lookup.default_checks': 'Las comprobaciones predeterminadas',
        'core-dns-lookup.query_freshness':
          '{{asked}}, ejecutado {{at}}. No se actualizan por sí solos: cada uno envía una consulta real, y una página que los repitiera cada pocos segundos añadiría tráfico a lo que mide.',
        'core-dns-lookup.machine_nameserver':
          'Esta máquina escucha en el puerto 53, por lo que es un servidor de nombres. Su propio tráfico empequeñecerá todo lo demás aquí; consulte Qué hay en el cable.',
        'core-dns-lookup.answered': 'Respondió',
        'core-dns-lookup.asked_server': 'Consulta a {{server}}',
        'core-dns-lookup.ttl_value': 'TTL {{duration}}',
        'core-dns-lookup.look_up_all': 'Buscarlo todo',
      }),
      hi: Object.freeze({
        'core-dns-lookup.sign_in_lookup':
          'नाम लुकअप चलाने के लिए साइन इन करें।',
        'core-dns-lookup.query_wait':
          'पूछा जा रहा है… जो सर्वर जवाब नहीं देता उसे छोड़ने में तीन सेकंड लगते हैं, इसलिए इसमें कुछ समय लग सकता है।',
        'core-dns-lookup.every_type': 'सभी प्रकार',
        'core-dns-lookup.asked_for_server':
          '{{server}} पर {{name}} {{type}} के लिए पूछा गया',
        'core-dns-lookup.asked_for': '{{name}} {{type}} के लिए पूछा गया',
        'core-dns-lookup.default_checks': 'डिफ़ॉल्ट जाँचें',
        'core-dns-lookup.query_freshness':
          '{{asked}}, {{at}} पर चलाया गया। ये अपने आप रीफ़्रेश नहीं होते: हर एक वास्तविक क्वेरी भेजता है, और जो पृष्ठ इन्हें हर कुछ सेकंड में दोहराता है वह अपने मापे जा रहे ट्रैफ़िक में जोड़ देगा।',
        'core-dns-lookup.machine_nameserver':
          'यह मशीन पोर्ट 53 पर सुन रही है, इसलिए यह नेमसर्वर है। इसका अपना ट्रैफ़िक यहाँ बाकी सभी चीज़ों से बहुत अधिक होगा — वायर पर क्या है देखें।',
        'core-dns-lookup.answered': 'उत्तर दिया',
        'core-dns-lookup.asked_server': '{{server}} से पूछा गया',
        'core-dns-lookup.ttl_value': 'TTL {{duration}}',
        'core-dns-lookup.look_up_all': 'सब कुछ देखें',
      }),
      ar: Object.freeze({
        'core-dns-lookup.sign_in_lookup':
          'سجّل الدخول لإجراء عمليات بحث عن الأسماء.',
        'core-dns-lookup.query_wait':
          'يجري السؤال… يستغرق التخلي عن خادم لا يرد ثلاث ثوانٍ، لذا قد يستغرق هذا بضعًا منها.',
        'core-dns-lookup.every_type': 'كل الأنواع',
        'core-dns-lookup.asked_for_server':
          'طُلب {{name}} {{type}} من {{server}}',
        'core-dns-lookup.asked_for': 'طُلب {{name}} {{type}}',
        'core-dns-lookup.default_checks': 'الفحوصات الافتراضية',
        'core-dns-lookup.query_freshness':
          '{{asked}}، تم التشغيل في {{at}}. لا تُحدَّث هذه تلقائيًا: كل واحدة ترسل استعلامًا حقيقيًا، والصفحة التي تكررها كل بضع ثوانٍ ستضيف إلى حركة المرور التي تقيسها.',
        'core-dns-lookup.machine_nameserver':
          'تستمع هذه الآلة على المنفذ 53، لذا فهي خادم أسماء. ستفوق حركتها الخاصة كل شيء آخر هنا — راجع ما يوجد على الشبكة.',
        'core-dns-lookup.answered': 'أجاب',
        'core-dns-lookup.asked_server': 'تم سؤال {{server}}',
        'core-dns-lookup.ttl_value': 'TTL {{duration}}',
        'core-dns-lookup.look_up_all': 'ابحث عنها كلها',
      }),
      zh: Object.freeze({
        'core-dns-lookup.sign_in_lookup': '登录以运行名称查询。',
        'core-dns-lookup.query_wait':
          '正在查询…放弃无响应的服务器需要三秒钟，因此可能需要一些时间。',
        'core-dns-lookup.every_type': '所有类型',
        'core-dns-lookup.asked_for_server':
          '在 {{server}} 查询 {{name}} {{type}}',
        'core-dns-lookup.asked_for': '查询 {{name}} {{type}}',
        'core-dns-lookup.default_checks': '默认检查',
        'core-dns-lookup.query_freshness':
          '{{asked}}，运行于 {{at}}。它们不会自行刷新：每项都会发送实际查询，而每隔几秒重复查询的页面会增加其正在测量的流量。',
        'core-dns-lookup.machine_nameserver':
          '此机器正在端口 53 上侦听，因此它是名称服务器。它自身的流量将远超此处其他所有流量——请查看线路上有什么。',
        'core-dns-lookup.answered': '已答复',
        'core-dns-lookup.asked_server': '已查询 {{server}}',
        'core-dns-lookup.ttl_value': 'TTL {{duration}}',
        'core-dns-lookup.look_up_all': '全部查询',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-dns-lookup.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
