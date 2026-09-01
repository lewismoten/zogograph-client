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
        'core-audit-shell.everything': 'everything',
        'core-audit-shell.anyone': 'anyone',
        'core-audit-shell.any_result': 'any result',
        'core-audit-shell.succeeded': 'succeeded',
        'core-audit-shell.refused': 'refused',
        'core-audit-shell.failed': 'failed',
        'core-audit-shell.export_confirm':
          'Download the full audit history as CSV?\n\nIt can contain private paths and client addresses. Store the file securely.',
        'core-audit-shell.export_preparing': 'Preparing the full audit CSV…',
        'core-audit-shell.download_csv': 'Download CSV',
        'core-audit-shell.download_csv_title':
          'Download the full audit history as a CSV file',
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
        'core-audit-shell.everything': 'todo',
        'core-audit-shell.anyone': 'cualquiera',
        'core-audit-shell.any_result': 'cualquier resultado',
        'core-audit-shell.succeeded': 'correcto',
        'core-audit-shell.refused': 'rechazado',
        'core-audit-shell.failed': 'fallido',
        'core-audit-shell.export_confirm':
          '¿Descargar el historial completo de auditoría como CSV?\n\nPuede contener rutas privadas y direcciones de clientes. Guarde el archivo de forma segura.',
        'core-audit-shell.export_preparing':
          'Se está preparando el CSV completo de auditoría…',
        'core-audit-shell.download_csv': 'Descargar CSV',
        'core-audit-shell.download_csv_title':
          'Descargar todo el historial de auditoría como archivo CSV',
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
        'core-audit-shell.everything': 'सब कुछ',
        'core-audit-shell.anyone': 'कोई भी',
        'core-audit-shell.any_result': 'कोई भी परिणाम',
        'core-audit-shell.succeeded': 'सफल',
        'core-audit-shell.refused': 'अस्वीकृत',
        'core-audit-shell.failed': 'विफल',
        'core-audit-shell.export_confirm':
          'पूर्ण ऑडिट इतिहास CSV के रूप में डाउनलोड करें?\n\nइसमें निजी पथ और क्लाइंट पते हो सकते हैं। फ़ाइल को सुरक्षित रखें।',
        'core-audit-shell.export_preparing':
          'पूरा ऑडिट CSV तैयार किया जा रहा है…',
        'core-audit-shell.download_csv': 'CSV डाउनलोड करें',
        'core-audit-shell.download_csv_title':
          'पूरा ऑडिट इतिहास CSV फ़ाइल के रूप में डाउनलोड करें',
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
        'core-audit-shell.everything': 'كل شيء',
        'core-audit-shell.anyone': 'أي شخص',
        'core-audit-shell.any_result': 'أي نتيجة',
        'core-audit-shell.succeeded': 'نجح',
        'core-audit-shell.refused': 'رُفض',
        'core-audit-shell.failed': 'فشل',
        'core-audit-shell.export_confirm':
          'تنزيل سجل التدقيق الكامل بصيغة CSV؟\n\nقد يحتوي مسارات خاصة وعناوين عملاء. احفظ الملف بأمان.',
        'core-audit-shell.export_preparing':
          'جارٍ إعداد ملف CSV الكامل للتدقيق…',
        'core-audit-shell.download_csv': 'نزّل CSV',
        'core-audit-shell.download_csv_title':
          'نزّل سجل التدقيق الكامل كملف CSV',
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
        'core-audit-shell.everything': '全部',
        'core-audit-shell.anyone': '任何人',
        'core-audit-shell.any_result': '任何结果',
        'core-audit-shell.succeeded': '成功',
        'core-audit-shell.refused': '已拒绝',
        'core-audit-shell.failed': '失败',
        'core-audit-shell.export_confirm':
          '将完整审计历史下载为 CSV？\n\n其中可能包含私有路径和客户端地址。请妥善保存文件。',
        'core-audit-shell.export_preparing': '正在准备完整审计 CSV…',
        'core-audit-shell.download_csv': '下载 CSV',
        'core-audit-shell.download_csv_title': '将完整审计历史下载为 CSV 文件',
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
