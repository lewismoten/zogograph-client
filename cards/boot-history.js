/* Core card package: the shell supplies table and timestamp helpers. */
window.ZogographBootHistoryCard = {
  manifest: Object.freeze({
    id: 'core-boot-history',
    locales: ['core-boot-history.'],
    helpers: ['wrap', 'target', 'renderTable', 'whenSpan', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-boot-history.title': 'Why it restarted',
        'core-boot-history.help':
          'From {{last}} on the host, which only root can read. A boot with a shutdown recorded before it was asked to stop; a boot with none means the previous session ended without recording one.',
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
        'core-boot-history.title': 'Por qué se reinició',
        'core-boot-history.help':
          'Desde {{last}} en el host, que solo root puede leer. Un arranque con apagado registrado ocurrió después de solicitarle que se detuviera; uno sin ello significa que la sesión anterior terminó sin registrar uno.',
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
        'core-boot-history.title': 'यह क्यों रीस्टार्ट हुआ',
        'core-boot-history.help':
          'होस्ट पर {{last}} से, जिसे केवल root पढ़ सकता है। जिस बूट से पहले बंद होना रिकॉर्ड है, उसे रुकने को कहा गया था; जिसके पास नहीं है उसमें पिछला सत्र बिना रिकॉर्ड किए समाप्त हुआ।',
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
        'core-boot-history.title': 'لماذا أعاد التشغيل',
        'core-boot-history.help':
          'من {{last}} على المضيف، الذي لا يستطيع قراءته إلا root. إقلاع سُجل قبله إيقاف يعني أنه طُلب منه التوقف؛ وإقلاع بلا ذلك يعني أن الجلسة السابقة انتهت من دون تسجيل واحد.',
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
        'core-boot-history.title': '为何重启',
        'core-boot-history.help':
          '来自主机上的 {{last}}，只有 root 能读取。一个启动若在被要求停止前记录了关机；没有记录的启动表示上一个会话结束时未记录关机。',
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

  text(key, helpers) {
    return helpers.i18n.t(
      `core-boot-history.${key}`,
      {},
      this.manifest.catalogs.en[`core-boot-history.${key}`],
    );
  },

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
