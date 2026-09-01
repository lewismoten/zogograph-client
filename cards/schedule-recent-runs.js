/* Core card package: recent collector-run ledger. */
window.ZogographScheduleRecentRunsCard = {
  manifest: Object.freeze({
    id: 'core-schedule-recent-runs',
    locales: ['core-schedule-recent-runs.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-schedule-recent-runs.recent_runs': 'Recent collector runs',
        'core-schedule-recent-runs.recent_runs_help':
          'Normal and requested work share this ledger, so a new task can be compared with the work already scheduled.',
        'core-schedule-recent-runs.started': 'Started',
        'core-schedule-recent-runs.collector': 'Collector',
        'core-schedule-recent-runs.work': 'Work',
        'core-schedule-recent-runs.took': 'Took',
        'core-schedule-recent-runs.result': 'Result',
        'core-schedule-recent-runs.ok': 'ok',
        'core-schedule-recent-runs.failed': 'failed',
      }),
      es: Object.freeze({
        'core-schedule-recent-runs.recent_runs':
          'Ejecuciones recientes del recopilador',
        'core-schedule-recent-runs.recent_runs_help':
          'El trabajo normal y el solicitado comparten este registro, por lo que puede comparar una tarea nueva con el trabajo ya programado.',
        'core-schedule-recent-runs.started': 'Inicio',
        'core-schedule-recent-runs.collector': 'Recopilador',
        'core-schedule-recent-runs.work': 'Trabajo',
        'core-schedule-recent-runs.took': 'Duración',
        'core-schedule-recent-runs.result': 'Resultado',
        'core-schedule-recent-runs.ok': 'correcto',
        'core-schedule-recent-runs.failed': 'falló',
      }),
      hi: Object.freeze({
        'core-schedule-recent-runs.recent_runs': 'कलेक्टर के हाल के रन',
        'core-schedule-recent-runs.recent_runs_help':
          'सामान्य और अनुरोधित काम इस एक ही लेखे में होते हैं, इसलिए नए कार्य की तुलना पहले से निर्धारित काम से की जा सकती है।',
        'core-schedule-recent-runs.started': 'शुरू हुआ',
        'core-schedule-recent-runs.collector': 'कलेक्टर',
        'core-schedule-recent-runs.work': 'काम',
        'core-schedule-recent-runs.took': 'लगा समय',
        'core-schedule-recent-runs.result': 'परिणाम',
        'core-schedule-recent-runs.ok': 'सफल',
        'core-schedule-recent-runs.failed': 'विफल',
      }),
      ar: Object.freeze({
        'core-schedule-recent-runs.recent_runs':
          'عمليات التشغيل الأخيرة للجامع',
        'core-schedule-recent-runs.recent_runs_help':
          'يشترك العمل العادي والمطلوب في هذا السجل، لذا يمكن مقارنة المهمة الجديدة بالعمل المجدول بالفعل.',
        'core-schedule-recent-runs.started': 'بدأ',
        'core-schedule-recent-runs.collector': 'الجامع',
        'core-schedule-recent-runs.work': 'العمل',
        'core-schedule-recent-runs.took': 'المدة',
        'core-schedule-recent-runs.result': 'النتيجة',
        'core-schedule-recent-runs.ok': 'ناجح',
        'core-schedule-recent-runs.failed': 'فشل',
      }),
      zh: Object.freeze({
        'core-schedule-recent-runs.recent_runs': '最近的收集器运行',
        'core-schedule-recent-runs.recent_runs_help':
          '常规工作和请求的工作共用此记录，因此可以将新任务与已安排的工作进行比较。',
        'core-schedule-recent-runs.started': '开始时间',
        'core-schedule-recent-runs.collector': '收集器',
        'core-schedule-recent-runs.work': '工作',
        'core-schedule-recent-runs.took': '耗时',
        'core-schedule-recent-runs.result': '结果',
        'core-schedule-recent-runs.ok': '成功',
        'core-schedule-recent-runs.failed': '失败',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-recent-runs.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
