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
        'core-process-trend.summary':
          'Top {{count}} recorded process groups for the selected range. Recent reporters lead; peak {{metric}} users fill the remaining slots, so a resolved problem remains visible. Names join replacement PIDs into one line.',
        'core-process-trend.empty_help':
          'No busy process snapshots fall inside this range. Process history is recorded only when the configured CPU or memory threshold is crossed.',
        'core-process-trend.empty': 'No recorded process load in this range',
        'core-process-trend.memory': 'memory',
        'core-process-trend.cpu': 'CPU',
      }),
      es: Object.freeze({
        'core-process-trend.summary':
          'Los {{count}} principales grupos de procesos registrados para el rango seleccionado. Los informantes recientes van primero; los usuarios máximos de {{metric}} ocupan los espacios restantes, de modo que un problema resuelto sigue visible. Los nombres unen los PID de reemplazo en una línea.',
        'core-process-trend.empty_help':
          'No hay instantáneas de procesos ocupados dentro de este rango. El historial de procesos se registra solo cuando se supera el umbral configurado de CPU o memoria.',
        'core-process-trend.empty':
          'No hay carga de proceso registrada en este rango',
        'core-process-trend.memory': 'memoria',
        'core-process-trend.cpu': 'CPU',
      }),
      hi: Object.freeze({
        'core-process-trend.summary':
          'चुनी गई सीमा के लिए शीर्ष {{count}} रिकॉर्ड किए गए प्रक्रिया समूह। हाल के रिपोर्टर पहले हैं; अधिकतम {{metric}} उपयोगकर्ता शेष स्थान भरते हैं, इसलिए हल की गई समस्या दिखाई देती रहती है। नाम प्रतिस्थापन PID को एक पंक्ति में जोड़ते हैं।',
        'core-process-trend.empty_help':
          'इस सीमा में कोई व्यस्त प्रक्रिया स्नैपशॉट नहीं है। प्रक्रिया इतिहास केवल तब दर्ज होता है जब कॉन्फ़िगर किया गया CPU या मेमोरी थ्रेशोल्ड पार होता है।',
        'core-process-trend.empty':
          'इस सीमा में कोई रिकॉर्ड किया गया प्रक्रिया लोड नहीं है',
        'core-process-trend.memory': 'मेमोरी',
        'core-process-trend.cpu': 'CPU',
      }),
      ar: Object.freeze({
        'core-process-trend.summary':
          'أهم {{count}} مجموعات عمليات مسجلة للنطاق المحدد. تتقدم التقارير الأحدث؛ ويملأ مستخدمو {{metric}} الأعلى الخانات المتبقية، لذلك تبقى المشكلة المحلولة ظاهرة. تجمع الأسماء معرّفات PID البديلة في سطر واحد.',
        'core-process-trend.empty_help':
          'لا توجد لقطات لعمليات مشغولة ضمن هذا النطاق. لا يُسجل سجل العمليات إلا عند تجاوز عتبة CPU أو الذاكرة المضبوطة.',
        'core-process-trend.empty': 'لا يوجد حمل عمليات مسجل في هذا النطاق',
        'core-process-trend.memory': 'الذاكرة',
        'core-process-trend.cpu': 'CPU',
      }),
      zh: Object.freeze({
        'core-process-trend.summary':
          '所选范围内排名前 {{count}} 的已记录进程组。最近报告的组优先；{{metric}} 使用峰值的组填补其余位置，因此已解决的问题仍会显示。名称会将替换 PID 合并为一行。',
        'core-process-trend.empty_help':
          '此范围内没有繁忙进程快照。仅当超过配置的 CPU 或内存阈值时才记录进程历史。',
        'core-process-trend.empty': '此范围内没有已记录的进程负载',
        'core-process-trend.memory': '内存',
        'core-process-trend.cpu': 'CPU',
      }),
    }),
  }),

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
