window.ZogographLogStreamSummaryCard = {
  manifest: Object.freeze({
    id: 'core-log-stream-summary',
    locales: ['core-log-stream-summary.'],
    helpers: ['note', 'count', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-log-stream-summary.title': 'Every log at once',
        'core-log-stream-summary.help':
          'A machine misbehaving does not tell you which log to open. This is all of them merged, newest first, because the line that explains it is the most recent one somewhere.',
        'core-log-stream-summary.read':
          '{{logs}} read. Ordered by {{ordering}}.',
        'core-log-stream-summary.read_skipped':
          '{{logs}} read, {{skipped, number}} skipped (compressed, unreadable or not text). Ordered by {{ordering}}.',
        'core-log-stream-summary.folded':
          '{{lines}} folded into the {{errors}} they belong to. A fatal writes one line for the error and a dozen for the trace, so without this the twenty newest lines are one error and nineteen fragments of it.',
        'core-log-stream-summary.loading': 'Reading the end of every log…',
        'core-log-stream-summary.empty': 'Nothing in any of them.',
      }),
      es: Object.freeze({
        'core-log-stream-summary.title': 'Todos los registros a la vez',
        'core-log-stream-summary.help':
          'Una máquina que se comporta mal no dice qué registro abrir. Aquí se reúnen todos, primero los más recientes, porque la línea que lo explica es la más reciente en algún lugar.',
        'core-log-stream-summary.read':
          '{{logs}} leídos. Ordenados por {{ordering}}.',
        'core-log-stream-summary.read_skipped':
          '{{logs}} leídos y {{skipped, number}} omitidos (comprimidos, ilegibles o no textuales). Ordenados por {{ordering}}.',
        'core-log-stream-summary.folded':
          '{{lines}} se integraron en los {{errors}} a los que pertenecen. Un error fatal escribe una línea para el error y una docena para el seguimiento; sin esto, las veinte líneas más recientes serían un error y diecinueve fragmentos.',
        'core-log-stream-summary.loading': 'Leyendo el final de cada registro…',
        'core-log-stream-summary.empty': 'No hay nada en ninguno de ellos.',
      }),
      hi: Object.freeze({
        'core-log-stream-summary.title': 'हर लॉग एक साथ',
        'core-log-stream-summary.help':
          'गलत व्यवहार करती मशीन यह नहीं बताती कि कौन सा लॉग खोलें। यहाँ सभी लॉग सबसे नए पहले मिलाए गए हैं, क्योंकि उसे समझाने वाली पंक्ति कहीं न कहीं सबसे हाल की होती है।',
        'core-log-stream-summary.read':
          '{{logs}} पढ़े गए। {{ordering}} के अनुसार क्रमित।',
        'core-log-stream-summary.read_skipped':
          '{{logs}} पढ़े गए, {{skipped, number}} छोड़े गए (संपीड़ित, अपठनीय या टेक्स्ट नहीं)। {{ordering}} के अनुसार क्रमित।',
        'core-log-stream-summary.folded':
          '{{lines}} को उन {{errors}} में समेट दिया गया जिनसे वे संबंधित हैं। एक फेटल त्रुटि के लिए एक पंक्ति और ट्रेस के लिए दर्जन भर पंक्तियाँ लिखती है; इसके बिना नवीनतम बीस पंक्तियाँ एक त्रुटि और उसके उन्नीस अंश होंगी।',
        'core-log-stream-summary.loading': 'हर लॉग का अंत पढ़ रहे हैं…',
        'core-log-stream-summary.empty': 'इनमें से किसी में कुछ नहीं है।',
      }),
      ar: Object.freeze({
        'core-log-stream-summary.title': 'كل السجلات معًا',
        'core-log-stream-summary.help':
          'لا تخبرك آلة تتصرف بشكل خاطئ بأي سجل تفتحه. هذه كلها مدمجة، الأحدث أولًا، لأن السطر الذي يفسرها هو الأحدث في مكان ما.',
        'core-log-stream-summary.read':
          'قُرئ {{logs}}. مرتبة حسب {{ordering}}.',
        'core-log-stream-summary.read_skipped':
          'قُرئ {{logs}} وتُخطي {{skipped, number}} (مضغوط أو غير مقروء أو ليس نصًا). مرتبة حسب {{ordering}}.',
        'core-log-stream-summary.folded':
          'دُمج {{lines}} في {{errors}} التي تنتمي إليها. يكتب الخطأ الفادح سطرًا للخطأ وعشرات للتتبّع؛ وبدون ذلك تكون أحدث عشرين سطرًا خطأً واحدًا وتسعة عشر جزءًا منه.',
        'core-log-stream-summary.loading': 'يجري قراءة نهاية كل سجل…',
        'core-log-stream-summary.empty': 'لا شيء في أي منها.',
      }),
      zh: Object.freeze({
        'core-log-stream-summary.title': '所有日志同时查看',
        'core-log-stream-summary.help':
          '行为异常的机器不会告诉您该打开哪个日志。这里将所有日志合并并按最新优先排列，因为能解释问题的那一行，往往在某处是最新的一行。',
        'core-log-stream-summary.read':
          '已读取 {{logs}}。按 {{ordering}} 排序。',
        'core-log-stream-summary.read_skipped':
          '已读取 {{logs}}，跳过 {{skipped, number}} 个（压缩、不可读或非文本）。按 {{ordering}} 排序。',
        'core-log-stream-summary.folded':
          '{{lines}} 行已折叠到所属的 {{errors}} 中。一次致命错误会为错误写一行、为堆栈写十多行；没有此处理，最新二十行会是一条错误和它的十九个片段。',
        'core-log-stream-summary.loading': '正在读取每个日志的末尾…',
        'core-log-stream-summary.empty': '其中没有任何内容。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-log-stream-summary.${key}`,
      {},
      this.manifest.catalogs.en[`core-log-stream-summary.${key}`],
    );
  },
  render(data, helpers) {
    if (data?.key) return this.text(data.key, helpers);
    const wrap = document.createDocumentFragment();
    const readable = (data.files || []).filter((file) => file.ok);
    const skipped = (data.files || []).filter((file) => !file.ok);
    wrap.appendChild(
      helpers.note(
        helpers.i18n.t(
          skipped.length
            ? 'core-log-stream-summary.read_skipped'
            : 'core-log-stream-summary.read',
          {
            logs: helpers.count(readable.length, 'log'),
            skipped: skipped.length,
            ordering: data.ordering,
          },
          skipped.length
            ? '{{logs}} read, {{skipped, number}} skipped (compressed, unreadable or not text). Ordered by {{ordering}}.'
            : '{{logs}} read. Ordered by {{ordering}}.',
        ),
      ),
    );
    if (data.note) wrap.appendChild(helpers.note(data.note));
    const folded = data.rows.reduce(
      (sum, row) => sum + (row.trace?.length ?? 0),
      0,
    );
    if (folded) {
      wrap.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-log-stream-summary.folded',
            {
              lines: helpers.count(folded, 'stack-trace line'),
              errors: helpers.count(
                data.rows.filter((row) => row.trace?.length).length,
                'error',
              ),
            },
            '{{lines}} folded into the {{errors}} they belong to.',
          ),
        ),
      );
    }
    return wrap;
  },
};
