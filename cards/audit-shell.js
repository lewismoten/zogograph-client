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
        'core-audit-shell.clear': 'Clear audit',
        'core-audit-shell.clear_confirm':
          'Clear every earlier audit entry?\n\nThis action stays as the first new audit entry.',
        'core-audit-shell.clear_result':
          'Cleared {{entries}}. The reset is recorded.',
        'core-audit-shell.what': 'What',
        'core-audit-shell.who': 'Who',
        'core-audit-shell.result': 'Result',
      }),
      es: Object.freeze({
        'core-audit-shell.title': 'Todo lo que ha hecho este monitor',
        'core-audit-shell.help':
          'Cada eliminación, cada análisis, cada solicitud al recolector del host, cada correo que envió, cada cambio de configuración y cada inicio de sesión, funcionaran o no. Una negativa se conserva con tanto cuidado como un éxito: saber qué no se hizo es la mitad de saber lo que ocurrió.',
        'core-audit-shell.clear': 'Limpiar auditoría',
        'core-audit-shell.clear_confirm':
          '¿Borrar todas las entradas de auditoría anteriores?\n\nEsta acción queda como la primera entrada de auditoría nueva.',
        'core-audit-shell.clear_result':
          'Se borraron {{entries}}. El restablecimiento quedó registrado.',
        'core-audit-shell.what': 'Qué',
        'core-audit-shell.who': 'Quién',
        'core-audit-shell.result': 'Resultado',
      }),
      hi: Object.freeze({
        'core-audit-shell.title': 'इस मॉनिटर ने जो कुछ किया',
        'core-audit-shell.help':
          'हर हटाना, हर स्कैन, होस्ट कलेक्टर को हर अनुरोध, भेजा हर ईमेल, हर कॉन्फ़िगरेशन बदलाव और हर साइन-इन—वह सफल हुआ या नहीं। अस्वीकृति भी सफलता की तरह सावधानी से रखी जाती है: क्या {{not}} किया गया, यह जानना क्या हुआ जानने का आधा भाग है।',
        'core-audit-shell.clear': 'ऑडिट साफ़ करें',
        'core-audit-shell.clear_confirm':
          'पहले की हर ऑडिट प्रविष्टि साफ़ करें?\n\nयह कार्रवाई पहली नई ऑडिट प्रविष्टि के रूप में रहेगी।',
        'core-audit-shell.clear_result':
          '{{entries}} साफ़ की गईं। रीसेट दर्ज किया गया है।',
        'core-audit-shell.what': 'क्या',
        'core-audit-shell.who': 'कौन',
        'core-audit-shell.result': 'परिणाम',
      }),
      ar: Object.freeze({
        'core-audit-shell.title': 'كل ما فعله هذا المراقب',
        'core-audit-shell.help':
          'كل حذف وكل فحص وكل طلب إلى جامع المضيف وكل بريد أرسله وكل تغيير إعداد وكل تسجيل دخول، سواء نجح أم لا. يُحتفظ بالرفض بعناية كالنجاح: معرفة ما {{not}} فُعل نصف معرفة ما حدث.',
        'core-audit-shell.clear': 'امسح التدقيق',
        'core-audit-shell.clear_confirm':
          'مسح كل إدخالات التدقيق السابقة؟\n\nيبقى هذا الإجراء أول إدخال تدقيق جديد.',
        'core-audit-shell.clear_result':
          'تم مسح {{entries}}. سُجلت إعادة الضبط.',
        'core-audit-shell.what': 'ماذا',
        'core-audit-shell.who': 'من',
        'core-audit-shell.result': 'النتيجة',
      }),
      zh: Object.freeze({
        'core-audit-shell.title': '此监控器完成的所有操作',
        'core-audit-shell.help':
          '每一次删除、扫描、向主机收集器发出的请求、发送的电子邮件、配置变更和登录，不论是否成功。拒绝记录和成功记录一样被仔细保留：了解什么{{not}}做，是了解发生了什么的一半。',
        'core-audit-shell.clear': '清除审计记录',
        'core-audit-shell.clear_confirm':
          '清除所有早期审计条目？\n\n此操作会保留为第一条新审计条目。',
        'core-audit-shell.clear_result': '已清除 {{entries}}。已记录此次重置。',
        'core-audit-shell.what': '内容',
        'core-audit-shell.who': '谁',
        'core-audit-shell.result': '结果',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-audit-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-audit-shell.${key}`],
    );
  },
  render() {},
};
