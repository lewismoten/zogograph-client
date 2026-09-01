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
        'core-system-problems-shell.last_seen': 'Last seen',
        'core-system-problems-shell.times': 'Times',
        'core-system-problems-shell.level': 'Level',
        'core-system-problems-shell.where': 'Where',
        'core-system-problems-shell.what_happened': 'What happened',
        'core-system-problems-shell.reference': 'Reference',
        'core-system-problems-shell.none': 'Nothing has gone wrong. Good.',
        'core-system-problems-shell.path_entry_one':
          '{{count, number}} entry in {{path}}',
        'core-system-problems-shell.path_entry_many':
          '{{count, number}} entries in {{path}}',
      }),
      es: Object.freeze({
        'core-system-problems-shell.title': 'Problemas recientes',
        'core-system-problems-shell.clear_log': 'Limpiar el registro',
        'core-system-problems-shell.help':
          'El propio registro de este panel. Cuando falla una solicitud le muestra una referencia: busque aquí la misma referencia para saber qué ocurrió realmente. Los fallos idénticos se cuentan juntos en vez de enumerarse cada vez: el panel sondea, cada sondeo llama a la API y una función rota se convierte en cuarenta líneas en un minuto.',
        'core-system-problems-shell.last_seen': 'Visto por última vez',
        'core-system-problems-shell.times': 'Veces',
        'core-system-problems-shell.level': 'Nivel',
        'core-system-problems-shell.where': 'Dónde',
        'core-system-problems-shell.what_happened': 'Qué ocurrió',
        'core-system-problems-shell.reference': 'Referencia',
        'core-system-problems-shell.none': 'No ha salido nada mal. Bien.',
        'core-system-problems-shell.path_entry_one':
          '{{count, number}} entrada en {{path}}',
        'core-system-problems-shell.path_entry_many':
          '{{count, number}} entradas en {{path}}',
      }),
      hi: Object.freeze({
        'core-system-problems-shell.title': 'हाल की समस्याएँ',
        'core-system-problems-shell.clear_log': 'लॉग साफ़ करें',
        'core-system-problems-shell.help':
          'इस डैशबोर्ड का अपना लॉग। अनुरोध विफल होने पर यह आपको एक संदर्भ दिखाता है—वास्तव में क्या हुआ जानने के लिए वही संदर्भ यहाँ खोजें। एक जैसे दोष हर बार सूचीबद्ध होने के बजाय साथ गिने जाते हैं: डैशबोर्ड पोल करता है, हर पोल API को कॉल करता है और एक टूटा फ़ंक्शन एक मिनट में चालीस पंक्तियाँ बन जाता है।',
        'core-system-problems-shell.last_seen': 'अंतिम बार देखा',
        'core-system-problems-shell.times': 'बार',
        'core-system-problems-shell.level': 'स्तर',
        'core-system-problems-shell.where': 'कहाँ',
        'core-system-problems-shell.what_happened': 'क्या हुआ',
        'core-system-problems-shell.reference': 'संदर्भ',
        'core-system-problems-shell.none': 'कुछ गलत नहीं हुआ। अच्छा।',
        'core-system-problems-shell.path_entry_one':
          '{{path}} में {{count, number}} प्रविष्टि',
        'core-system-problems-shell.path_entry_many':
          '{{path}} में {{count, number}} प्रविष्टियाँ',
      }),
      ar: Object.freeze({
        'core-system-problems-shell.title': 'المشكلات الأخيرة',
        'core-system-problems-shell.clear_log': 'امسح السجل',
        'core-system-problems-shell.help':
          'سجل لوحة المعلومات هذه نفسها. حين يفشل طلب يعرض لك مرجعًا — ابحث هنا عن المرجع نفسه لمعرفة ما حدث فعليًا. تُعد الأعطال المتطابقة معًا بدل سردها في كل مرة: تستطلع اللوحة، وكل استطلاع يستدعي API، فتتحول دالة معطلة واحدة إلى أربعين سطرًا في دقيقة.',
        'core-system-problems-shell.last_seen': 'آخر ظهور',
        'core-system-problems-shell.times': 'مرات',
        'core-system-problems-shell.level': 'المستوى',
        'core-system-problems-shell.where': 'المكان',
        'core-system-problems-shell.what_happened': 'ما حدث',
        'core-system-problems-shell.reference': 'المرجع',
        'core-system-problems-shell.none': 'لم يحدث أي خطأ. جيد.',
        'core-system-problems-shell.path_entry_one': 'إدخال واحد في {{path}}',
        'core-system-problems-shell.path_entry_many':
          '{{count, number}} إدخالات في {{path}}',
      }),
      zh: Object.freeze({
        'core-system-problems-shell.title': '最近的问题',
        'core-system-problems-shell.clear_log': '清除日志',
        'core-system-problems-shell.help':
          '此仪表板自身的日志。请求失败时会显示一个引用编号——在这里查找相同编号即可了解实际发生了什么。相同故障会合并计数而非每次都列出：仪表板轮询，每次轮询都会调用 API，一个损坏的函数一分钟内就会变成四十行记录。',
        'core-system-problems-shell.last_seen': '最后出现',
        'core-system-problems-shell.times': '次数',
        'core-system-problems-shell.level': '级别',
        'core-system-problems-shell.where': '位置',
        'core-system-problems-shell.what_happened': '发生了什么',
        'core-system-problems-shell.reference': '参考',
        'core-system-problems-shell.none': '没有出现任何问题。很好。',
        'core-system-problems-shell.path_entry_one':
          '{{path}} 中有 {{count, number}} 条记录',
        'core-system-problems-shell.path_entry_many':
          '{{path}} 中有 {{count, number}} 条记录',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-system-problems-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-system-problems-shell.${key}`],
    );
  },
  render() {},
};
