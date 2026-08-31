/* Core card package: the shell supplies chart and axis helpers. */
window.ZogographProcessTrendCard = {
  manifest: Object.freeze({
    id: 'core-process-trend',
    locales: ['core-process-trend.'],
    helpers: [
      'chart',
      'note',
      'drawLineChart',
      'humanCount',
      'bytesAxis',
      'percentAxis',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-process-trend.title': 'Reported process load over this range',
        'core-process-trend.initial_help':
          'The newest reported processes lead this list, with high peaks retained so a process that has settled down is still visible. Names group replacement workers together; PIDs are intentionally not a chart identity.',
        'core-process-trend.summary':
          'Top {{count}} recorded process groups for the selected range. Recent reporters lead; peak {{metric}} users fill the remaining slots, so a resolved problem remains visible. Names join replacement PIDs into one line.',
        'core-process-trend.empty_help':
          'No busy process snapshots fall inside this range. Process history is recorded only when the configured CPU or memory threshold is crossed.',
        'core-process-trend.empty': 'No recorded process load in this range',
        'core-process-trend.memory': 'memory',
        'core-process-trend.cpu': 'CPU',
        'core-process-trend.age_with_newest': '{{age}} · newest {{newest}}',
        'core-process-trend.age_started_now': 'Started just now.',
        'core-process-trend.age_started_hour': 'Started within the hour.',
        'core-process-trend.age_started_today': 'Started today.',
        'core-process-trend.age_long_running':
          'Running for more than a day - a daemon, or something that never finished.',
      }),
      es: Object.freeze({
        'core-process-trend.title':
          'Carga de procesos notificada en este periodo',
        'core-process-trend.initial_help':
          'Los procesos informados más recientes encabezan esta lista, y se conservan los picos altos para que un proceso que ya se calmó siga visible. Los nombres agrupan trabajadores de reemplazo; los PID no son deliberadamente una identidad del gráfico.',
        'core-process-trend.summary':
          'Los {{count}} principales grupos de procesos registrados para el rango seleccionado. Los informantes recientes van primero; los usuarios máximos de {{metric}} ocupan los espacios restantes, de modo que un problema resuelto sigue visible. Los nombres unen los PID de reemplazo en una línea.',
        'core-process-trend.empty_help':
          'No hay instantáneas de procesos ocupados dentro de este rango. El historial de procesos se registra solo cuando se supera el umbral configurado de CPU o memoria.',
        'core-process-trend.empty':
          'No hay carga de proceso registrada en este rango',
        'core-process-trend.memory': 'memoria',
        'core-process-trend.cpu': 'CPU',
        'core-process-trend.age_with_newest':
          '{{age}} · más reciente {{newest}}',
        'core-process-trend.age_started_now': 'Iniciado ahora mismo.',
        'core-process-trend.age_started_hour':
          'Iniciado durante la última hora.',
        'core-process-trend.age_started_today': 'Iniciado hoy.',
        'core-process-trend.age_long_running':
          'Lleva más de un día en ejecución: un daemon o algo que nunca terminó.',
      }),
      hi: Object.freeze({
        'core-process-trend.title': 'इस सीमा में रिपोर्ट किया गया प्रोसेस लोड',
        'core-process-trend.initial_help':
          'सबसे हाल में रिपोर्ट की गई प्रक्रियाएं इस सूची में आगे हैं और ऊंचे शिखर रखे जाते हैं ताकि शांत हो चुकी प्रक्रिया भी दिखती रहे। नाम प्रतिस्थापन वर्करों को एक साथ समूहित करते हैं; PID जानबूझकर चार्ट की पहचान नहीं हैं।',
        'core-process-trend.summary':
          'चुनी गई सीमा के लिए शीर्ष {{count}} रिकॉर्ड किए गए प्रक्रिया समूह। हाल के रिपोर्टर पहले हैं; अधिकतम {{metric}} उपयोगकर्ता शेष स्थान भरते हैं, इसलिए हल की गई समस्या दिखाई देती रहती है। नाम प्रतिस्थापन PID को एक पंक्ति में जोड़ते हैं।',
        'core-process-trend.empty_help':
          'इस सीमा में कोई व्यस्त प्रक्रिया स्नैपशॉट नहीं है। प्रक्रिया इतिहास केवल तब दर्ज होता है जब कॉन्फ़िगर किया गया CPU या मेमोरी थ्रेशोल्ड पार होता है।',
        'core-process-trend.empty':
          'इस सीमा में कोई रिकॉर्ड किया गया प्रक्रिया लोड नहीं है',
        'core-process-trend.memory': 'मेमोरी',
        'core-process-trend.cpu': 'CPU',
        'core-process-trend.age_with_newest': '{{age}} · सबसे नया {{newest}}',
        'core-process-trend.age_started_now': 'अभी शुरू हुआ।',
        'core-process-trend.age_started_hour': 'एक घंटे के भीतर शुरू हुआ।',
        'core-process-trend.age_started_today': 'आज शुरू हुआ।',
        'core-process-trend.age_long_running':
          'एक दिन से अधिक समय से चल रहा है — डेमन, या कुछ जो कभी समाप्त नहीं हुआ।',
      }),
      ar: Object.freeze({
        'core-process-trend.title': 'حمل العملية المُبلّغ عنه خلال هذا النطاق',
        'core-process-trend.initial_help':
          'تتقدم العمليات التي أُبلغ عنها مؤخرًا هذه القائمة، وتُحتفظ بالذروات العالية حتى تبقى العملية التي هدأت ظاهرة. تجمع الأسماء العاملين البدلاء معًا؛ ولا تُعد معرّفات PID هوية للرسم عمدًا.',
        'core-process-trend.summary':
          'أهم {{count}} مجموعات عمليات مسجلة للنطاق المحدد. تتقدم التقارير الأحدث؛ ويملأ مستخدمو {{metric}} الأعلى الخانات المتبقية، لذلك تبقى المشكلة المحلولة ظاهرة. تجمع الأسماء معرّفات PID البديلة في سطر واحد.',
        'core-process-trend.empty_help':
          'لا توجد لقطات لعمليات مشغولة ضمن هذا النطاق. لا يُسجل سجل العمليات إلا عند تجاوز عتبة CPU أو الذاكرة المضبوطة.',
        'core-process-trend.empty': 'لا يوجد حمل عمليات مسجل في هذا النطاق',
        'core-process-trend.memory': 'الذاكرة',
        'core-process-trend.cpu': 'CPU',
        'core-process-trend.age_with_newest': '{{age}} · الأحدث {{newest}}',
        'core-process-trend.age_started_now': 'بدأ للتو.',
        'core-process-trend.age_started_hour': 'بدأ خلال الساعة.',
        'core-process-trend.age_started_today': 'بدأ اليوم.',
        'core-process-trend.age_long_running':
          'يعمل لأكثر من يوم — خدمة، أو شيء لم ينتهِ أبدًا.',
      }),
      zh: Object.freeze({
        'core-process-trend.title': '此范围内报告的进程负载',
        'core-process-trend.initial_help':
          '最新报告的进程排在列表前面，并保留高峰值，因此已经平静下来的进程仍然可见。名称会将替换工作进程归在一起；PID 有意不作为图表身份标识。',
        'core-process-trend.summary':
          '所选范围内排名前 {{count}} 的已记录进程组。最近报告的组优先；{{metric}} 使用峰值的组填补其余位置，因此已解决的问题仍会显示。名称会将替换 PID 合并为一行。',
        'core-process-trend.empty_help':
          '此范围内没有繁忙进程快照。仅当超过配置的 CPU 或内存阈值时才记录进程历史。',
        'core-process-trend.empty': '此范围内没有已记录的进程负载',
        'core-process-trend.memory': '内存',
        'core-process-trend.cpu': 'CPU',
        'core-process-trend.age_with_newest': '{{age}} · 最新 {{newest}}',
        'core-process-trend.age_started_now': '刚刚启动。',
        'core-process-trend.age_started_hour': '在一小时内启动。',
        'core-process-trend.age_started_today': '今天启动。',
        'core-process-trend.age_long_running':
          '运行已超过一天——可能是守护进程，也可能是从未结束的内容。',
      }),
    }),
  }),

  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-process-trend.${key}`,
      values,
      this.manifest.catalogs.en[`core-process-trend.${key}`],
    );
  },

  render(trend, helpers) {
    const {
      chart,
      note,
      drawLineChart,
      humanCount,
      bytesAxis,
      percentAxis,
      i18n,
    } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!chart || !note) return;
    const memory = trend.metric === 'memory';
    const names = Array.isArray(trend.names) ? trend.names.slice(0, 10) : [];
    const metric = memory
      ? t('core-process-trend.memory', {}, 'memory')
      : t('core-process-trend.cpu', {}, 'CPU');
    note.textContent = names.length
      ? t(
          'core-process-trend.summary',
          { count: humanCount(names.length), metric },
          'Top {{count}} recorded process groups for the selected range. Recent reporters lead; peak {{metric}} users fill the remaining slots, so a resolved problem remains visible. Names join replacement PIDs into one line.',
        )
      : t(
          'core-process-trend.empty_help',
          {},
          'No busy process snapshots fall inside this range. Process history is recorded only when the configured CPU or memory threshold is crossed.',
        );
    drawLineChart(
      chart,
      Array.isArray(trend.rows) ? trend.rows : [],
      names.map((name) => ({ key: name, label: name })),
      {
        axis: memory ? bytesAxis : percentAxis,
        zeroBased: true,
        empty: t(
          'core-process-trend.empty',
          {},
          'No recorded process load in this range',
        ),
      },
    );
  },
};
