/* Core card package: table of saved assistant conversations. */
window.ZogographAiPastConversationsCard = {
  manifest: Object.freeze({
    id: 'core-ai-past-conversations',
    locales: ['core-ai-past-conversations.'],
    helpers: [
      'target',
      'heading',
      'help',
      'retention',
      'note',
      'renderTable',
      'whenSpan',
      'open',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-past-conversations.title': 'Past conversations',
        'core-ai-past-conversations.help':
          'Every conversation, newest first. Open one to read it, or pick it up where it left off — that starts a fresh conversation carrying what was said, so the limits it ran into do not come with it.',
        'core-ai-past-conversations.retention_help':
          'Pinning one keeps it past the retention rule. That rule deletes by age, which is right for the ninety-nine nobody will read again and wrong for the one that explained an outage.',
        'core-ai-past-conversations.empty':
          'There have been no conversations yet.',
        'core-ai-past-conversations.topic': 'What it was about',
        'core-ai-past-conversations.named_by': '{{assistant}} named this one.',
        'core-ai-past-conversations.no_topic': 'no topic',
        'core-ai-past-conversations.started': 'Started',
        'core-ai-past-conversations.turns': 'Turns',
        'core-ai-past-conversations.cost': 'Cost',
        'core-ai-past-conversations.ended': 'How it ended',
        'core-ai-past-conversations.kept': 'Kept',
        'core-ai-past-conversations.pinned': 'pinned',
        'core-ai-past-conversations.pinned_tip':
          'Retention will not delete this one.',
      }),
      es: Object.freeze({
        'core-ai-past-conversations.title': 'Conversaciones anteriores',
        'core-ai-past-conversations.help':
          'Todas las conversaciones, primero las más recientes. Abra una para leerla o retómela donde quedó: así se inicia una conversación nueva que conserva lo dicho, pero no los límites que encontró.',
        'core-ai-past-conversations.retention_help':
          'Fijar una la conserva más allá de la regla de retención. Esa regla elimina por antigüedad, lo correcto para las noventa y nueve que nadie volverá a leer y no para la que explicó una interrupción.',
        'core-ai-past-conversations.empty': 'Aún no ha habido conversaciones.',
        'core-ai-past-conversations.topic': 'De qué trataba',
        'core-ai-past-conversations.named_by': '{{assistant}} nombró esta.',
        'core-ai-past-conversations.no_topic': 'sin tema',
        'core-ai-past-conversations.started': 'Iniciada',
        'core-ai-past-conversations.turns': 'Turnos',
        'core-ai-past-conversations.cost': 'Coste',
        'core-ai-past-conversations.ended': 'Cómo terminó',
        'core-ai-past-conversations.kept': 'Conservada',
        'core-ai-past-conversations.pinned': 'fijada',
        'core-ai-past-conversations.pinned_tip':
          'La retención no eliminará esta.',
      }),
      hi: Object.freeze({
        'core-ai-past-conversations.title': 'पिछली बातचीत',
        'core-ai-past-conversations.help':
          'हर बातचीत, सबसे नई पहले। एक को पढ़ने के लिए खोलें या जहाँ छूटी थी वहाँ से उठाएँ—इससे कही गई बातों को लेकर नई बातचीत शुरू होती है, इसलिए उससे मिली सीमाएँ साथ नहीं आतीं।',
        'core-ai-past-conversations.retention_help':
          'एक को पिन करने से वह रिटेंशन नियम के बाद भी रहती है। नियम उम्र के हिसाब से हटाता है, जो उन निन्यानवे के लिए सही है जिन्हें कोई फिर नहीं पढ़ेगा, पर उस एक के लिए नहीं जिसने आउटेज समझाया था।',
        'core-ai-past-conversations.empty': 'अभी तक कोई बातचीत नहीं हुई है।',
        'core-ai-past-conversations.topic': 'यह किस बारे में था',
        'core-ai-past-conversations.named_by':
          '{{assistant}} ने इसका नाम दिया।',
        'core-ai-past-conversations.no_topic': 'कोई विषय नहीं',
        'core-ai-past-conversations.started': 'शुरू किया',
        'core-ai-past-conversations.turns': 'टर्न',
        'core-ai-past-conversations.cost': 'लागत',
        'core-ai-past-conversations.ended': 'कैसे समाप्त हुआ',
        'core-ai-past-conversations.kept': 'रखा गया',
        'core-ai-past-conversations.pinned': 'पिन किया गया',
        'core-ai-past-conversations.pinned_tip': 'रिटेंशन इसे नहीं हटाएगा।',
      }),
      ar: Object.freeze({
        'core-ai-past-conversations.title': 'المحادثات السابقة',
        'core-ai-past-conversations.help':
          'كل محادثة، الأحدث أولًا. افتح واحدة لقراءتها أو تابعها من حيث توقفت — يبدأ ذلك محادثة جديدة تحمل ما قيل، فلا تحمل معها الحدود التي واجهتها.',
        'core-ai-past-conversations.retention_help':
          'تثبيت واحدة يبقيها بعد قاعدة الاحتفاظ. تحذف القاعدة حسب العمر، وهذا صحيح للتسع والتسعين التي لن يقرأها أحد ثانية وليس للواحدة التي شرحت انقطاعًا.',
        'core-ai-past-conversations.empty': 'لم تحدث أي محادثات بعد.',
        'core-ai-past-conversations.topic': 'ما كان موضوعها',
        'core-ai-past-conversations.named_by':
          'سمّى {{assistant}} هذه المحادثة.',
        'core-ai-past-conversations.no_topic': 'بلا موضوع',
        'core-ai-past-conversations.started': 'بدأت',
        'core-ai-past-conversations.turns': 'الجولات',
        'core-ai-past-conversations.cost': 'التكلفة',
        'core-ai-past-conversations.ended': 'كيف انتهت',
        'core-ai-past-conversations.kept': 'محفوظة',
        'core-ai-past-conversations.pinned': 'مثبّتة',
        'core-ai-past-conversations.pinned_tip': 'لن تحذفها سياسة الاحتفاظ.',
      }),
      zh: Object.freeze({
        'core-ai-past-conversations.title': '过往对话',
        'core-ai-past-conversations.help':
          '所有对话按最新优先排列。打开一项即可阅读，或从中断处继续——这会新建一个带有先前内容的对话，因此它遇到的限制不会一同带来。',
        'core-ai-past-conversations.retention_help':
          '固定一项可让它不受保留规则影响。该规则按时间删除，对九十九段再也不会读的对话是正确的，但不适用于解释过一次故障的那一段。',
        'core-ai-past-conversations.empty': '尚无对话记录。',
        'core-ai-past-conversations.topic': '对话主题',
        'core-ai-past-conversations.named_by': '{{assistant}} 为此对话命名。',
        'core-ai-past-conversations.no_topic': '无主题',
        'core-ai-past-conversations.started': '开始时间',
        'core-ai-past-conversations.turns': '轮次',
        'core-ai-past-conversations.cost': '费用',
        'core-ai-past-conversations.ended': '结束方式',
        'core-ai-past-conversations.kept': '保留',
        'core-ai-past-conversations.pinned': '已固定',
        'core-ai-past-conversations.pinned_tip': '保留策略不会删除此对话。',
      }),
    }),
  }),
  render(input, helpers) {
    const {
      target,
      heading,
      help,
      retention,
      note,
      renderTable,
      whenSpan,
      open,
      i18n,
    } = helpers;
    const t = (key, values, fallback) =>
      i18n.t(`core-ai-past-conversations.${key}`, values, fallback);
    heading.textContent = t('title', {}, 'Past conversations');
    help.textContent = t(
      'help',
      {},
      'Every conversation, newest first. Open one to read it, or pick it up where it left off — that starts a fresh conversation carrying what was said, so the limits it ran into do not come with it.',
    );
    retention.textContent = t(
      'retention_help',
      {},
      'Pinning one keeps it past the retention rule. That rule deletes by age, which is right for the ninety-nine nobody will read again and wrong for the one that explained an outage.',
    );
    if (!input.rows.length) {
      target.replaceChildren(
        note(t('empty', {}, 'There have been no conversations yet.')),
      );
      return;
    }
    const holder = document.createElement('div');
    holder.id = 'pastChatsTable';
    target.replaceChildren(holder);
    renderTable(
      'pastChatsTable',
      input.rows,
      [
        {
          label: t('topic', {}, 'What it was about'),
          className: 'name',
          render: (row) => {
            const said = document.createElement('span');
            if (row.title) {
              said.textContent = row.title;
              if (row.title_by === 'assistant')
                said.title = t(
                  'named_by',
                  { assistant: input.assistant },
                  '{{assistant}} named this one.',
                );
            } else {
              said.className = 'muted';
              said.textContent = t('no_topic', {}, 'no topic');
            }
            return said;
          },
        },
        {
          label: t('started', {}, 'Started'),
          render: (row) => whenSpan(row.started_at),
        },
        {
          label: t('turns', {}, 'Turns'),
          className: 'size',
          render: (row) => row.turns,
        },
        {
          label: t('cost', {}, 'Cost'),
          className: 'size',
          render: (row) => row.spent?.said ?? '',
        },
        {
          label: t('ended', {}, 'How it ended'),
          render: (row) =>
            row.state === 'ended' || row.state === 'stopped'
              ? row.why_stopped || row.state
              : row.state,
        },
        {
          label: t('kept', {}, 'Kept'),
          render: (row) => {
            if (!row.pinned) return '';
            const said = document.createElement('span');
            said.textContent = t('pinned', {}, 'pinned');
            said.className = 'own-label';
            said.title = t(
              'pinned_tip',
              {},
              'Retention will not delete this one.',
            );
            return said;
          },
        },
      ],
      (row) => open(row.id),
      null,
      (row) => String(row.id),
    );
  },
};
