/* Core card package: static shell copy for the dashboard problem log. */
window.ZogographSystemProblemsShellCard = {
  manifest: Object.freeze({
    id: 'core-system-problems-shell',
    locales: ['core-system-problems-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-problems-shell.title': 'Recent problems',
        'core-system-problems-shell.clear_log': 'Clear the log',
        'core-system-problems-shell.help':
          'This dashboard’s own log. When a request fails it shows you a reference — find the same reference here for what actually happened. Identical faults are counted together rather than listed each time: the dashboard polls, every poll calls the API, and one broken function becomes forty lines in a minute.',
      }),
      es: Object.freeze({
        'core-system-problems-shell.title': 'Problemas recientes',
        'core-system-problems-shell.clear_log': 'Limpiar el registro',
        'core-system-problems-shell.help':
          'El propio registro de este panel. Cuando falla una solicitud le muestra una referencia: busque aquí la misma referencia para saber qué ocurrió realmente. Los fallos idénticos se cuentan juntos en vez de enumerarse cada vez: el panel sondea, cada sondeo llama a la API y una función rota se convierte en cuarenta líneas en un minuto.',
      }),
      hi: Object.freeze({
        'core-system-problems-shell.title': 'हाल की समस्याएँ',
        'core-system-problems-shell.clear_log': 'लॉग साफ़ करें',
        'core-system-problems-shell.help':
          'इस डैशबोर्ड का अपना लॉग। अनुरोध विफल होने पर यह आपको एक संदर्भ दिखाता है—वास्तव में क्या हुआ जानने के लिए वही संदर्भ यहाँ खोजें। एक जैसे दोष हर बार सूचीबद्ध होने के बजाय साथ गिने जाते हैं: डैशबोर्ड पोल करता है, हर पोल API को कॉल करता है और एक टूटा फ़ंक्शन एक मिनट में चालीस पंक्तियाँ बन जाता है।',
      }),
      ar: Object.freeze({
        'core-system-problems-shell.title': 'المشكلات الأخيرة',
        'core-system-problems-shell.clear_log': 'امسح السجل',
        'core-system-problems-shell.help':
          'سجل لوحة المعلومات هذه نفسها. حين يفشل طلب يعرض لك مرجعًا — ابحث هنا عن المرجع نفسه لمعرفة ما حدث فعليًا. تُعد الأعطال المتطابقة معًا بدل سردها في كل مرة: تستطلع اللوحة، وكل استطلاع يستدعي API، فتتحول دالة معطلة واحدة إلى أربعين سطرًا في دقيقة.',
      }),
      zh: Object.freeze({
        'core-system-problems-shell.title': '最近的问题',
        'core-system-problems-shell.clear_log': '清除日志',
        'core-system-problems-shell.help':
          '此仪表板自身的日志。请求失败时会显示一个引用编号——在这里查找相同编号即可了解实际发生了什么。相同故障会合并计数而非每次都列出：仪表板轮询，每次轮询都会调用 API，一个损坏的函数一分钟内就会变成四十行记录。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-problems-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-problems-shell.${key}`],
    );
  },
  render() {},
};
