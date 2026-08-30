/* Core card package: concise outcome cards above the action log. */
window.ZogographActionSummaryCard = {
  manifest: Object.freeze({
    id: 'core-action-summary',
    locales: ['core-action-summary.'],
    helpers: ['target', 'humanBytes', 'humanTime', 'count', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-action-summary.things_done': 'Things done',
        'core-action-summary.since': 'since {{time}}',
        'core-action-summary.space_freed': 'Space freed',
        'core-action-summary.files_removed': '{{count}} removed',
        'core-action-summary.refused_failed': 'Refused or failed',
        'core-action-summary.changed_nothing': 'these changed nothing',
        'core-action-summary.everything_worked': 'everything worked',
      }),
      es: Object.freeze({
        'core-action-summary.things_done': 'Acciones realizadas',
        'core-action-summary.since': 'desde {{time}}',
        'core-action-summary.space_freed': 'Espacio liberado',
        'core-action-summary.files_removed': '{{count}} eliminados',
        'core-action-summary.refused_failed': 'Rechazadas o fallidas',
        'core-action-summary.changed_nothing': 'estas no cambiaron nada',
        'core-action-summary.everything_worked': 'todo funcionó',
      }),
      hi: Object.freeze({
        'core-action-summary.things_done': 'किए गए काम',
        'core-action-summary.since': '{{time}} से',
        'core-action-summary.space_freed': 'खाली की गई जगह',
        'core-action-summary.files_removed': '{{count}} हटाए गए',
        'core-action-summary.refused_failed': 'अस्वीकृत या विफल',
        'core-action-summary.changed_nothing': 'इनसे कुछ नहीं बदला',
        'core-action-summary.everything_worked': 'सब काम किया',
      }),
      ar: Object.freeze({
        'core-action-summary.things_done': 'الأعمال المنجزة',
        'core-action-summary.since': 'منذ {{time}}',
        'core-action-summary.space_freed': 'مساحة محررة',
        'core-action-summary.files_removed': 'أُزيل {{count}}',
        'core-action-summary.refused_failed': 'مرفوض أو فاشل',
        'core-action-summary.changed_nothing': 'لم تغيّر هذه شيئًا',
        'core-action-summary.everything_worked': 'كل شيء نجح',
      }),
      zh: Object.freeze({
        'core-action-summary.things_done': '已完成操作',
        'core-action-summary.since': '自 {{time}} 起',
        'core-action-summary.space_freed': '释放空间',
        'core-action-summary.files_removed': '已删除 {{count}} 个',
        'core-action-summary.refused_failed': '已拒绝或失败',
        'core-action-summary.changed_nothing': '这些未产生任何更改',
        'core-action-summary.everything_worked': '全部成功',
      }),
    }),
  }),
  render(rows, helpers) {
    const { target, humanBytes, humanTime, count, i18n } = helpers;
    if (!target) return;
    const freed = rows.reduce(
      (sum, row) => sum + Number(row.bytes_freed || 0),
      0,
    );
    const files = rows.reduce(
      (sum, row) => sum + Number(row.files_removed || 0),
      0,
    );
    const refused = rows.filter((row) => row.outcome !== 'ok').length;
    const cards = [
      [
        i18n.t('core-action-summary.things_done', {}, 'Things done'),
        String(rows.length),
        rows.length
          ? i18n.t(
              'core-action-summary.since',
              { time: humanTime(rows[rows.length - 1].at) },
              'since {{time}}',
            )
          : '',
      ],
      [
        i18n.t('core-action-summary.space_freed', {}, 'Space freed'),
        humanBytes(freed),
        i18n.t(
          'core-action-summary.files_removed',
          { count: count(files, 'file') },
          '{{count}} removed',
        ),
      ],
      [
        i18n.t('core-action-summary.refused_failed', {}, 'Refused or failed'),
        String(refused),
        refused
          ? i18n.t(
              'core-action-summary.changed_nothing',
              {},
              'these changed nothing',
            )
          : i18n.t(
              'core-action-summary.everything_worked',
              {},
              'everything worked',
            ),
      ],
    ];
    const fragment = document.createDocumentFragment();
    for (const [label, value, sub] of cards) {
      const card = document.createElement('div');
      card.className = 'card';
      for (const [className, text] of [
        ['label', label],
        ['value', value],
        ['sub muted', sub],
      ]) {
        const node = document.createElement('div');
        node.className = className;
        node.textContent = text;
        card.appendChild(node);
      }
      fragment.appendChild(card);
    }
    target.replaceChildren(fragment);
  },
};
