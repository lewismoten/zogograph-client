/* Core card package: the shell supplies table and timestamp helpers. */
window.ZogographBootHistoryCard = {
  manifest: Object.freeze({
    id: 'core-boot-history',
    locales: ['core-boot-history.'],
    helpers: ['wrap', 'target', 'renderTable', 'whenSpan', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-boot-history.empty': 'No boot history.' }),
      es: Object.freeze({
        'core-boot-history.empty': 'No hay historial de arranque.',
      }),
      hi: Object.freeze({
        'core-boot-history.empty': 'कोई बूट इतिहास नहीं है।',
      }),
      ar: Object.freeze({ 'core-boot-history.empty': 'لا يوجد سجل للإقلاع.' }),
      zh: Object.freeze({ 'core-boot-history.empty': '没有启动历史。' }),
    }),
  }),

  render(history, helpers) {
    const { wrap, target, renderTable, whenSpan, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!wrap || !target) return;
    if (!history) {
      wrap.hidden = true;
      return;
    }
    wrap.hidden = false;
    if (!history.available) {
      target.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent:
            history.reason ||
            t('core-boot-history.empty', {}, 'No boot history.'),
        }),
      );
      return;
    }
    renderTable(target.id, history.boots, [
      {
        label: t('uptime.came_up', {}, 'Came up'),
        render: (row) => whenSpan(row.booted_at),
      },
      {
        label: t('uptime.kernel', {}, 'Kernel'),
        render: (row) => row.kernel || '—',
      },
      {
        label: t('uptime.previous_session_ended', {}, 'Previous session ended'),
        render: (row) => {
          const span = document.createElement('span');
          span.textContent =
            row.ended === 'shutdown'
              ? t('uptime.ended_cleanly', {}, 'cleanly')
              : row.ended === 'unclean'
                ? t('uptime.ended_unclean', {}, 'without a shutdown')
                : t('uptime.not_known', {}, 'not known');
          if (row.ended === 'unclean') span.className = 'grew';
          return span;
        },
      },
      {
        label: t('uptime.still_running', {}, 'Still running'),
        className: 'size',
        render: (row) =>
          Number(row.still_running) ? t('form.yes', {}, 'yes') : '',
      },
      {
        label: t('uptime.what_that_means', {}, 'What that means'),
        className: 'name',
        render: (row) => row.reason,
      },
    ]);
  },
};
