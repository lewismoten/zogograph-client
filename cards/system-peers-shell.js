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
