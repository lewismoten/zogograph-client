/* Core card package: kernel slab caches seen by the host collector. */
window.ZogographHostSlabCachesCard = {
  manifest: Object.freeze({
    id: 'core-host-slab-caches',
    locales: ['core-host-slab-caches.'],
    helpers: ['target', 'renderTable', 'namedThing', 'humanBytes', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-slab-caches.title': 'Kernel slab caches',
        'core-host-slab-caches.summary':
          'Memory the kernel holds for itself. It belongs to no process and no account, and it is the first place to look when the numbers do not add up — a quarter of a gigabyte of xfs_inode appears in nothing else on this site.',
        'core-host-slab-caches.cache': 'Cache',
        'core-host-slab-caches.holding': 'Holding',
        'core-host-slab-caches.objects': 'Objects',
        'core-host-slab-caches.each': 'Each',
      }),
      es: Object.freeze({
        'core-host-slab-caches.title': 'Cachés slab del kernel',
        'core-host-slab-caches.summary':
          'Memoria que el kernel retiene para sí mismo. No pertenece a ningún proceso ni cuenta y es el primer lugar que revisar cuando los números no cuadran: un cuarto de gigabyte de xfs_inode no aparece en ningún otro lugar del sitio.',
        'core-host-slab-caches.cache': 'Caché',
        'core-host-slab-caches.holding': 'Almacenado',
        'core-host-slab-caches.objects': 'Objetos',
        'core-host-slab-caches.each': 'Cada uno',
      }),
      hi: Object.freeze({
        'core-host-slab-caches.title': 'कर्नेल स्लैब कैश',
        'core-host-slab-caches.summary':
          'मेमोरी जिसे कर्नेल अपने लिए रखता है। यह किसी प्रोसेस या खाते की नहीं होती और जब संख्याएँ मेल न खाएँ तो पहले यहीं देखें: xfs_inode का एक चौथाई गीगाबाइट इस साइट पर और कहीं नहीं दिखता।',
        'core-host-slab-caches.cache': 'कैश',
        'core-host-slab-caches.holding': 'रखा है',
        'core-host-slab-caches.objects': 'ऑब्जेक्ट',
        'core-host-slab-caches.each': 'हर एक',
      }),
      ar: Object.freeze({
        'core-host-slab-caches.title': 'ذاكرات slab المؤقتة للنواة',
        'core-host-slab-caches.summary':
          'ذاكرة تحتفظ بها النواة لنفسها. لا تنتمي إلى عملية أو حساب، وهي أول مكان لفحصه عندما لا تتطابق الأرقام: لا يظهر ربع غيغابايت من xfs_inode في أي موضع آخر من الموقع.',
        'core-host-slab-caches.cache': 'ذاكرة مؤقتة',
        'core-host-slab-caches.holding': 'المحفوظ',
        'core-host-slab-caches.objects': 'الكائنات',
        'core-host-slab-caches.each': 'لكل واحد',
      }),
      zh: Object.freeze({
        'core-host-slab-caches.title': '内核 slab 缓存',
        'core-host-slab-caches.summary':
          '内核为自己保留的内存。它不属于任何进程或账户；当数值对不上时应先查看这里：四分之一 GB 的 xfs_inode 不会出现在本网站的其他位置。',
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
