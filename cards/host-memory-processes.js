/* Core card package: host-wide processes behind the memory breakdown. */
window.ZogographHostMemoryProcessesCard = {
  manifest: Object.freeze({
    id: 'core-host-memory-processes',
    locales: ['core-host-memory-processes.'],
    helpers: [
      'target',
      'renderTable',
      'userCell',
      'namedThing',
      'humanBytes',
      'hostPss',
      'humanDuration',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-memory-processes.title': 'Biggest processes on the host',
        'core-host-memory-processes.pid': 'PID',
        'core-host-memory-processes.user': 'User',
        'core-host-memory-processes.process': 'Process',
        'core-host-memory-processes.resident': 'Resident',
        'core-host-memory-processes.proportional_pss': 'Proportional (PSS)',
        'core-host-memory-processes.swapped': 'Swapped',
        'core-host-memory-processes.threads': 'Threads',
        'core-host-memory-processes.running_for': 'Running for',
        'core-host-memory-processes.command': 'Command',
      }),
      es: Object.freeze({
        'core-host-memory-processes.title': 'Procesos más grandes del host',
        'core-host-memory-processes.pid': 'PID',
        'core-host-memory-processes.user': 'Usuario',
        'core-host-memory-processes.process': 'Proceso',
        'core-host-memory-processes.resident': 'Residente',
        'core-host-memory-processes.proportional_pss': 'Proporcional (PSS)',
        'core-host-memory-processes.swapped': 'En intercambio',
        'core-host-memory-processes.threads': 'Hilos',
        'core-host-memory-processes.running_for': 'En ejecución',
        'core-host-memory-processes.command': 'Comando',
      }),
      hi: Object.freeze({
        'core-host-memory-processes.title': 'होस्ट पर सबसे बड़े प्रोसेस',
        'core-host-memory-processes.pid': 'PID',
        'core-host-memory-processes.user': 'उपयोगकर्ता',
        'core-host-memory-processes.process': 'प्रोसेस',
        'core-host-memory-processes.resident': 'रेज़िडेंट',
        'core-host-memory-processes.proportional_pss': 'आनुपातिक (PSS)',
        'core-host-memory-processes.swapped': 'स्वैप्ड',
        'core-host-memory-processes.threads': 'थ्रेड',
        'core-host-memory-processes.running_for': 'चल रहा है',
        'core-host-memory-processes.command': 'कमांड',
      }),
      ar: Object.freeze({
        'core-host-memory-processes.title': 'أكبر العمليات على المضيف',
        'core-host-memory-processes.pid': 'PID',
        'core-host-memory-processes.user': 'المستخدم',
        'core-host-memory-processes.process': 'العملية',
        'core-host-memory-processes.resident': 'المقيم',
        'core-host-memory-processes.proportional_pss': 'التناسبي (PSS)',
        'core-host-memory-processes.swapped': 'المبدّل',
        'core-host-memory-processes.threads': 'الخيوط',
        'core-host-memory-processes.running_for': 'وقت التشغيل',
        'core-host-memory-processes.command': 'الأمر',
      }),
      zh: Object.freeze({
        'core-host-memory-processes.title': '主机上最大的进程',
        'core-host-memory-processes.pid': 'PID',
        'core-host-memory-processes.user': '用户',
        'core-host-memory-processes.process': '进程',
        'core-host-memory-processes.resident': '常驻',
        'core-host-memory-processes.proportional_pss': '比例集大小 (PSS)',
        'core-host-memory-processes.swapped': '已交换',
        'core-host-memory-processes.threads': '线程',
        'core-host-memory-processes.running_for': '运行时长',
        'core-host-memory-processes.command': '命令',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-memory-processes.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-memory-processes.${key}`],
    );
  },
  render({ rows = [] }, helpers) {
    const {
      target,
      renderTable,
      userCell,
      namedThing,
      humanBytes,
      hostPss,
      humanDuration,
      i18n,
    } = helpers;
    const t = (key, fallback) =>
      i18n.t(`core-host-memory-processes.${key}`, {}, fallback);
    renderTable(target.id, rows, [
      { label: t('pid', 'PID'), className: 'size', render: (row) => row.pid },
      { label: t('user', 'User'), render: (row) => userCell(row.user) },
      {
        label: t('process', 'Process'),
        render: (row) => namedThing('process', row.name),
      },
      {
        label: t('resident', 'Resident'),
        className: 'size',
        render: (row) => humanBytes(row.rss_bytes),
      },
      {
        label: t('proportional_pss', 'Proportional (PSS)'),
        className: 'size',
        render: (row) => hostPss(row.pss_bytes),
      },
      {
        label: t('swapped', 'Swapped'),
        className: 'size',
        render: (row) => humanBytes(row.swap_bytes),
      },
      {
        label: t('threads', 'Threads'),
        className: 'size',
        render: (row) => row.threads,
      },
      {
        label: t('running_for', 'Running for'),
        className: 'size',
        render: (row) =>
          row.elapsed_seconds == null
            ? '—'
            : humanDuration(row.elapsed_seconds),
      },
      {
        label: t('command', 'Command'),
        className: 'cmd',
        render: (row) => row.command,
      },
    ]);
  },
};
