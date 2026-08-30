/* Core card package: messages left for the next operator to open the site. */
window.ZogographBroadcastBannerCard = {
  manifest: Object.freeze({
    id: 'core-broadcast-banner',
    locales: ['core-broadcast-banner.'],
    helpers: ['target', 'ago', 'externalLink', 'clear', 'notice', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-broadcast-banner.one': 'A message from whoever was here last',
        'core-broadcast-banner.many':
          '{{count, number}} messages from whoever was here last',
        'core-broadcast-banner.who': '{{who}}, {{ago}}',
        'core-broadcast-banner.somebody': 'somebody',
        'core-broadcast-banner.clear': 'Clear it',
        'core-broadcast-banner.clear_help': 'Stop showing this to everyone',
      }),
      es: Object.freeze({
        'core-broadcast-banner.one':
          'Un mensaje de quien estuvo aquí por última vez',
        'core-broadcast-banner.many':
          '{{count, number}} mensajes de quien estuvo aquí por última vez',
        'core-broadcast-banner.who': '{{who}}, {{ago}}',
        'core-broadcast-banner.somebody': 'alguien',
        'core-broadcast-banner.clear': 'Borrarlo',
        'core-broadcast-banner.clear_help': 'Dejar de mostrarlo a todos',
      }),
      hi: Object.freeze({
        'core-broadcast-banner.one': 'पिछली बार यहाँ आए व्यक्ति का संदेश',
        'core-broadcast-banner.many':
          'पिछली बार यहाँ आए व्यक्ति के {{count, number}} संदेश',
        'core-broadcast-banner.who': '{{who}}, {{ago}}',
        'core-broadcast-banner.somebody': 'कोई व्यक्ति',
        'core-broadcast-banner.clear': 'हटाएँ',
        'core-broadcast-banner.clear_help': 'इसे सबको दिखाना बंद करें',
      }),
      ar: Object.freeze({
        'core-broadcast-banner.one': 'رسالة ممن كان هنا آخر مرة',
        'core-broadcast-banner.many':
          '{{count, number}} رسائل ممن كان هنا آخر مرة',
        'core-broadcast-banner.who': '{{who}}، {{ago}}',
        'core-broadcast-banner.somebody': 'شخص ما',
        'core-broadcast-banner.clear': 'مسحها',
        'core-broadcast-banner.clear_help': 'إيقاف عرضها للجميع',
      }),
      zh: Object.freeze({
        'core-broadcast-banner.one': '上次在此操作的人留下的消息',
        'core-broadcast-banner.many':
          '上次在此操作的人留下的 {{count, number}} 条消息',
        'core-broadcast-banner.who': '{{who}}，{{ago}}',
        'core-broadcast-banner.somebody': '某人',
        'core-broadcast-banner.clear': '清除',
        'core-broadcast-banner.clear_help': '停止向所有人显示此消息',
      }),
    }),
  }),

  render(rows, helpers) {
    const { target, ago, externalLink, clear, notice, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    const fragment = document.createDocumentFragment();
    const heading = document.createElement('strong');
    heading.textContent =
      rows.length === 1
        ? t(
            'core-broadcast-banner.one',
            {},
            'A message from whoever was here last',
          )
        : t(
            'core-broadcast-banner.many',
            { count: rows.length },
            '{{count, number}} messages from whoever was here last',
          );
    fragment.appendChild(heading);
    for (const row of rows) {
      const item = document.createElement('div');
      item.className = 'broadcast';
      const text = document.createElement('div');
      text.textContent = row.body;
      item.appendChild(text);
      const who = document.createElement('div');
      who.className = 'who';
      who.textContent = t(
        'core-broadcast-banner.who',
        {
          who: row.who || t('core-broadcast-banner.somebody', {}, 'somebody'),
          ago: ago(row.at),
        },
        '{{who}}, {{ago}}',
      );
      item.appendChild(who);
      if (row.link) item.appendChild(externalLink(row.link));
      const clearButton = document.createElement('button');
      clearButton.className = 'link';
      clearButton.textContent = t(
        'core-broadcast-banner.clear',
        {},
        'Clear it',
      );
      clearButton.title = t(
        'core-broadcast-banner.clear_help',
        {},
        'Stop showing this to everyone',
      );
      clearButton.addEventListener('click', async () => {
        clearButton.disabled = true;
        try {
          await clear(row.id);
        } catch (error) {
          notice(String(error.message || error));
          clearButton.disabled = false;
        }
      });
      item.appendChild(clearButton);
      fragment.appendChild(item);
    }
    target.replaceChildren(fragment);
  },
};
