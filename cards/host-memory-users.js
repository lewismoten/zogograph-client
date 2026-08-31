/* Core card package: host-wide memory ownership by account. */
window.ZogographHostMemoryUsersCard = {
  manifest: Object.freeze({
    id: 'core-host-memory-users',
    locales: ['core-host-memory-users.'],
    helpers: [
      'target',
      'renderTable',
      'userCell',
      'rankedCell',
      'humanBytes',
      'hostPss',
      'topTwo',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-memory-users.title': 'Memory by user',
        'core-host-memory-users.user': 'User',
        'core-host-memory-users.processes': 'Processes',
        'core-host-memory-users.threads': 'Threads',
        'core-host-memory-users.resident': 'Resident',
        'core-host-memory-users.proportional_pss': 'Proportional (PSS)',
        'core-host-memory-users.swapped': 'Swapped',
      }),
      es: Object.freeze({
        'core-host-memory-users.title': 'Memoria por usuario',
        'core-host-memory-users.user': 'Usuario',
        'core-host-memory-users.processes': 'Procesos',
        'core-host-memory-users.threads': 'Hilos',
        'core-host-memory-users.resident': 'Residente',
        'core-host-memory-users.proportional_pss': 'Proporcional (PSS)',
        'core-host-memory-users.swapped': 'En intercambio',
      }),
      hi: Object.freeze({
        'core-host-memory-users.title': 'उपयोगकर्ता के अनुसार मेमोरी',
        'core-host-memory-users.user': 'उपयोगकर्ता',
        'core-host-memory-users.processes': 'प्रोसेस',
        'core-host-memory-users.threads': 'थ्रेड',
        'core-host-memory-users.resident': 'रेज़िडेंट',
        'core-host-memory-users.proportional_pss': 'आनुपातिक (PSS)',
        'core-host-memory-users.swapped': 'स्वैप्ड',
      }),
      ar: Object.freeze({
        'core-host-memory-users.title': 'الذاكرة حسب المستخدم',
        'core-host-memory-users.user': 'المستخدم',
        'core-host-memory-users.processes': 'العمليات',
        'core-host-memory-users.threads': 'الخيوط',
        'core-host-memory-users.resident': 'المقيم',
        'core-host-memory-users.proportional_pss': 'التناسبي (PSS)',
        'core-host-memory-users.swapped': 'المبدّل',
      }),
      zh: Object.freeze({
        'core-host-memory-users.title': '按用户划分的内存',
        'core-host-memory-users.user': '用户',
        'core-host-memory-users.processes': '进程',
        'core-host-memory-users.threads': '线程',
        'core-host-memory-users.resident': '常驻',
        'core-host-memory-users.proportional_pss': '比例集大小 (PSS)',
        'core-host-memory-users.swapped': '已交换',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-memory-users.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-memory-users.${key}`],
    );
  },
  render({ rows = [] }, helpers) {
    const {
      target,
      renderTable,
      userCell,
      rankedCell,
      humanBytes,
      hostPss,
      topTwo,
      i18n,
    } = helpers;
    const rank = topTwo(rows, ['rss_bytes', 'pss_bytes', 'swap_bytes']);
    const t = (key, fallback) =>
      i18n.t(`core-host-memory-users.${key}`, {}, fallback);
    renderTable(target.id, rows, [
      { label: t('user', 'User'), render: (row) => userCell(row.user) },
      {
        label: t('processes', 'Processes'),
        className: 'size',
        render: (row) => row.procs,
      },
      {
        label: t('threads', 'Threads'),
        className: 'size',
        render: (row) => row.threads,
      },
      {
        label: t('resident', 'Resident'),
        className: 'size',
        render: (row) =>
          rankedCell(humanBytes(row.rss_bytes), rank.rss_bytes, row),
      },
      {
        label: t('proportional_pss', 'Proportional (PSS)'),
        className: 'size',
        render: (row) =>
          rankedCell(hostPss(row.pss_bytes), rank.pss_bytes, row),
      },
      {
        label: t('swapped', 'Swapped'),
        className: 'size',
        render: (row) =>
          rankedCell(humanBytes(row.swap_bytes), rank.swap_bytes, row),
      },
    ]);
  },
};
