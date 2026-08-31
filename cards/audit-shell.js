/* Core package: static guidance for the audit trail page. */
window.ZogographAuditShellCard = {
  manifest: Object.freeze({
    id: 'core-audit-shell',
    locales: ['core-audit-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-audit-shell.title': 'Everything this monitor has done',
        'core-audit-shell.help':
          'Every deletion, every scan, every request to the host collector, every email it sent, every configuration change and every sign-in — whether each worked or not. A refusal is kept as carefully as a success: knowing what was {{not}} done is half of knowing what happened.',
      }),
      es: Object.freeze({
        'core-audit-shell.title': 'Todo lo que ha hecho este monitor',
        'core-audit-shell.help':
          'Cada eliminación, cada análisis, cada solicitud al recolector del host, cada correo que envió, cada cambio de configuración y cada inicio de sesión, funcionaran o no. Una negativa se conserva con tanto cuidado como un éxito: saber qué no se hizo es la mitad de saber lo que ocurrió.',
      }),
      hi: Object.freeze({
        'core-audit-shell.title': 'इस मॉनिटर ने जो कुछ किया',
        'core-audit-shell.help':
          'हर हटाना, हर स्कैन, होस्ट कलेक्टर को हर अनुरोध, भेजा हर ईमेल, हर कॉन्फ़िगरेशन बदलाव और हर साइन-इन—वह सफल हुआ या नहीं। अस्वीकृति भी सफलता की तरह सावधानी से रखी जाती है: क्या {{not}} किया गया, यह जानना क्या हुआ जानने का आधा भाग है।',
      }),
      ar: Object.freeze({
        'core-audit-shell.title': 'كل ما فعله هذا المراقب',
        'core-audit-shell.help':
          'كل حذف وكل فحص وكل طلب إلى جامع المضيف وكل بريد أرسله وكل تغيير إعداد وكل تسجيل دخول، سواء نجح أم لا. يُحتفظ بالرفض بعناية كالنجاح: معرفة ما {{not}} فُعل نصف معرفة ما حدث.',
      }),
      zh: Object.freeze({
        'core-audit-shell.title': '此监控器完成的所有操作',
        'core-audit-shell.help':
          '每一次删除、扫描、向主机收集器发出的请求、发送的电子邮件、配置变更和登录，不论是否成功。拒绝记录和成功记录一样被仔细保留：了解什么{{not}}做，是了解发生了什么的一半。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-audit-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-audit-shell.${key}`],
    );
  },
  render() {},
};
