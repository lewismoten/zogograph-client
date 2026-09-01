/* Core card package: the shell supplies selected-report and open callbacks. */
window.ZogographNetworkCaptureHistoryCard = {
  manifest: Object.freeze({
    id: 'core-network-capture-history',
    locales: ['core-network-capture-history.'],
    helpers: [
      'target',
      'note',
      'renderTable',
      'whenSpan',
      'humanCount',
      'humanBytes',
      'open',
      'renderCapture',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-network-capture-history.title': 'What was on the wire',
        'core-network-capture-history.help':
          'Every capture that has been taken. One report answers ‘what is happening now’; two an hour apart answer ‘is this getting worse’, which is usually the real question. Rates are shown rather than totals, because two samples of different lengths cannot be compared any other way.',
        'core-network-capture-history.empty':
          'No captures have been taken yet. Ask for one on What is on the wire.',
        'core-network-capture-history.captures_empty':
          'No captures have been taken yet. Ask for one on What is on the wire.',
        'core-network-capture-history.taken': 'Taken',
        'core-network-capture-history.interface': 'Interface',
        'core-network-capture-history.filter': 'Filter',
        'core-network-capture-history.over': 'Over',
        'core-network-capture-history.packets_per_second': 'Packets/s',
        'core-network-capture-history.bytes_per_second': 'Bytes/s',
        'core-network-capture-history.arriving': 'Arriving',
        'core-network-capture-history.leaving': 'Leaving',
        'core-network-capture-history.peers': 'Peers',
        'core-network-capture-history.shown_below': 'shown below',
        'core-network-capture-history.open': 'Open',
      }),
      es: Object.freeze({
        'core-network-capture-history.title': 'Qué había en la red',
        'core-network-capture-history.help':
          'Todas las capturas realizadas. Un informe responde «qué ocurre ahora»; dos separados por una hora responden «¿está empeorando?», que suele ser la verdadera pregunta. Se muestran tasas en vez de totales porque dos muestras de distinta duración no se pueden comparar de otro modo.',
        'core-network-capture-history.empty':
          'Aún no se han tomado capturas. Solicite una en Qué hay en el cable.',
        'core-network-capture-history.captures_empty':
          'Aún no se han tomado capturas. Solicite una en Qué hay en el cable.',
        'core-network-capture-history.taken': 'Tomada',
        'core-network-capture-history.interface': 'Interfaz',
        'core-network-capture-history.filter': 'Filtro',
        'core-network-capture-history.over': 'Durante',
        'core-network-capture-history.packets_per_second': 'Paquetes/s',
        'core-network-capture-history.bytes_per_second': 'Bytes/s',
        'core-network-capture-history.arriving': 'Entrantes',
        'core-network-capture-history.leaving': 'Salientes',
        'core-network-capture-history.peers': 'Pares',
        'core-network-capture-history.shown_below': 'mostrada abajo',
        'core-network-capture-history.open': 'Abrir',
      }),
      hi: Object.freeze({
        'core-network-capture-history.title': 'नेटवर्क पर क्या था',
        'core-network-capture-history.help':
          'ली गई हर कैप्चर। एक रिपोर्ट ‘अभी क्या हो रहा है’ का उत्तर देती है; घंटे भर के अंतर वाली दो रिपोर्टें ‘क्या यह बदतर हो रहा है’ का उत्तर देती हैं, जो आमतौर पर असली सवाल है। कुल के बजाय दरें दिखाई जाती हैं क्योंकि अलग अवधि के दो नमूनों की तुलना और किसी तरह नहीं की जा सकती।',
        'core-network-capture-history.empty':
          'अभी तक कोई कैप्चर नहीं लिया गया है। वायर पर क्या है में एक के लिए कहें।',
        'core-network-capture-history.captures_empty':
          'अभी तक कोई कैप्चर नहीं लिया गया है। वायर पर क्या है में एक के लिए कहें।',
        'core-network-capture-history.taken': 'लिया गया',
        'core-network-capture-history.interface': 'इंटरफ़ेस',
        'core-network-capture-history.filter': 'फ़िल्टर',
        'core-network-capture-history.over': 'अवधि',
        'core-network-capture-history.packets_per_second': 'पैकेट/से.',
        'core-network-capture-history.bytes_per_second': 'बाइट/से.',
        'core-network-capture-history.arriving': 'आने वाले',
        'core-network-capture-history.leaving': 'जाने वाले',
        'core-network-capture-history.peers': 'पीयर',
        'core-network-capture-history.shown_below': 'नीचे दिखाया गया',
        'core-network-capture-history.open': 'खोलें',
      }),
      ar: Object.freeze({
        'core-network-capture-history.title': 'ما الذي كان على الشبكة',
        'core-network-capture-history.help':
          'كل عملية التقاط أُجريت. يجيب تقرير واحد عن «ما الذي يحدث الآن»؛ ويجيب تقريران يفصل بينهما ساعة عن «هل يزداد الأمر سوءًا؟»، وهذا هو السؤال الحقيقي غالبًا. تُعرض المعدلات بدل الإجماليات لأن عينتين بمدتين مختلفتين لا يمكن مقارنتهما بأي طريقة أخرى.',
        'core-network-capture-history.empty':
          'لم تؤخذ لقطات بعد. اطلب واحدة من قسم ما الموجود على الشبكة.',
        'core-network-capture-history.captures_empty':
          'لم تؤخذ لقطات بعد. اطلب واحدة من قسم ما الموجود على الشبكة.',
        'core-network-capture-history.taken': 'أُخذت',
        'core-network-capture-history.interface': 'الواجهة',
        'core-network-capture-history.filter': 'المرشح',
        'core-network-capture-history.over': 'خلال',
        'core-network-capture-history.packets_per_second': 'حزم/ث',
        'core-network-capture-history.bytes_per_second': 'بايت/ث',
        'core-network-capture-history.arriving': 'الوارد',
        'core-network-capture-history.leaving': 'الصادر',
        'core-network-capture-history.peers': 'الأقران',
        'core-network-capture-history.shown_below': 'معروضة أدناه',
        'core-network-capture-history.open': 'فتح',
      }),
      zh: Object.freeze({
        'core-network-capture-history.title': '线路上曾有什么',
        'core-network-capture-history.help':
          '已进行的每一次捕获。一份报告回答“现在发生了什么”；相隔一小时的两份报告回答“情况是否在恶化”，这通常才是真正的问题。显示的是速率而非总量，因为不同持续时间的两个样本无法以其他方式比较。',
        'core-network-capture-history.empty':
          '尚未进行抓取。请在“线路上有什么”中请求一次抓取。',
        'core-network-capture-history.captures_empty':
          '尚未进行抓取。请在“线路上有什么”中请求一次抓取。',
        'core-network-capture-history.taken': '采集时间',
        'core-network-capture-history.interface': '接口',
        'core-network-capture-history.filter': '筛选器',
        'core-network-capture-history.over': '时长',
        'core-network-capture-history.packets_per_second': '数据包/秒',
        'core-network-capture-history.bytes_per_second': '字节/秒',
        'core-network-capture-history.arriving': '入站',
        'core-network-capture-history.leaving': '出站',
        'core-network-capture-history.peers': '对端',
        'core-network-capture-history.shown_below': '显示在下方',
        'core-network-capture-history.open': '打开',
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-network-capture-history.${key}`,
      {},
      this.manifest.catalogs.en[`core-network-capture-history.${key}`],
    );
  },

  render(data, helpers) {
    const {
      target,
      note,
      renderTable,
      whenSpan,
      humanCount,
      humanBytes,
      open,
      renderCapture,
      i18n,
    } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!data.rows?.length) {
      target.replaceChildren(
        note(
          t(
            'core-network-capture-history.empty',
            {},
            'No captures have been taken yet. Ask for one on What is on the wire.',
          ),
        ),
      );
      return;
    }

    const holder = document.createElement('div');
    holder.id = 'captureList';
    target.replaceChildren(holder);
    renderTable(
      holder.id,
      data.rows,
      [
        {
          label: t('core-network-capture-history.taken', {}, 'Taken'),
          render: (row) => whenSpan(row.taken_at),
        },
        {
          label: t('core-network-capture-history.interface', {}, 'Interface'),
          render: (row) => row.interface,
        },
        {
          label: t('core-network-capture-history.filter', {}, 'Filter'),
          render: (row) => row.filter_label,
        },
        {
          label: t('core-network-capture-history.over', {}, 'Over'),
          className: 'size',
          render: (row) => `${row.seconds}s`,
        },
        {
          label: t(
            'core-network-capture-history.packets_per_second',
            {},
            'Packets/s',
          ),
          className: 'size',
          render: (row) =>
            row.packets_per_second == null
              ? '—'
              : humanCount(row.packets_per_second),
        },
        {
          label: t(
            'core-network-capture-history.bytes_per_second',
            {},
            'Bytes/s',
          ),
          className: 'size',
          render: (row) =>
            row.bytes_per_second == null
              ? '—'
              : `${humanBytes(row.bytes_per_second)}/s`,
        },
        {
          label: t('core-network-capture-history.arriving', {}, 'Arriving'),
          className: 'size',
          render: (row) => humanBytes(row.in_bytes),
        },
        {
          label: t('core-network-capture-history.leaving', {}, 'Leaving'),
          className: 'size',
          render: (row) => humanBytes(row.out_bytes),
        },
        {
          label: t('core-network-capture-history.peers', {}, 'Peers'),
          className: 'size',
          render: (row) => humanCount(row.distinct_peers),
        },
        {
          label: '',
          render: (row) => {
            const button = document.createElement('button');
            button.className = 'link';
            const showing = Number(data.capture?.id) === Number(row.id);
            button.textContent = showing
              ? t('core-network-capture-history.shown_below', {}, 'shown below')
              : t('core-network-capture-history.open', {}, 'Open');
            button.disabled = showing;
            button.addEventListener('click', () => open(Number(row.id)));
            return button;
          },
        },
      ],
      null,
      null,
      (row) => String(row.id),
    );

    const detail = document.createElement('div');
    detail.className = 'capture-detail';
    target.appendChild(detail);
    renderCapture(data.capture, detail);
  },
};
