/* Core card package: collector timing and PHP selector controls. */
window.ZogographScheduleJobControlsCard = {
  manifest: Object.freeze({
    id: 'core-schedule-job-controls',
    locales: ['core-schedule-job-controls.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-schedule-job-controls.vitals': 'Vitals collection',
        'core-schedule-job-controls.scan': 'Directory scan',
        'core-schedule-job-controls.minute': 'At minute',
        'core-schedule-job-controls.hour': 'At hour',
        'core-schedule-job-controls.php': 'PHP command-line binary',
      }),
      es: Object.freeze({
        'core-schedule-job-controls.vitals': 'Recopilación de métricas',
        'core-schedule-job-controls.scan': 'Análisis de directorios',
        'core-schedule-job-controls.minute': 'En el minuto',
        'core-schedule-job-controls.hour': 'A la hora',
        'core-schedule-job-controls.php': 'Binario de PHP de línea de comandos',
      }),
      hi: Object.freeze({
        'core-schedule-job-controls.vitals': 'Vitals संग्रह',
        'core-schedule-job-controls.scan': 'निर्देशिका स्कैन',
        'core-schedule-job-controls.minute': 'मिनट पर',
        'core-schedule-job-controls.hour': 'घंटे पर',
        'core-schedule-job-controls.php': 'PHP कमांड-लाइन बाइनरी',
      }),
      ar: Object.freeze({
        'core-schedule-job-controls.vitals': 'جمع القياسات',
        'core-schedule-job-controls.scan': 'فحص الدليل',
        'core-schedule-job-controls.minute': 'عند الدقيقة',
        'core-schedule-job-controls.hour': 'عند الساعة',
        'core-schedule-job-controls.php': 'برنامج PHP لسطر الأوامر',
      }),
      zh: Object.freeze({
        'core-schedule-job-controls.vitals': 'Vitals 采集',
        'core-schedule-job-controls.scan': '目录扫描',
        'core-schedule-job-controls.minute': '在分钟',
        'core-schedule-job-controls.hour': '在小时',
        'core-schedule-job-controls.php': 'PHP 命令行二进制文件',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-job-controls.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
