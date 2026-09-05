/* Shared navigation badge primitive. */
const NavigationBadgeCard = {
  manifest: Object.freeze({
    id: 'core-navigation-badge',
    locales: ['core-navigation-badge.'],
    owner: 'core-ui',
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
        'core-navigation-badge.pending_ready': '{{label}} is ready.',
        'core-navigation-badge.pending_new': 'new',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-ui/locales/es/navigation-badge.json',
      hi: 'packages/core-ui/locales/hi/navigation-badge.json',
      ar: 'packages/core-ui/locales/ar/navigation-badge.json',
      zh: 'packages/core-ui/locales/zh/navigation-badge.json',
    }),
  }),
  render(input, helpers) {
    const { i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (input.kind === 'pending_ready')
      return t(
        'core-navigation-badge.pending_ready',
        { label: input.label },
        '{{label}} is ready.',
      );
    if (input.kind === 'pending_new')
      return t('core-navigation-badge.pending_new', {}, 'new');
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
Zogograph.cards.register(NavigationBadgeCard);
