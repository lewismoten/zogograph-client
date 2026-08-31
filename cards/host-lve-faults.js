/* Core card package: account-level host limit faults. */
window.ZogographHostLveFaultsCard = {
  manifest: Object.freeze({
    id: 'core-host-lve-faults',
    locales: ['core-host-lve-faults.'],
    helpers: ['target', 'renderTable', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-lve-faults.account': 'Account',
        'core-host-lve-faults.cpu_faults': 'CPU faults',
        'core-host-lve-faults.memory_faults': 'Memory faults',
        'core-host-lve-faults.process_faults': 'Process faults',
      }),
      es: Object.freeze({
        'core-host-lve-faults.account': 'Cuenta',
        'core-host-lve-faults.cpu_faults': 'Fallos de CPU',
        'core-host-lve-faults.memory_faults': 'Fallos de memoria',
        'core-host-lve-faults.process_faults': 'Fallos de proceso',
      }),
      hi: Object.freeze({
        'core-host-lve-faults.account': 'खाता',
        'core-host-lve-faults.cpu_faults': 'CPU दोष',
        'core-host-lve-faults.memory_faults': 'मेमोरी दोष',
        'core-host-lve-faults.process_faults': 'प्रोसेस दोष',
      }),
      ar: Object.freeze({
        'core-host-lve-faults.account': 'الحساب',
        'core-host-lve-faults.cpu_faults': 'أعطال CPU',
        'core-host-lve-faults.memory_faults': 'أعطال الذاكرة',
        'core-host-lve-faults.process_faults': 'أعطال العملية',
      }),
      zh: Object.freeze({
        'core-host-lve-faults.account': '账户',
        'core-host-lve-faults.cpu_faults': 'CPU 故障',
        'core-host-lve-faults.memory_faults': '内存故障',
        'core-host-lve-faults.process_faults': '进程故障',
      }),
    }),
  }),
  render({ rows = [] }, { target, renderTable, i18n }) {
    const t = (key, fallback) =>
      i18n.t(`core-host-lve-faults.${key}`, {}, fallback);
    renderTable(target.id, rows, [
      { label: t('account', 'Account'), render: (row) => row.user },
      {
        label: t('cpu_faults', 'CPU faults'),
        className: 'size',
        render: (row) => row.faults_cpu,
      },
      {
        label: t('memory_faults', 'Memory faults'),
        className: 'size',
        render: (row) => row.faults_mem,
      },
      {
        label: t('process_faults', 'Process faults'),
        className: 'size',
        render: (row) => row.faults_procs,
      },
    ]);
  },
};
