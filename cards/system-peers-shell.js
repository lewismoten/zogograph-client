/* Core card package: static shell copy for trusted Zogograph servers. */
window.ZogographSystemPeersShellCard = {
  manifest: Object.freeze({
    id: 'core-system-peers-shell',
    locales: ['core-system-peers-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-peers-shell.title': 'Trusted Zogograph servers',
        'core-system-peers-shell.help':
          'A trusted server is explicitly configured here and checked through HTTPS OpenID Connect discovery. Saving it does not share data or sign anyone in; those authorization-code steps are deliberately separate.',
        'core-system-peers-shell.capacity':
          '{{edition}} edition: controller plus {{limit, number}} remote hosts. {{used, number}} configured; {{remaining, number}} remaining.',
        'core-system-peers-shell.add': 'Add trusted server',
        'core-system-peers-shell.capacity_reached':
          'This edition has reached its remote-host limit. Remove or replace a server to free a slot.',
        'core-system-peers-shell.add_help':
          'Add a trusted remote Zogograph server.',
        'core-system-peers-shell.none':
          'No trusted servers are configured yet.',
        'core-system-peers-shell.table_server': 'Server',
        'core-system-peers-shell.table_issuer': 'Issuer',
        'core-system-peers-shell.table_client': 'Client',
        'core-system-peers-shell.table_secret': 'Secret',
        'core-system-peers-shell.table_transport': 'Transport',
        'core-system-peers-shell.not_configured': 'not configured',
        'core-system-peers-shell.configured': 'configured',
        'core-system-peers-shell.not_needed': 'not needed',
        'core-system-peers-shell.not_paired': 'not paired',
        'core-system-peers-shell.awaiting_delivery':
          'awaiting first delivery ({{elapsed}})',
        'core-system-peers-shell.paired': 'paired',
        'core-system-peers-shell.table_last_delivery': 'Last delivery',
        'core-system-peers-shell.none_yet': 'none yet',
        'core-system-peers-shell.table_connected': 'Connected',
        'core-system-peers-shell.not_connected': 'not connected',
        'core-system-peers-shell.test': 'Test',
        'core-system-peers-shell.test_oidc': 'Test OIDC discovery',
        'core-system-peers-shell.delivery_separate':
          'Remote delivery is configured separately from OIDC.',
        'core-system-peers-shell.connected_to': 'Connected to {{issuer}}.',
        'core-system-peers-shell.reconnect': 'Reconnect',
        'core-system-peers-shell.connect': 'Connect',
        'core-system-peers-shell.view_data': 'View data',
        'core-system-peers-shell.overview_title': '{{name}} overview',
        'core-system-peers-shell.collected': 'Collected',
        'core-system-peers-shell.nothing_collected': 'nothing collected',
        'core-system-peers-shell.table_disk_free': 'Disk free',
        'core-system-peers-shell.table_cpu': 'CPU',
        'core-system-peers-shell.table_memory': 'Memory',
        'core-system-peers-shell.load': 'Load',
      }),
      es: Object.freeze({
        'core-system-peers-shell.title': 'Servidores Zogograph de confianza',
        'core-system-peers-shell.help':
          'Un servidor de confianza se configura explícitamente aquí y se comprueba mediante descubrimiento HTTPS de OpenID Connect. Guardarlo no comparte datos ni inicia sesión a nadie; esos pasos de código de autorización son deliberadamente independientes.',
        'core-system-peers-shell.capacity':
          'Edición {{edition}}: controlador y {{limit, number}} servidores remotos. {{used, number}} configurados; quedan {{remaining, number}}.',
        'core-system-peers-shell.add': 'Añadir servidor de confianza',
        'core-system-peers-shell.capacity_reached':
          'Esta edición alcanzó el límite de servidores remotos. Quite o sustituya un servidor para liberar una plaza.',
        'core-system-peers-shell.add_help':
          'Añada un servidor remoto Zogograph de confianza.',
        'core-system-peers-shell.none':
          'Aún no hay servidores de confianza configurados.',
        'core-system-peers-shell.table_server': 'Servidor',
        'core-system-peers-shell.table_issuer': 'Emisor',
        'core-system-peers-shell.table_client': 'Cliente',
        'core-system-peers-shell.table_secret': 'Secreto',
        'core-system-peers-shell.table_transport': 'Transporte',
        'core-system-peers-shell.not_configured': 'sin configurar',
        'core-system-peers-shell.configured': 'configurado',
        'core-system-peers-shell.not_needed': 'no necesario',
        'core-system-peers-shell.not_paired': 'sin vincular',
        'core-system-peers-shell.awaiting_delivery':
          'esperando la primera entrega ({{elapsed}})',
        'core-system-peers-shell.paired': 'vinculado',
        'core-system-peers-shell.table_last_delivery': 'Última entrega',
        'core-system-peers-shell.none_yet': 'aún no hay',
        'core-system-peers-shell.table_connected': 'Conectado',
        'core-system-peers-shell.not_connected': 'sin conectar',
        'core-system-peers-shell.test': 'Probar',
        'core-system-peers-shell.test_oidc': 'Probar descubrimiento OIDC',
        'core-system-peers-shell.delivery_separate':
          'La entrega remota se configura por separado de OIDC.',
        'core-system-peers-shell.connected_to': 'Conectado a {{issuer}}.',
        'core-system-peers-shell.reconnect': 'Reconectar',
        'core-system-peers-shell.connect': 'Conectar',
        'core-system-peers-shell.view_data': 'Ver datos',
        'core-system-peers-shell.overview_title': 'Resumen de {{name}}',
        'core-system-peers-shell.collected': 'Recogido',
        'core-system-peers-shell.nothing_collected': 'no se recogió nada',
        'core-system-peers-shell.table_disk_free': 'Espacio libre en disco',
        'core-system-peers-shell.table_cpu': 'CPU',
        'core-system-peers-shell.table_memory': 'Memoria',
        'core-system-peers-shell.load': 'Carga',
      }),
      hi: Object.freeze({
        'core-system-peers-shell.title': 'विश्वसनीय Zogograph सर्वर',
        'core-system-peers-shell.help':
          'विश्वसनीय सर्वर यहाँ स्पष्ट रूप से कॉन्फ़िगर होता है और HTTPS OpenID Connect डिस्कवरी से जाँचा जाता है। इसे सहेजने से डेटा साझा नहीं होता और न किसी का साइन-इन होता है; वे authorization-code चरण जानबूझकर अलग हैं।',
        'core-system-peers-shell.capacity':
          '{{edition}} संस्करण: नियंत्रक और {{limit, number}} दूरस्थ होस्ट। {{used, number}} कॉन्फ़िगर हैं; {{remaining, number}} शेष हैं।',
        'core-system-peers-shell.add': 'विश्वसनीय सर्वर जोड़ें',
        'core-system-peers-shell.capacity_reached':
          'यह संस्करण दूरस्थ-होस्ट सीमा तक पहुँच गया है। स्थान खाली करने के लिए सर्वर हटाएँ या बदलें।',
        'core-system-peers-shell.add_help':
          'विश्वसनीय दूरस्थ Zogograph सर्वर जोड़ें।',
        'core-system-peers-shell.none':
          'अभी कोई विश्वसनीय सर्वर कॉन्फ़िगर नहीं है।',
        'core-system-peers-shell.table_server': 'सर्वर',
        'core-system-peers-shell.table_issuer': 'जारीकर्ता',
        'core-system-peers-shell.table_client': 'क्लाइंट',
        'core-system-peers-shell.table_secret': 'गुप्त',
        'core-system-peers-shell.table_transport': 'परिवहन',
        'core-system-peers-shell.not_configured': 'कॉन्फ़िगर नहीं',
        'core-system-peers-shell.configured': 'कॉन्फ़िगर',
        'core-system-peers-shell.not_needed': 'आवश्यक नहीं',
        'core-system-peers-shell.not_paired': 'युग्मित नहीं',
        'core-system-peers-shell.awaiting_delivery':
          'पहली डिलीवरी की प्रतीक्षा ({{elapsed}})',
        'core-system-peers-shell.paired': 'युग्मित',
        'core-system-peers-shell.table_last_delivery': 'पिछली डिलीवरी',
        'core-system-peers-shell.none_yet': 'अभी तक नहीं',
        'core-system-peers-shell.table_connected': 'कनेक्टेड',
        'core-system-peers-shell.not_connected': 'कनेक्ट नहीं',
        'core-system-peers-shell.test': 'जाँचें',
        'core-system-peers-shell.test_oidc': 'OIDC खोज जाँचें',
        'core-system-peers-shell.delivery_separate':
          'दूरस्थ डिलीवरी OIDC से अलग कॉन्फ़िगर की जाती है।',
        'core-system-peers-shell.connected_to': '{{issuer}} से कनेक्ट है।',
        'core-system-peers-shell.reconnect': 'फिर कनेक्ट करें',
        'core-system-peers-shell.connect': 'कनेक्ट करें',
        'core-system-peers-shell.view_data': 'डेटा देखें',
        'core-system-peers-shell.overview_title': '{{name}} अवलोकन',
        'core-system-peers-shell.collected': 'संग्रहित',
        'core-system-peers-shell.nothing_collected': 'कुछ भी एकत्र नहीं हुआ',
        'core-system-peers-shell.table_disk_free': 'खाली डिस्क',
        'core-system-peers-shell.table_cpu': 'CPU',
        'core-system-peers-shell.table_memory': 'मेमोरी',
        'core-system-peers-shell.load': 'लोड',
      }),
      ar: Object.freeze({
        'core-system-peers-shell.title': 'خوادم Zogograph الموثوقة',
        'core-system-peers-shell.help':
          'يُضبط خادم موثوق صراحة هنا ويُفحص عبر اكتشاف HTTPS OpenID Connect. حفظه لا يشارك بيانات ولا يسجل دخول أحد؛ خطوات رمز التفويض تلك منفصلة عمدًا.',
        'core-system-peers-shell.capacity':
          'إصدار {{edition}}: وحدة التحكم و{{limit, number}} مضيفًا بعيدًا. تم إعداد {{used, number}}؛ المتبقي {{remaining, number}}.',
        'core-system-peers-shell.add': 'أضف خادمًا موثوقًا',
        'core-system-peers-shell.capacity_reached':
          'بلغ هذا الإصدار حد المضيفين البعيدين. أزل خادمًا أو استبدله لتحرير مكان.',
        'core-system-peers-shell.add_help':
          'أضف خادم Zogograph بعيدًا موثوقًا.',
        'core-system-peers-shell.none': 'لا توجد خوادم موثوقة مُعَدّة بعد.',
        'core-system-peers-shell.table_server': 'الخادم',
        'core-system-peers-shell.table_issuer': 'المُصدِر',
        'core-system-peers-shell.table_client': 'العميل',
        'core-system-peers-shell.table_secret': 'السر',
        'core-system-peers-shell.table_transport': 'النقل',
        'core-system-peers-shell.not_configured': 'غير مُعَدّ',
        'core-system-peers-shell.configured': 'مُعَدّ',
        'core-system-peers-shell.not_needed': 'غير مطلوب',
        'core-system-peers-shell.not_paired': 'غير مقترن',
        'core-system-peers-shell.awaiting_delivery':
          'بانتظار أول تسليم ({{elapsed}})',
        'core-system-peers-shell.paired': 'مقترن',
        'core-system-peers-shell.table_last_delivery': 'آخر تسليم',
        'core-system-peers-shell.none_yet': 'لا شيء بعد',
        'core-system-peers-shell.table_connected': 'متصل',
        'core-system-peers-shell.not_connected': 'غير متصل',
        'core-system-peers-shell.test': 'اختبر',
        'core-system-peers-shell.test_oidc': 'اختبر اكتشاف OIDC',
        'core-system-peers-shell.delivery_separate':
          'يُضبط التسليم البعيد بشكل منفصل عن OIDC.',
        'core-system-peers-shell.connected_to': 'متصل بـ {{issuer}}.',
        'core-system-peers-shell.reconnect': 'أعد الاتصال',
        'core-system-peers-shell.connect': 'اتصل',
        'core-system-peers-shell.view_data': 'اعرض البيانات',
        'core-system-peers-shell.overview_title': 'نظرة عامة على {{name}}',
        'core-system-peers-shell.collected': 'جُمعت',
        'core-system-peers-shell.nothing_collected': 'لم يُجمع شيء',
        'core-system-peers-shell.table_disk_free': 'مساحة القرص الحرة',
        'core-system-peers-shell.table_cpu': 'المعالج',
        'core-system-peers-shell.table_memory': 'الذاكرة',
        'core-system-peers-shell.load': 'الحمل',
      }),
      zh: Object.freeze({
        'core-system-peers-shell.title': '受信任的 Zogograph 服务器',
        'core-system-peers-shell.help':
          '受信任服务器会在此明确配置，并通过 HTTPS OpenID Connect 发现进行检查。保存它不会共享数据或让任何人登录；这些授权代码步骤有意保持独立。',
        'core-system-peers-shell.capacity':
          '{{edition}} 版本：控制器加 {{limit, number}} 个远程主机。已配置 {{used, number}} 个；还可配置 {{remaining, number}} 个。',
        'core-system-peers-shell.add': '添加受信任服务器',
        'core-system-peers-shell.capacity_reached':
          '此版本已达到远程主机数量限制。请删除或更换服务器以释放名额。',
        'core-system-peers-shell.add_help':
          '添加受信任的远程 Zogograph 服务器。',
        'core-system-peers-shell.none': '尚未配置受信任的服务器。',
        'core-system-peers-shell.table_server': '服务器',
        'core-system-peers-shell.table_issuer': '颁发者',
        'core-system-peers-shell.table_client': '客户端',
        'core-system-peers-shell.table_secret': '密钥',
        'core-system-peers-shell.table_transport': '传输',
        'core-system-peers-shell.not_configured': '未配置',
        'core-system-peers-shell.configured': '已配置',
        'core-system-peers-shell.not_needed': '不需要',
        'core-system-peers-shell.not_paired': '未配对',
        'core-system-peers-shell.awaiting_delivery':
          '等待首次传送（{{elapsed}}）',
        'core-system-peers-shell.paired': '已配对',
        'core-system-peers-shell.table_last_delivery': '上次传送',
        'core-system-peers-shell.none_yet': '尚无',
        'core-system-peers-shell.table_connected': '已连接',
        'core-system-peers-shell.not_connected': '未连接',
        'core-system-peers-shell.test': '测试',
        'core-system-peers-shell.test_oidc': '测试 OIDC 发现',
        'core-system-peers-shell.delivery_separate':
          '远程传送与 OIDC 分开配置。',
        'core-system-peers-shell.connected_to': '已连接到 {{issuer}}。',
        'core-system-peers-shell.reconnect': '重新连接',
        'core-system-peers-shell.connect': '连接',
        'core-system-peers-shell.view_data': '查看数据',
        'core-system-peers-shell.overview_title': '{{name}} 概览',
        'core-system-peers-shell.collected': '已收集',
        'core-system-peers-shell.nothing_collected': '未收集任何内容',
        'core-system-peers-shell.table_disk_free': '可用磁盘空间',
        'core-system-peers-shell.table_cpu': 'CPU',
        'core-system-peers-shell.table_memory': '内存',
        'core-system-peers-shell.load': '负载',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-system-peers-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-system-peers-shell.${key}`],
    );
  },
  render() {},
};
