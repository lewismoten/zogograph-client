/* Core card package: concise completion text for a terminal command. */
window.ZogographTerminalVerdictSummaryCard = {
  manifest: Object.freeze({
    id: 'core-terminal-verdict-summary',
    locales: ['core-terminal-verdict-summary.'],
    helpers: ['humanSpan', 'humanBytes', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-verdict-summary.timed_out': 'Stopped at the time limit',
        'core-terminal-verdict-summary.stopped_by': 'Stopped by {{actor}}',
        'core-terminal-verdict-summary.finished': 'Finished',
        'core-terminal-verdict-summary.summary':
          '{{state}} after {{duration}}, {{size}} of output.',
      }),
      es: Object.freeze({
        'core-terminal-verdict-summary.timed_out':
          'Se detuvo al alcanzar el límite de tiempo',
        'core-terminal-verdict-summary.stopped_by': 'Detenido por {{actor}}',
        'core-terminal-verdict-summary.finished': 'Finalizado',
        'core-terminal-verdict-summary.summary':
          '{{state}} después de {{duration}}, con {{size}} de salida.',
      }),
      hi: Object.freeze({
        'core-terminal-verdict-summary.timed_out': 'समय सीमा पर रोका गया',
        'core-terminal-verdict-summary.stopped_by': '{{actor}} ने रोका',
        'core-terminal-verdict-summary.finished': 'समाप्त',
        'core-terminal-verdict-summary.summary':
          '{{state}}: {{duration}} बाद, {{size}} आउटपुट।',
      }),
      ar: Object.freeze({
        'core-terminal-verdict-summary.timed_out': 'توقف عند حد الوقت',
        'core-terminal-verdict-summary.stopped_by': 'أوقفه {{actor}}',
        'core-terminal-verdict-summary.finished': 'انتهى',
        'core-terminal-verdict-summary.summary':
          '{{state}} بعد {{duration}}، ومخرجات بحجم {{size}}.',
      }),
      zh: Object.freeze({
        'core-terminal-verdict-summary.timed_out': '已在时间限制处停止',
        'core-terminal-verdict-summary.stopped_by': '已被 {{actor}} 停止',
        'core-terminal-verdict-summary.finished': '已完成',
        'core-terminal-verdict-summary.summary':
          '{{state}}，耗时 {{duration}}，输出 {{size}}。',
      }),
    }),
  }),

  render(data, helpers) {
    const { humanSpan, humanBytes, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const state = data.timed_out
      ? t(
          'core-terminal-verdict-summary.timed_out',
          {},
          'Stopped at the time limit',
        )
      : data.stopped_by
        ? t(
            'core-terminal-verdict-summary.stopped_by',
            { actor: data.stopped_by },
            'Stopped by {{actor}}',
          )
        : t('core-terminal-verdict-summary.finished', {}, 'Finished');
    return t(
      'core-terminal-verdict-summary.summary',
      {
        state,
        duration: humanSpan(data.ran_for, true),
        size: humanBytes(data.size),
      },
      '{{state}} after {{duration}}, {{size}} of output.',
    );
  },
};
