/* Core card package: table of saved assistant conversations. */
window.ZogographAiPastConversationsCard = {
  manifest: Object.freeze({
    id: 'core-ai-past-conversations',
    locales: ['core-ai-past-conversations.'],
    helpers: ['target', 'note', 'renderTable', 'whenSpan', 'open', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
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
    const { target, note, renderTable, whenSpan, open, i18n } = helpers;
    const t = (key, values, fallback) =>
      i18n.t(`core-ai-past-conversations.${key}`, values, fallback);
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
