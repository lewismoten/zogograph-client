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
      }),
      es: Object.freeze({
        'core-system-peers-shell.title': 'Servidores Zogograph de confianza',
        'core-system-peers-shell.help':
          'Un servidor de confianza se configura explícitamente aquí y se comprueba mediante descubrimiento HTTPS de OpenID Connect. Guardarlo no comparte datos ni inicia sesión a nadie; esos pasos de código de autorización son deliberadamente independientes.',
      }),
      hi: Object.freeze({
        'core-system-peers-shell.title': 'विश्वसनीय Zogograph सर्वर',
        'core-system-peers-shell.help':
          'विश्वसनीय सर्वर यहाँ स्पष्ट रूप से कॉन्फ़िगर होता है और HTTPS OpenID Connect डिस्कवरी से जाँचा जाता है। इसे सहेजने से डेटा साझा नहीं होता और न किसी का साइन-इन होता है; वे authorization-code चरण जानबूझकर अलग हैं।',
      }),
      ar: Object.freeze({
        'core-system-peers-shell.title': 'خوادم Zogograph الموثوقة',
        'core-system-peers-shell.help':
          'يُضبط خادم موثوق صراحة هنا ويُفحص عبر اكتشاف HTTPS OpenID Connect. حفظه لا يشارك بيانات ولا يسجل دخول أحد؛ خطوات رمز التفويض تلك منفصلة عمدًا.',
      }),
      zh: Object.freeze({
        'core-system-peers-shell.title': '受信任的 Zogograph 服务器',
        'core-system-peers-shell.help':
          '受信任服务器会在此明确配置，并通过 HTTPS OpenID Connect 发现进行检查。保存它不会共享数据或让任何人登录；这些授权代码步骤有意保持独立。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-peers-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-peers-shell.${key}`],
    );
  },
  render() {},
};
