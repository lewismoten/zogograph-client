/* Core card package: symlink problem filters. */
window.ZogographSymlinkFilterCard = {
  manifest: Object.freeze({
    id: 'core-symlink-filter',
    locales: ['core-symlink-filter.'],
    helpers: ['humanCount', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-filter.all': 'All',
        'core-symlink-filter.broken': 'Broken',
        'core-symlink-filter.looped': 'Looped',
        'core-symlink-filter.external': 'External',
      }),
      es: Object.freeze({
        'core-symlink-filter.all': 'Todos',
        'core-symlink-filter.broken': 'Rotos',
        'core-symlink-filter.looped': 'En bucle',
        'core-symlink-filter.external': 'Externos',
      }),
      hi: Object.freeze({
        'core-symlink-filter.all': 'सभी',
        'core-symlink-filter.broken': 'टूटे हुए',
        'core-symlink-filter.looped': 'लूप वाले',
        'core-symlink-filter.external': 'बाहरी',
      }),
      ar: Object.freeze({
        'core-symlink-filter.all': 'الكل',
        'core-symlink-filter.broken': 'معطلة',
        'core-symlink-filter.looped': 'حلقية',
        'core-symlink-filter.external': 'خارجية',
      }),
      zh: Object.freeze({
        'core-symlink-filter.all': '全部',
        'core-symlink-filter.broken': '损坏',
        'core-symlink-filter.looped': '循环',
        'core-symlink-filter.external': '外部',
      }),
    }),
  }),
  views: Object.freeze({
    all: { key: 'core-symlink-filter.all', fallback: 'All', kinds: null },
    broken: {
      key: 'core-symlink-filter.broken',
      fallback: 'Broken',
      kinds: ['broken'],
    },
    looped: {
      key: 'core-symlink-filter.looped',
      fallback: 'Looped',
      kinds: ['loop', 'ancestor'],
    },
    external: {
      key: 'core-symlink-filter.external',
      fallback: 'External',
      kinds: ['outside'],
    },
  }),
  label(view, i18n) {
    return i18n.t(view.key, {}, view.fallback);
  },
  render(input, helpers) {
    const { data, selected, onSelect } = input;
    const { humanCount, i18n } = helpers;
    const wrap = document.createElement('div');
    wrap.className = 'seg-row';
    for (const [key, view] of Object.entries(this.views)) {
      const count =
        view.kinds === null
          ? data.problems.length
          : view.kinds.reduce(
              (sum, kind) => sum + (data.kinds?.[kind] ?? 0),
              0,
            );
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'seg' + (selected === key ? ' on' : '');
      button.textContent = `${this.label(view, i18n)} ${humanCount(count)}`;
      button.disabled = count === 0 && key !== 'all';
      button.addEventListener('click', () => onSelect(key));
      wrap.appendChild(button);
    }
    return wrap;
  },
};
