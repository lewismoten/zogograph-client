/* Core card package: the compact unread-alert interruption on the overview. */
window.ZogographAlertBannerCard = {
  manifest: Object.freeze({
    id: 'core-alert-banner',
    locales: ['core-alert-banner.'],
    helpers: ['target', 'severityIcon', 'showView', 'dismiss', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-alert-banner.unread': '{{count, number}} unread alert(s)',
        'core-alert-banner.see_all': 'See all',
        'core-alert-banner.hide_until_new':
          'Hide this until something new fires',
        'core-alert-banner.dismiss': 'Dismiss',
      }),
      es: Object.freeze({
        'core-alert-banner.unread': '{{count, number}} alertas sin leer',
        'core-alert-banner.see_all': 'Ver todas',
        'core-alert-banner.hide_until_new':
          'Ocultar hasta que ocurra algo nuevo',
        'core-alert-banner.dismiss': 'Descartar',
      }),
      hi: Object.freeze({
        'core-alert-banner.unread': '{{count, number}} अपठित चेतावनियाँ',
        'core-alert-banner.see_all': 'सभी देखें',
        'core-alert-banner.hide_until_new': 'कुछ नया होने तक छिपाएँ',
        'core-alert-banner.dismiss': 'खारिज करें',
      }),
      ar: Object.freeze({
        'core-alert-banner.unread': '{{count, number}} تنبيهات غير مقروءة',
        'core-alert-banner.see_all': 'عرض الكل',
        'core-alert-banner.hide_until_new': 'إخفاء حتى يحدث شيء جديد',
        'core-alert-banner.dismiss': 'تجاهل',
      }),
      zh: Object.freeze({
        'core-alert-banner.unread': '{{count, number}} 条未读提醒',
        'core-alert-banner.see_all': '查看全部',
        'core-alert-banner.hide_until_new': '在有新提醒前隐藏此内容',
        'core-alert-banner.dismiss': '关闭',
      }),
    }),
  }),

  render(rows, helpers) {
    const { target, severityIcon, showView, dismiss, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    target.className = rows.some((row) => row.severity === 'critical')
      ? 'crit'
      : '';
    const fragment = document.createDocumentFragment();
    const heading = document.createElement('strong');
    heading.textContent = t(
      'core-alert-banner.unread',
      { count: rows.length },
      '{{count, number}} unread alert(s)',
    );
    fragment.appendChild(heading);
    const list = document.createElement('ul');
    for (const row of rows.slice(0, 4)) {
      const item = document.createElement('li');
      item.textContent = `${severityIcon(row.severity)} ${row.subject}`;
      list.appendChild(item);
    }
    fragment.appendChild(list);
    const go = document.createElement('button');
    go.className = 'link';
    go.textContent = t('core-alert-banner.see_all', {}, 'See all');
    go.addEventListener('click', () => showView('alerts'));
    fragment.appendChild(go);
    const close = document.createElement('button');
    close.className = 'dismiss';
    close.title = t(
      'core-alert-banner.hide_until_new',
      {},
      'Hide this until something new fires',
    );
    close.setAttribute(
      'aria-label',
      t('core-alert-banner.dismiss', {}, 'Dismiss'),
    );
    close.textContent = '×';
    close.addEventListener('click', dismiss);
    fragment.appendChild(close);
    target.replaceChildren(fragment);
  },
};
