/* Core card package: the shell supplies table and timestamp helpers. */
window.ZogographBootHistoryCard = {
  manifest: Object.freeze({
    id: 'core-boot-history',
    locales: ['core-boot-history.'],
    helpers: ['wrap', 'target', 'renderTable', 'whenSpan', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-boot-history.empty': 'No boot history.',
        'core-boot-history.came_up': 'Came up',
        'core-boot-history.kernel': 'Kernel',
        'core-boot-history.previous_session_ended': 'Previous session ended',
        'core-boot-history.ended_cleanly': 'cleanly',
        'core-boot-history.ended_unclean': 'without a shutdown',
        'core-boot-history.not_known': 'not known',
        'core-boot-history.still_running': 'Still running',
        'core-boot-history.what_that_means': 'What that means',
      }),
      es: Object.freeze({
        'core-boot-history.empty': 'No hay historial de arranque.',
        'core-boot-history.came_up': 'Arrancó',
        'core-boot-history.kernel': 'Kernel',
        'core-boot-history.previous_session_ended':
          'La sesión anterior terminó',
        'core-boot-history.ended_cleanly': 'correctamente',
        'core-boot-history.ended_unclean': 'sin apagado',
        'core-boot-history.not_known': 'no se sabe',
        'core-boot-history.still_running': 'Sigue en ejecución',
        'core-boot-history.what_that_means': 'Qué significa',
      }),
      hi: Object.freeze({
        'core-boot-history.empty': 'कोई बूट इतिहास नहीं है।',
        'core-boot-history.came_up': 'चालू हुआ',
        'core-boot-history.kernel': 'कर्नेल',
        'core-boot-history.previous_session_ended': 'पिछला सत्र समाप्त हुआ',
        'core-boot-history.ended_cleanly': 'सही तरीके से',
        'core-boot-history.ended_unclean': 'शटडाउन के बिना',
        'core-boot-history.not_known': 'ज्ञात नहीं',
        'core-boot-history.still_running': 'अभी भी चल रहा है',
        'core-boot-history.what_that_means': 'इसका अर्थ',
      }),
      ar: Object.freeze({
        'core-boot-history.empty': 'لا يوجد سجل للإقلاع.',
        'core-boot-history.came_up': 'بدأ التشغيل',
        'core-boot-history.kernel': 'النواة',
        'core-boot-history.previous_session_ended': 'انتهت الجلسة السابقة',
        'core-boot-history.ended_cleanly': 'بشكل سليم',
        'core-boot-history.ended_unclean': 'من دون إيقاف',
        'core-boot-history.not_known': 'غير معروف',
        'core-boot-history.still_running': 'ما زال يعمل',
        'core-boot-history.what_that_means': 'ما يعنيه ذلك',
      }),
      zh: Object.freeze({
        'core-boot-history.empty': '没有启动历史。',
        'core-boot-history.came_up': '已启动',
        'core-boot-history.kernel': '内核',
        'core-boot-history.previous_session_ended': '上一个会话结束',
        'core-boot-history.ended_cleanly': '正常关闭',
        'core-boot-history.ended_unclean': '未关闭',
        'core-boot-history.not_known': '未知',
        'core-boot-history.still_running': '仍在运行',
        'core-boot-history.what_that_means': '这意味着什么',
      }),
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
        label: t('core-boot-history.came_up', {}, 'Came up'),
        render: (row) => whenSpan(row.booted_at),
      },
      {
        label: t('core-boot-history.kernel', {}, 'Kernel'),
        render: (row) => row.kernel || '—',
      },
      {
        label: t(
          'core-boot-history.previous_session_ended',
          {},
          'Previous session ended',
        ),
        render: (row) => {
          const span = document.createElement('span');
          span.textContent =
            row.ended === 'shutdown'
              ? t('core-boot-history.ended_cleanly', {}, 'cleanly')
              : row.ended === 'unclean'
                ? t('core-boot-history.ended_unclean', {}, 'without a shutdown')
                : t('core-boot-history.not_known', {}, 'not known');
          if (row.ended === 'unclean') span.className = 'grew';
          return span;
        },
      },
      {
        label: t('core-boot-history.still_running', {}, 'Still running'),
        className: 'size',
        render: (row) =>
          Number(row.still_running) ? t('form.yes', {}, 'yes') : '',
      },
      {
        label: t('core-boot-history.what_that_means', {}, 'What that means'),
        className: 'name',
        render: (row) => row.reason,
      },
    ]);
  },
};
