window.ZogographLogRepeatsCard = {
  manifest: Object.freeze({
    id: 'core-log-repeats',
    locales: ['core-log-repeats.'],
    helpers: [
      'target',
      'note',
      'humanCount',
      'count',
      'renderTable',
      'whenSpan',
      'logMarks',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-log-repeats.title': 'What keeps repeating',
        'core-log-repeats.help':
          'A log filling a disk is almost never full of interesting distinct events; it is one event ten thousand times. These are the same lines with their variable parts — numbers, addresses, paths, quoted strings — taken out and counted, which is the only presentation of that fact anybody can act on.',
        'core-log-repeats.summary':
          '{{lines}} recent lines across every log fall into {{shapes}}.',
        'core-log-repeats.per_minute': '{{rate}}/min',
        'core-log-repeats.grouped_as': 'Grouped as: {{shape}}',
        'core-log-repeats.empty': 'Nothing to group yet.',
        'core-log-repeats.times': 'Times',
        'core-log-repeats.rate': 'Rate',
        'core-log-repeats.in': 'In',
        'core-log-repeats.last_one': 'Last one',
        'core-log-repeats.what_it_says': 'What it says',
      }),
      es: Object.freeze({
        'core-log-repeats.title': 'Lo que se repite',
        'core-log-repeats.help':
          'Un registro que llena un disco casi nunca contiene eventos distintos e interesantes; es un evento diez mil veces. Aquí se agrupan y cuentan las mismas líneas quitando sus partes variables — números, direcciones, rutas y cadenas entre comillas —, la única presentación sobre la que se puede actuar.',
        'core-log-repeats.summary':
          '{{lines}} líneas recientes de todos los registros se agrupan en {{shapes}}.',
        'core-log-repeats.per_minute': '{{rate}}/min',
        'core-log-repeats.grouped_as': 'Agrupado como: {{shape}}',
        'core-log-repeats.empty': 'Aún no hay nada que agrupar.',
        'core-log-repeats.times': 'Veces',
        'core-log-repeats.rate': 'Tasa',
        'core-log-repeats.in': 'En',
        'core-log-repeats.last_one': 'Último',
        'core-log-repeats.what_it_says': 'Qué dice',
      }),
      hi: Object.freeze({
        'core-log-repeats.title': 'क्या बार-बार दोहराता है',
        'core-log-repeats.help':
          'डिस्क भरने वाला लॉग शायद ही अलग रोचक घटनाओं से भरा होता है; वह एक घटना दस हज़ार बार होती है। ये वही पंक्तियाँ हैं जिनके बदलने वाले भाग — संख्या, पते, पथ और उद्धृत स्ट्रिंग — हटाकर गिने गए हैं।',
        'core-log-repeats.summary':
          'सभी लॉग की {{lines}} हाल की पंक्तियाँ {{shapes}} में आती हैं।',
        'core-log-repeats.per_minute': '{{rate}}/मिनट',
        'core-log-repeats.grouped_as': 'इस रूप में समूहीकृत: {{shape}}',
        'core-log-repeats.empty': 'समूह बनाने के लिए अभी कुछ नहीं है।',
        'core-log-repeats.times': 'बार',
        'core-log-repeats.rate': 'दर',
        'core-log-repeats.in': 'में',
        'core-log-repeats.last_one': 'अंतिम',
        'core-log-repeats.what_it_says': 'यह क्या कहता है',
      }),
      ar: Object.freeze({
        'core-log-repeats.title': 'ما الذي يتكرر',
        'core-log-repeats.help':
          'يكاد السجل الذي يملأ قرصًا لا يكون مليئًا بأحداث مثيرة ومختلفة؛ بل حدث واحد عشرة آلاف مرة. هذه هي السطور نفسها بعد إزالة أجزائها المتغيرة — الأرقام والعناوين والمسارات والسلاسل المقتبسة — وعدّها، وهو العرض الوحيد الذي يمكن التصرف بناءً عليه.',
        'core-log-repeats.summary':
          'تقع {{lines}} أسطر حديثة من كل السجلات ضمن {{shapes}}.',
        'core-log-repeats.per_minute': '{{rate}}/دقيقة',
        'core-log-repeats.grouped_as': 'مجمّع بوصفه: {{shape}}',
        'core-log-repeats.empty': 'لا شيء لتجميعه بعد.',
        'core-log-repeats.times': 'المرات',
        'core-log-repeats.rate': 'المعدل',
        'core-log-repeats.in': 'في',
        'core-log-repeats.last_one': 'الأخير',
        'core-log-repeats.what_it_says': 'ما الذي يقوله',
      }),
      zh: Object.freeze({
        'core-log-repeats.title': '反复出现的内容',
        'core-log-repeats.help':
          '占满磁盘的日志几乎从来不是由许多有趣且不同的事件组成；而是同一事件发生一万次。这里将相同行中可变的部分——数字、地址、路径和引号内字符串——剔除后计数，这是唯一可据以采取行动的呈现方式。',
        'core-log-repeats.summary':
          '所有日志中最近的 {{lines}} 行归入 {{shapes}}。',
        'core-log-repeats.per_minute': '{{rate}}/分钟',
        'core-log-repeats.grouped_as': '分组为：{{shape}}',
        'core-log-repeats.empty': '还没有可分组的内容。',
        'core-log-repeats.times': '次数',
        'core-log-repeats.rate': '速率',
        'core-log-repeats.in': '在',
        'core-log-repeats.last_one': '最后一个',
        'core-log-repeats.what_it_says': '它说了什么',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-log-repeats.${key}`,
      {},
      this.manifest.catalogs.en[`core-log-repeats.${key}`],
    );
  },
  render(data, helpers) {
    const repeats = data.repeats;
    if (!repeats?.groups.length) {
      helpers.target.replaceChildren(
        helpers.note(
          helpers.i18n.t('core-log-repeats.empty', {}, 'Nothing to group yet.'),
        ),
      );
      return;
    }
    const wrap = document.createDocumentFragment();
    wrap.appendChild(
      helpers.note(
        helpers.i18n.t(
          'core-log-repeats.summary',
          {
            lines: helpers.humanCount(repeats.lines),
            shapes: helpers.count(repeats.distinct, 'distinct shape'),
          },
          '{{lines}} recent lines across every log fall into {{shapes}}.',
        ),
      ),
    );
    const holder = document.createElement('div');
    holder.id = 'repeatTable';
    wrap.appendChild(holder);
    helpers.target.replaceChildren(wrap);
    helpers.renderTable(
      'repeatTable',
      repeats.groups,
      [
        {
          label: helpers.i18n.t('core-log-repeats.times', {}, 'Times'),
          className: 'size',
          render: (row) => helpers.humanCount(row.count),
        },
        {
          label: helpers.i18n.t('core-log-repeats.rate', {}, 'Rate'),
          className: 'size',
          render: (row) => {
            if (row.per_minute == null) return '—';
            const span = document.createElement('span');
            span.textContent = helpers.i18n.t(
              'core-log-repeats.per_minute',
              { rate: row.per_minute },
              '{{rate}}/min',
            );
            if (row.per_minute >= 60) span.className = 'grew';
            return span;
          },
        },
        {
          label: helpers.i18n.t('core-log-repeats.in', {}, 'In'),
          render: (row) => (row.files || []).join(', '),
        },
        {
          label: helpers.i18n.t('core-log-repeats.last_one', {}, 'Last one'),
          render: (row) => (row.last_at ? helpers.whenSpan(row.last_at) : '—'),
        },
        {
          label: helpers.i18n.t(
            'core-log-repeats.what_it_says',
            {},
            'What it says',
          ),
          className: 'cmd',
          render: (row) => {
            const cell = document.createDocumentFragment();
            if (row.kind) {
              const [icon, meaning] = helpers.logMarks[row.kind] || ['▫️', ''];
              const mark = document.createElement('span');
              mark.textContent = `${icon} `;
              mark.title = meaning;
              cell.appendChild(mark);
            }
            if (row.where) {
              const who = document.createElement('span');
              who.className = 'muted';
              who.textContent = `${row.where}: `;
              cell.appendChild(who);
            }
            const message = document.createElement('span');
            message.className = 'repeat-line';
            message.textContent = row.message || row.example;
            message.title = `${row.example}\n\n${helpers.i18n.t(
              'core-log-repeats.grouped_as',
              { shape: row.shape },
              'Grouped as: {{shape}}',
            )}`;
            cell.appendChild(message);
            return cell;
          },
        },
      ],
      null,
      null,
      (row) => row.shape,
    );
  },
};
