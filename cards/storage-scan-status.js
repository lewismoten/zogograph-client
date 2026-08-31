/* Core card package: storage scan state labels. */
window.ZogographStorageScanStatusCard = {
  manifest: Object.freeze({
    id: 'core-storage-scan-status',
    locales: ['core-storage-scan-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-scan-status.title': 'The last scan',
        'core-storage-scan-status.now': 'Scan now',
        'core-storage-scan-status.running': 'Scanning… ({{elapsed}})',
        'core-storage-scan-status.finished': 'Scan finished.',
      }),
      es: Object.freeze({
        'core-storage-scan-status.title': 'El último análisis',
        'core-storage-scan-status.now': 'Analizar ahora',
        'core-storage-scan-status.running': 'Analizando… ({{elapsed}})',
        'core-storage-scan-status.finished': 'Análisis terminado.',
      }),
      hi: Object.freeze({
        'core-storage-scan-status.title': 'पिछला स्कैन',
        'core-storage-scan-status.now': 'अभी स्कैन करें',
        'core-storage-scan-status.running': 'स्कैन हो रहा है… ({{elapsed}})',
        'core-storage-scan-status.finished': 'स्कैन पूरा हुआ।',
      }),
      ar: Object.freeze({
        'core-storage-scan-status.title': 'آخر فحص',
        'core-storage-scan-status.now': 'افحص الآن',
        'core-storage-scan-status.running': 'جارٍ الفحص… ({{elapsed}})',
        'core-storage-scan-status.finished': 'اكتمل الفحص.',
      }),
      zh: Object.freeze({
        'core-storage-scan-status.title': '上次扫描',
        'core-storage-scan-status.now': '立即扫描',
        'core-storage-scan-status.running': '正在扫描…（{{elapsed}}）',
        'core-storage-scan-status.finished': '扫描已完成。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-storage-scan-status.${key}`,
      {},
      this.manifest.catalogs.en[`core-storage-scan-status.${key}`],
    );
  },
  render(input, { i18n }) {
    const { elapsed = '', state } = input;
    const fallback = {
      now: 'Scan now',
      running: `Scanning… (${elapsed})`,
      finished: 'Scan finished.',
    };
    return i18n.t(
      `core-storage-scan-status.${state}`,
      { elapsed },
      fallback[state],
    );
  },
};
