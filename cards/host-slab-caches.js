/* Core card package: kernel slab caches seen by the host collector. */
window.ZogographHostSlabCachesCard = {
  manifest: Object.freeze({
    id: 'core-host-slab-caches',
    locales: ['core-host-slab-caches.'],
    helpers: ['target', 'renderTable', 'namedThing', 'humanBytes', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-slab-caches.title': 'Kernel slab caches',
        'core-host-slab-caches.cache': 'Cache',
        'core-host-slab-caches.holding': 'Holding',
        'core-host-slab-caches.objects': 'Objects',
        'core-host-slab-caches.each': 'Each',
      }),
      es: Object.freeze({
        'core-host-slab-caches.title': 'Cachés slab del kernel',
        'core-host-slab-caches.cache': 'Caché',
        'core-host-slab-caches.holding': 'Almacenado',
        'core-host-slab-caches.objects': 'Objetos',
        'core-host-slab-caches.each': 'Cada uno',
      }),
      hi: Object.freeze({
        'core-host-slab-caches.title': 'कर्नेल स्लैब कैश',
        'core-host-slab-caches.cache': 'कैश',
        'core-host-slab-caches.holding': 'रखा है',
        'core-host-slab-caches.objects': 'ऑब्जेक्ट',
        'core-host-slab-caches.each': 'हर एक',
      }),
      ar: Object.freeze({
        'core-host-slab-caches.title': 'ذاكرات slab المؤقتة للنواة',
        'core-host-slab-caches.cache': 'ذاكرة مؤقتة',
        'core-host-slab-caches.holding': 'المحفوظ',
        'core-host-slab-caches.objects': 'الكائنات',
        'core-host-slab-caches.each': 'لكل واحد',
      }),
      zh: Object.freeze({
        'core-host-slab-caches.title': '内核 slab 缓存',
        'core-host-slab-caches.cache': '缓存',
        'core-host-slab-caches.holding': '占用',
        'core-host-slab-caches.objects': '对象',
        'core-host-slab-caches.each': '每个',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-slab-caches.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-slab-caches.${key}`],
    );
  },
  render({ rows = [] }, { target, renderTable, namedThing, humanBytes, i18n }) {
    const t = (key, fallback) =>
      i18n.t(`core-host-slab-caches.${key}`, {}, fallback);
    renderTable(target.id, rows, [
      {
        label: t('cache', 'Cache'),
        render: (row) => namedThing('slab', row.name),
      },
      {
        label: t('holding', 'Holding'),
        className: 'size',
        render: (row) => humanBytes(row.cache_bytes),
      },
      {
        label: t('objects', 'Objects'),
        className: 'size',
        render: (row) =>
          Number(row.objs).toLocaleString(
            document.documentElement.lang || undefined,
          ),
      },
      {
        label: t('each', 'Each'),
        className: 'size',
        render: (row) => humanBytes(row.obj_bytes),
      },
    ]);
  },
};
