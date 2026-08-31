/* Core card package: localized navigation-badge status and descriptions. */
window.ZogographNavigationBadgeCard = {
  manifest: Object.freeze({
    id: 'core-navigation-badge',
    locales: ['core-navigation-badge.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-navigation-badge.alerts':
          '{{unread, number}} unread alert(s), {{silenced, number}} of which {{verb}} held back by {{assistant}}.',
        'core-navigation-badge.was': 'was',
        'core-navigation-badge.were': 'were',
        'core-navigation-badge.problem_title':
          '{{count, number}} kind(s) of problem in the log{{worst}}{{details}}',
        'core-navigation-badge.problem_worst': ', the worst being {{meaning}}',
        'core-navigation-badge.problem_details': '.\n{{details}}',
        'core-navigation-badge.problem_end': '.',
        'core-navigation-badge.critical': 'something failed outright',
        'core-navigation-badge.error': 'an error',
        'core-navigation-badge.warning': 'a warning',
        'core-navigation-badge.notice': 'a notice',
        'core-navigation-badge.info': 'a note',
      }),
      es: Object.freeze({
        'core-navigation-badge.alerts':
          '{{unread, number}} alertas sin leer; {{silenced, number}} {{verb}} retenidas por {{assistant}}.',
        'core-navigation-badge.was': 'fue',
        'core-navigation-badge.were': 'fueron',
        'core-navigation-badge.problem_title':
          '{{count, number}} tipo(s) de problema en el registro{{worst}}{{details}}',
        'core-navigation-badge.problem_worst': ', siendo el peor {{meaning}}',
        'core-navigation-badge.problem_details': '.\n{{details}}',
        'core-navigation-badge.problem_end': '.',
        'core-navigation-badge.critical': 'un fallo completo',
        'core-navigation-badge.error': 'un error',
        'core-navigation-badge.warning': 'una advertencia',
        'core-navigation-badge.notice': 'un aviso',
        'core-navigation-badge.info': 'una nota',
      }),
      hi: Object.freeze({
        'core-navigation-badge.alerts':
          '{{unread, number}} अपठित चेतावनियाँ, जिनमें से {{silenced, number}} को {{assistant}} ने रोक रखा {{verb}}।',
        'core-navigation-badge.was': 'है',
        'core-navigation-badge.were': 'हैं',
        'core-navigation-badge.problem_title':
          'लॉग में {{count, number}} प्रकार की समस्या{{worst}}{{details}}',
        'core-navigation-badge.problem_worst':
          ', जिनमें सबसे गंभीर {{meaning}} है',
        'core-navigation-badge.problem_details': '.\n{{details}}',
        'core-navigation-badge.problem_end': '.',
        'core-navigation-badge.critical': 'पूर्ण विफलता',
        'core-navigation-badge.error': 'एक त्रुटि',
        'core-navigation-badge.warning': 'एक चेतावनी',
        'core-navigation-badge.notice': 'एक सूचना',
        'core-navigation-badge.info': 'एक टिप्पणी',
      }),
      ar: Object.freeze({
        'core-navigation-badge.alerts':
          '{{unread, number}} تنبيهًا غير مقروء؛ احتجز {{assistant}} {{silenced, number}} منها.',
        'core-navigation-badge.was': 'كان',
        'core-navigation-badge.were': 'كانت',
        'core-navigation-badge.problem_title':
          '{{count, number}} نوعًا من المشكلات في السجل{{worst}}{{details}}',
        'core-navigation-badge.problem_worst': '، وأسوأها {{meaning}}',
        'core-navigation-badge.problem_details': '.\n{{details}}',
        'core-navigation-badge.problem_end': '.',
        'core-navigation-badge.critical': 'فشلًا تامًا',
        'core-navigation-badge.error': 'خطأ',
        'core-navigation-badge.warning': 'تحذير',
        'core-navigation-badge.notice': 'إشعار',
        'core-navigation-badge.info': 'ملاحظة',
      }),
      zh: Object.freeze({
        'core-navigation-badge.alerts':
          '{{unread, number}} 条未读警报，其中 {{silenced, number}} 条被 {{assistant}} 暂缓。',
        'core-navigation-badge.was': '被',
        'core-navigation-badge.were': '被',
        'core-navigation-badge.problem_title':
          '日志中有 {{count, number}} 类问题{{worst}}{{details}}',
        'core-navigation-badge.problem_worst': '，其中最严重的是{{meaning}}',
        'core-navigation-badge.problem_details': '。\n{{details}}',
        'core-navigation-badge.problem_end': '。',
        'core-navigation-badge.critical': '完全失败',
        'core-navigation-badge.error': '错误',
        'core-navigation-badge.warning': '警告',
        'core-navigation-badge.notice': '通知',
        'core-navigation-badge.info': '提示',
      }),
    }),
  }),

  render(input, helpers) {
    const { i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (input.kind === 'alerts') {
      return t(
        'core-navigation-badge.alerts',
        {
          unread: input.unread,
          silenced: input.silenced,
          verb: t(
            input.silenced === 1
              ? 'core-navigation-badge.was'
              : 'core-navigation-badge.were',
            {},
            input.silenced === 1 ? 'was' : 'were',
          ),
          assistant: input.assistant,
        },
        '{{unread, number}} unread alert(s), {{silenced, number}} of which {{verb}} held back by {{assistant}}.',
      );
    }

    const marks = {
      critical: ['crit', '🔴'],
      error: ['crit', '🔴'],
      warning: ['warn', '🟡'],
      notice: ['warn', '🔵'],
      info: ['warn', '🔵'],
    };
    const [tone, icon] = marks[input.worst] ?? ['warn', ''];
    const meaning = input.worst
      ? t(
          `core-navigation-badge.${input.worst}`,
          {},
          {
            critical: 'something failed outright',
            error: 'an error',
            warning: 'a warning',
            notice: 'a notice',
            info: 'a note',
          }[input.worst] ?? '',
        )
      : '';
    const details = input.byLevel
      ? Object.entries(input.byLevel)
          .map(([name, count]) => `${count} ${name}`)
          .join(', ')
      : '';
    return {
      tone,
      icon,
      title: t(
        'core-navigation-badge.problem_title',
        {
          count: input.count,
          worst: meaning
            ? t(
                'core-navigation-badge.problem_worst',
                { meaning },
                ', the worst being {{meaning}}',
              )
            : '',
          details: details
            ? t(
                'core-navigation-badge.problem_details',
                { details },
                '.\n{{details}}',
              )
            : t('core-navigation-badge.problem_end', {}, '.'),
        },
        '{{count, number}} kind(s) of problem in the log{{worst}}{{details}}',
      ),
    };
  },
};
