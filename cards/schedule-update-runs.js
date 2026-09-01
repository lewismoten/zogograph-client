/* Core card package: system update-run history table. */
window.ZogographScheduleUpdateRunsCard = {
  manifest: Object.freeze({
    id: 'core-schedule-update-runs',
    locales: ['core-schedule-update-runs.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-schedule-update-runs.started': 'Started',
        'core-schedule-update-runs.stage': 'Stage',
        'core-schedule-update-runs.manager': 'Manager',
        'core-schedule-update-runs.available': 'Available',
        'core-schedule-update-runs.took': 'Took',
        'core-schedule-update-runs.result': 'Result',
        'core-schedule-update-runs.ok': 'ok',
        'core-schedule-update-runs.failed': 'failed',
      }),
      es: Object.freeze({
        'core-schedule-update-runs.started': 'Inicio',
        'core-schedule-update-runs.stage': 'Fase',
        'core-schedule-update-runs.manager': 'Gestor',
        'core-schedule-update-runs.available': 'Disponible',
        'core-schedule-update-runs.took': 'Duración',
        'core-schedule-update-runs.result': 'Resultado',
        'core-schedule-update-runs.ok': 'correcto',
        'core-schedule-update-runs.failed': 'falló',
      }),
      hi: Object.freeze({
        'core-schedule-update-runs.started': 'शुरू हुआ',
        'core-schedule-update-runs.stage': 'चरण',
        'core-schedule-update-runs.manager': 'प्रबंधक',
        'core-schedule-update-runs.available': 'उपलब्ध',
        'core-schedule-update-runs.took': 'लगा समय',
        'core-schedule-update-runs.result': 'परिणाम',
        'core-schedule-update-runs.ok': 'सफल',
        'core-schedule-update-runs.failed': 'विफल',
      }),
      ar: Object.freeze({
        'core-schedule-update-runs.started': 'بدأ',
        'core-schedule-update-runs.stage': 'المرحلة',
        'core-schedule-update-runs.manager': 'المدير',
        'core-schedule-update-runs.available': 'متاح',
        'core-schedule-update-runs.took': 'المدة',
        'core-schedule-update-runs.result': 'النتيجة',
        'core-schedule-update-runs.ok': 'ناجح',
        'core-schedule-update-runs.failed': 'فشل',
      }),
      zh: Object.freeze({
        'core-schedule-update-runs.started': '开始时间',
        'core-schedule-update-runs.stage': '阶段',
        'core-schedule-update-runs.manager': '管理器',
        'core-schedule-update-runs.available': '可用',
        'core-schedule-update-runs.took': '耗时',
        'core-schedule-update-runs.result': '结果',
        'core-schedule-update-runs.ok': '成功',
        'core-schedule-update-runs.failed': '失败',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-update-runs.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
