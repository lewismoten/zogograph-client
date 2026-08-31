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
        'core-log-stream-summary.sign_in': 'Sign in to read log files.',
        'core-log-stream-summary.stack_trace_folded':
          '{{count}} stack-trace lines folded in. Click to read.',
        'core-log-stream-summary.timestamp_missing_hint':
          'This line carries no timestamp this recognises. It is placed by when its file was last written.',
        'core-log-stream-summary.open_whole_file': 'Open the whole file',
        'core-log-stream-summary.newer_button': '‹ Newer',
        'core-log-stream-summary.older_button': 'Older ›',
        'core-log-stream-summary.detail_title': 'A log line',
        'core-log-stream-summary.detail_entry_of':
          '{{label}} · entry {{current, number}} of {{total, number}}',
        'core-log-stream-summary.detail_when': 'When',
        'core-log-stream-summary.detail_from': 'From',
        'core-log-stream-summary.detail_line': 'Line',
        'core-log-stream-summary.detail_level': 'Level',
        'core-log-stream-summary.detail_written_by': 'Written by',
        'core-log-stream-summary.detail_in': 'In',
        'core-log-stream-summary.detail_what_it_is': 'What it is',
        'core-log-stream-summary.detail_what_it_says': 'What it says',
        'core-log-stream-summary.detail_as_written': 'As it was written',
        'core-log-stream-summary.detail_file_line':
          '{{file}}, line {{line, number}}',
        'core-log-stream-summary.detail_stack_trace':
          'Stack trace ({{count, number}} lines)',
        'core-log-stream-summary.detail_trace_help':
          'Read from the top: the first frame is where it failed, and each one below is what called it. The bottom is usually where the request came in.',
        'core-log-stream-summary.detail_timestamp_missing':
          'not stated on the line — placed by when the file was last written',
        'core-log-stream-summary.stream_when': 'When',
        'core-log-stream-summary.stream_log': 'Log',
        'core-log-stream-summary.stream_wrote_it': 'Wrote it',
        'core-log-stream-summary.stream_message': 'Message',
        'core-log-stream-summary.stream_severity_hint':
          'How bad the line says it is',
        'core-log-stream-summary.stream_when_hint':
          'When the line says it happened',
        'core-log-stream-summary.stream_log_hint': 'Which file it came from',
        'core-log-stream-summary.skimming_note':
          'Click any line for the whole of it, taken apart. The columns here are cut short on purpose — this view is for skimming.',
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
        'core-log-stream-summary.sign_in':
          'Inicie sesión para leer archivos de registro.',
        'core-log-stream-summary.stack_trace_folded':
          '{{count}} líneas de seguimiento de pila ocultas. Haga clic para leer.',
        'core-log-stream-summary.timestamp_missing_hint':
          'Esta línea no contiene una marca de tiempo que se reconozca. Se coloca según cuándo se escribió por última vez su archivo.',
        'core-log-stream-summary.open_whole_file': 'Abrir el archivo completo',
        'core-log-stream-summary.newer_button': '‹ Más reciente',
        'core-log-stream-summary.older_button': 'Más antiguo ›',
        'core-log-stream-summary.detail_title': 'Una línea de registro',
        'core-log-stream-summary.detail_entry_of':
          '{{label}} · entrada {{current, number}} de {{total, number}}',
        'core-log-stream-summary.detail_when': 'Cuándo',
        'core-log-stream-summary.detail_from': 'De',
        'core-log-stream-summary.detail_line': 'Línea',
        'core-log-stream-summary.detail_level': 'Nivel',
        'core-log-stream-summary.detail_written_by': 'Escrito por',
        'core-log-stream-summary.detail_in': 'En',
        'core-log-stream-summary.detail_what_it_is': 'Qué es',
        'core-log-stream-summary.detail_what_it_says': 'Qué dice',
        'core-log-stream-summary.detail_as_written': 'Tal como se escribió',
        'core-log-stream-summary.detail_file_line':
          '{{file}}, línea {{line, number}}',
        'core-log-stream-summary.detail_stack_trace':
          'Traza de pila ({{count, number}} líneas)',
        'core-log-stream-summary.detail_trace_help':
          'Lee desde arriba: el primer marco es donde falló y cada uno debajo es quien lo llamó. El final suele ser donde entró la solicitud.',
        'core-log-stream-summary.detail_timestamp_missing':
          'no se indica en la línea; se coloca según la última vez que se escribió el archivo',
        'core-log-stream-summary.stream_when': 'Cuándo',
        'core-log-stream-summary.stream_log': 'Registro',
        'core-log-stream-summary.stream_wrote_it': 'Lo escribió',
        'core-log-stream-summary.stream_message': 'Mensaje',
        'core-log-stream-summary.stream_severity_hint':
          'Qué tan grave dice la línea que es',
        'core-log-stream-summary.stream_when_hint':
          'Cuándo dice la línea que ocurrió',
        'core-log-stream-summary.stream_log_hint': 'De qué archivo procede',
        'core-log-stream-summary.skimming_note':
          'Haga clic en cualquier línea para verla completa y desglosada. Las columnas se recortan a propósito: esta vista sirve para hojear.',
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
        'core-log-stream-summary.sign_in':
          'लॉग फ़ाइलें पढ़ने के लिए साइन इन करें।',
        'core-log-stream-summary.stack_trace_folded':
          '{{count}} स्टैक-ट्रेस पंक्तियाँ छिपी हैं। पढ़ने के लिए क्लिक करें।',
        'core-log-stream-summary.timestamp_missing_hint':
          'इस लाइन में कोई पहचाने जाने योग्य टाइमस्टैम्प नहीं है। इसे उसकी फ़ाइल के आखिरी बार लिखे जाने के समय के अनुसार रखा गया है।',
        'core-log-stream-summary.open_whole_file': 'पूरी फ़ाइल खोलें',
        'core-log-stream-summary.newer_button': '‹ नया',
        'core-log-stream-summary.older_button': 'पुराना ›',
        'core-log-stream-summary.detail_title': 'एक लॉग लाइन',
        'core-log-stream-summary.detail_entry_of':
          '{{label}} · प्रविष्टि {{current, number}} / {{total, number}}',
        'core-log-stream-summary.detail_when': 'कब',
        'core-log-stream-summary.detail_from': 'से',
        'core-log-stream-summary.detail_line': 'लाइन',
        'core-log-stream-summary.detail_level': 'स्तर',
        'core-log-stream-summary.detail_written_by': 'द्वारा लिखा गया',
        'core-log-stream-summary.detail_in': 'में',
        'core-log-stream-summary.detail_what_it_is': 'यह क्या है',
        'core-log-stream-summary.detail_what_it_says': 'यह क्या कहता है',
        'core-log-stream-summary.detail_as_written': 'जैसा लिखा गया था',
        'core-log-stream-summary.detail_file_line':
          '{{file}}, लाइन {{line, number}}',
        'core-log-stream-summary.detail_stack_trace':
          'स्टैक ट्रेस ({{count, number}} लाइनें)',
        'core-log-stream-summary.detail_trace_help':
          'ऊपर से पढ़ें: पहला फ्रेम वह जगह है जहाँ यह विफल हुआ, और उसके नीचे हर फ्रेम वह है जिसने इसे बुलाया। सबसे नीचे आम तौर पर अनुरोध आने की जगह होती है।',
        'core-log-stream-summary.detail_timestamp_missing':
          'लाइन में नहीं बताया गया — फ़ाइल आखिरी बार लिखे जाने के समय के अनुसार रखा गया',
        'core-log-stream-summary.stream_when': 'कब',
        'core-log-stream-summary.stream_log': 'लॉग',
        'core-log-stream-summary.stream_wrote_it': 'किसने लिखा',
        'core-log-stream-summary.stream_message': 'संदेश',
        'core-log-stream-summary.stream_severity_hint': 'लाइन कितनी गंभीर है',
        'core-log-stream-summary.stream_when_hint': 'लाइन के अनुसार यह कब हुआ',
        'core-log-stream-summary.stream_log_hint': 'यह किस फ़ाइल से आया',
        'core-log-stream-summary.skimming_note':
          'किसी भी पंक्ति को पूरी तरह और भागों में देखने के लिए उस पर क्लिक करें। यहाँ के कॉलम जानबूझकर छोटे रखे गए हैं — यह दृश्य जल्दी देखने के लिए है।',
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
        'core-log-stream-summary.sign_in': 'سجّل الدخول لقراءة ملفات السجل.',
        'core-log-stream-summary.stack_trace_folded':
          '{{count}} سطور تتبع مكدس مطوية. انقر للقراءة.',
        'core-log-stream-summary.timestamp_missing_hint':
          'لا يحمل هذا السطر طابعًا زمنيًا يمكن التعرف عليه. وُضع حسب وقت آخر كتابة لملفه.',
        'core-log-stream-summary.open_whole_file': 'افتح الملف كاملًا',
        'core-log-stream-summary.newer_button': '‹ الأحدث',
        'core-log-stream-summary.older_button': 'الأقدم ›',
        'core-log-stream-summary.detail_title': 'سطر سجل',
        'core-log-stream-summary.detail_entry_of':
          '{{label}} · الإدخال {{current, number}} من {{total, number}}',
        'core-log-stream-summary.detail_when': 'متى',
        'core-log-stream-summary.detail_from': 'من',
        'core-log-stream-summary.detail_line': 'السطر',
        'core-log-stream-summary.detail_level': 'المستوى',
        'core-log-stream-summary.detail_written_by': 'كتبه',
        'core-log-stream-summary.detail_in': 'في',
        'core-log-stream-summary.detail_what_it_is': 'ما هو',
        'core-log-stream-summary.detail_what_it_says': 'ما الذي يقوله',
        'core-log-stream-summary.detail_as_written': 'كما كُتب',
        'core-log-stream-summary.detail_file_line':
          '{{file}}، السطر {{line, number}}',
        'core-log-stream-summary.detail_stack_trace':
          'تتبع المكدس ({{count, number}} سطرًا)',
        'core-log-stream-summary.detail_trace_help':
          'اقرأ من الأعلى: الإطار الأول هو مكان الفشل، وكل إطار أدناه هو ما استدعاه. يكون الأسفل عادةً هو مكان ورود الطلب.',
        'core-log-stream-summary.detail_timestamp_missing':
          'غير مذكور في السطر — وُضع حسب وقت آخر كتابة للملف',
        'core-log-stream-summary.stream_when': 'متى',
        'core-log-stream-summary.stream_log': 'السجل',
        'core-log-stream-summary.stream_wrote_it': 'كتبه',
        'core-log-stream-summary.stream_message': 'الرسالة',
        'core-log-stream-summary.stream_severity_hint':
          'مدى خطورة السطر كما يصفها',
        'core-log-stream-summary.stream_when_hint': 'متى يقول السطر إن ذلك حدث',
        'core-log-stream-summary.stream_log_hint': 'الملف الذي جاء منه',
        'core-log-stream-summary.skimming_note':
          'انقر أي سطر لرؤيته كاملًا ومفككًا. تُقصَّر الأعمدة هنا عمدًا — هذا العرض للتصفح السريع.',
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
        'core-log-stream-summary.sign_in': '登录后可读取日志文件。',
        'core-log-stream-summary.stack_trace_folded':
          '已折叠 {{count}} 行堆栈跟踪。点击阅读。',
        'core-log-stream-summary.timestamp_missing_hint':
          '该行没有可识别的时间戳。它按其文件最后写入的时间放置。',
        'core-log-stream-summary.open_whole_file': '打开整个文件',
        'core-log-stream-summary.newer_button': '‹ 更新',
        'core-log-stream-summary.older_button': '更早 ›',
        'core-log-stream-summary.detail_title': '一条日志行',
        'core-log-stream-summary.detail_entry_of':
          '{{label}} · 第 {{current, number}} 条，共 {{total, number}} 条',
        'core-log-stream-summary.detail_when': '时间',
        'core-log-stream-summary.detail_from': '来源',
        'core-log-stream-summary.detail_line': '行',
        'core-log-stream-summary.detail_level': '级别',
        'core-log-stream-summary.detail_written_by': '写入者',
        'core-log-stream-summary.detail_in': '在',
        'core-log-stream-summary.detail_what_it_is': '它是什么',
        'core-log-stream-summary.detail_what_it_says': '它说了什么',
        'core-log-stream-summary.detail_as_written': '原样内容',
        'core-log-stream-summary.detail_file_line':
          '{{file}}，第 {{line, number}} 行',
        'core-log-stream-summary.detail_stack_trace':
          '堆栈跟踪（{{count, number}} 行）',
        'core-log-stream-summary.detail_trace_help':
          '从顶部开始阅读：第一帧是失败的位置，下面每一帧都是调用它的内容。底部通常是请求进入的位置。',
        'core-log-stream-summary.detail_timestamp_missing':
          '该行未说明时间——按文件最后写入的时间放置',
        'core-log-stream-summary.stream_when': '时间',
        'core-log-stream-summary.stream_log': '日志',
        'core-log-stream-summary.stream_wrote_it': '写入者',
        'core-log-stream-summary.stream_message': '消息',
        'core-log-stream-summary.stream_severity_hint': '该行表明的严重程度',
        'core-log-stream-summary.stream_when_hint': '该行所述发生的时间',
        'core-log-stream-summary.stream_log_hint': '它来自哪个文件',
        'core-log-stream-summary.skimming_note':
          '单击任意一行即可查看完整内容及其拆分。这里的列特意截短——此视图用于快速浏览。',
      }),
    }),
  }),
  text(key, helpers, values = {}) {
    return helpers.i18n.t(
      `core-log-stream-summary.${key}`,
      values,
      this.manifest.catalogs.en[`core-log-stream-summary.${key}`],
    );
  },
  render(data, helpers) {
    if (data?.key) return this.text(data.key, helpers, data.values);
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
