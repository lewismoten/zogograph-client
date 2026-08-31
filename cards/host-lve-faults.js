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
        'core-host-lve-faults.help':
          'A process killed for hitting an LVE limit leaves no other trace. A rising fault count is the evidence.',
      }),
      es: Object.freeze({
        'core-host-lve-faults.account': 'Cuenta',
        'core-host-lve-faults.cpu_faults': 'Fallos de CPU',
        'core-host-lve-faults.memory_faults': 'Fallos de memoria',
        'core-host-lve-faults.process_faults': 'Fallos de proceso',
        'core-host-lve-faults.help':
          'Un proceso finalizado por alcanzar un límite LVE no deja otro rastro. Un número creciente de fallos es la evidencia.',
      }),
      hi: Object.freeze({
        'core-host-lve-faults.account': 'खाता',
        'core-host-lve-faults.cpu_faults': 'CPU दोष',
        'core-host-lve-faults.memory_faults': 'मेमोरी दोष',
        'core-host-lve-faults.process_faults': 'प्रोसेस दोष',
        'core-host-lve-faults.help':
          'LVE सीमा पर समाप्त किया गया प्रोसेस कोई अन्य निशान नहीं छोड़ता। बढ़ती fault संख्या इसका प्रमाण है।',
      }),
      ar: Object.freeze({
        'core-host-lve-faults.account': 'الحساب',
        'core-host-lve-faults.cpu_faults': 'أعطال CPU',
        'core-host-lve-faults.memory_faults': 'أعطال الذاكرة',
        'core-host-lve-faults.process_faults': 'أعطال العملية',
        'core-host-lve-faults.help':
          'العملية التي تُقتل لبلوغ حد LVE لا تترك أثرًا آخر. ازدياد عدد الأعطال هو الدليل.',
      }),
      zh: Object.freeze({
        'core-host-lve-faults.account': '账户',
        'core-host-lve-faults.cpu_faults': 'CPU 故障',
        'core-host-lve-faults.memory_faults': '内存故障',
        'core-host-lve-faults.process_faults': '进程故障',
        'core-host-lve-faults.help':
          '因达到 LVE 限制而被终止的进程不会留下其他痕迹。不断增加的故障计数就是证据。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-lve-faults.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-lve-faults.${key}`],
    );
  },
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
