window.ZogographAiSessionsCard = {
  manifest: Object.freeze({
    id: 'core-ai-sessions',
    locales: ['core-ai-sessions.'],
    helpers: [
      'target',
      'note',
      'factGrid',
      'humanCount',
      'renderTable',
      'whenSpan',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-sessions.empty': 'No conversations yet.',
        'core-ai-sessions.conversations': 'Conversations',
        'core-ai-sessions.turns': 'Turns',
        'core-ai-sessions.tokens': 'Tokens',
        'core-ai-sessions.tokens_summary': '{{input}} in, {{output}} out',
        'core-ai-sessions.tokens_cached': ', {{tokens}} cached',
        'core-ai-sessions.spent': 'Spent',
        'core-ai-sessions.started': 'Started',
        'core-ai-sessions.ran_for': 'Ran for',
        'core-ai-sessions.ended': 'How it ended',
        'core-ai-sessions.model': 'Model',
        'core-ai-sessions.cost': 'Cost',
      }),
      es: Object.freeze({
        'core-ai-sessions.empty': 'Aún no hay conversaciones.',
        'core-ai-sessions.conversations': 'Conversaciones',
        'core-ai-sessions.turns': 'Turnos',
        'core-ai-sessions.tokens': 'Tokens',
        'core-ai-sessions.tokens_summary':
          '{{input}} de entrada, {{output}} de salida',
        'core-ai-sessions.tokens_cached': ', {{tokens}} en caché',
        'core-ai-sessions.spent': 'Gastado',
        'core-ai-sessions.started': 'Iniciada',
        'core-ai-sessions.ran_for': 'Duró',
        'core-ai-sessions.ended': 'Cómo terminó',
        'core-ai-sessions.model': 'Modelo',
        'core-ai-sessions.cost': 'Coste',
      }),
      hi: Object.freeze({
        'core-ai-sessions.empty': 'अभी कोई बातचीत नहीं है।',
        'core-ai-sessions.conversations': 'बातचीत',
        'core-ai-sessions.turns': 'चरण',
        'core-ai-sessions.tokens': 'टोकन',
        'core-ai-sessions.tokens_summary': '{{input}} इन, {{output}} आउट',
        'core-ai-sessions.tokens_cached': ', {{tokens}} कैश किए गए',
        'core-ai-sessions.spent': 'खर्च किया गया',
        'core-ai-sessions.started': 'शुरू हुआ',
        'core-ai-sessions.ran_for': 'चला',
        'core-ai-sessions.ended': 'कैसे समाप्त हुआ',
        'core-ai-sessions.model': 'मॉडल',
        'core-ai-sessions.cost': 'लागत',
      }),
      ar: Object.freeze({
        'core-ai-sessions.empty': 'لا توجد محادثات بعد.',
        'core-ai-sessions.conversations': 'المحادثات',
        'core-ai-sessions.turns': 'الجولات',
        'core-ai-sessions.tokens': 'الرموز',
        'core-ai-sessions.tokens_summary': '{{input}} وارد، {{output}} صادر',
        'core-ai-sessions.tokens_cached': '، {{tokens}} مخزنة مؤقتاً',
        'core-ai-sessions.spent': 'المنفَق',
        'core-ai-sessions.started': 'بدأت',
        'core-ai-sessions.ran_for': 'استمرت',
        'core-ai-sessions.ended': 'كيف انتهت',
        'core-ai-sessions.model': 'النموذج',
        'core-ai-sessions.cost': 'التكلفة',
      }),
      zh: Object.freeze({
        'core-ai-sessions.empty': '尚无对话。',
        'core-ai-sessions.conversations': '对话',
        'core-ai-sessions.turns': '轮次',
        'core-ai-sessions.tokens': '令牌',
        'core-ai-sessions.tokens_summary': '输入 {{input}}，输出 {{output}}',
        'core-ai-sessions.tokens_cached': '，已缓存 {{tokens}}',
        'core-ai-sessions.spent': '已花费',
        'core-ai-sessions.started': '开始时间',
        'core-ai-sessions.ran_for': '运行时长',
        'core-ai-sessions.ended': '结束方式',
        'core-ai-sessions.model': '模型',
        'core-ai-sessions.cost': '费用',
      }),
    }),
  }),
  render(data, helpers) {
    if (!data.sessions.length) {
      helpers.target.replaceChildren(
        helpers.note(
          helpers.i18n.t('core-ai-sessions.empty', {}, 'No conversations yet.'),
        ),
      );
      return;
    }
    const totals = data.totals;
    const text = (key, fallback, values = {}) =>
      helpers.i18n.t(`core-ai-sessions.${key}`, values, fallback);
    const parts = [
      helpers.factGrid([
        [
          text('conversations', 'Conversations'),
          helpers.humanCount(totals.sessions),
        ],
        [text('turns', 'Turns'), helpers.humanCount(totals.turns)],
        [
          text('tokens', 'Tokens'),
          text('tokens_summary', '{{input}} in, {{output}} out', {
            input: helpers.humanCount(totals.tokens_in),
            output: helpers.humanCount(totals.tokens_out),
          }) +
            (totals.tokens_cached
              ? text('tokens_cached', ', {{tokens}} cached', {
                  tokens: helpers.humanCount(totals.tokens_cached),
                })
              : ''),
        ],
        [text('spent', 'Spent'), totals.spent],
      ]),
    ];
    const holder = document.createElement('div');
    holder.id = 'aiSessionTable';
    parts.push(holder);
    helpers.target.replaceChildren(...parts);
    helpers.renderTable(
      'aiSessionTable',
      data.sessions,
      [
        {
          label: text('started', 'Started'),
          render: (row) => helpers.whenSpan(row.started_at),
        },
        { label: text('ran_for', 'Ran for'), render: (row) => row.ran_for },
        {
          label: text('ended', 'How it ended'),
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = row.state;
            if (row.why_stopped) span.title = row.why_stopped;
            if (row.state === 'failed') span.className = 'band-crit';
            return span;
          },
        },
        {
          label: text('model', 'Model'),
          className: 'cmd',
          render: (row) => row.model,
        },
        {
          label: text('turns', 'Turns'),
          className: 'size',
          render: (row) => String(row.turns),
        },
        {
          label: text('tokens', 'Tokens'),
          className: 'size',
          render: (row) => helpers.humanCount(row.tokens),
        },
        {
          label: text('cost', 'Cost'),
          className: 'size',
          render: (row) => row.spent,
        },
      ],
      null,
      null,
      (row) => String(row.id),
    );
  },
};
