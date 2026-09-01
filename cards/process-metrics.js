/* Core card package: process-ranking and CPU measurement annotations. */
window.ZogographProcessMetricsCard = {
  manifest: Object.freeze({
    id: 'core-process-metrics',
    locales: ['core-process-metrics.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-process-metrics.unavailable':
          'Cannot read the process list here.',
        'core-process-metrics.rank_largest': 'The largest in this column.',
        'core-process-metrics.rank_second_largest':
          'The second largest in this column.',
        'core-process-metrics.cpu_cores': '{{share}} of {{cores}} cores',
        'core-process-metrics.cpu_cores_worth': "{{share}} cores' worth",
        'core-process-metrics.cpu_help':
          'ps measures each process against one core, so anything threaded can pass 100%. The CPU figure on the overview is a share of the whole machine and is a different measurement.',
      }),
      es: Object.freeze({
        'core-process-metrics.unavailable':
          'No se puede leer aquí la lista de procesos.',
        'core-process-metrics.rank_largest': 'El mayor de esta columna.',
        'core-process-metrics.rank_second_largest':
          'El segundo mayor de esta columna.',
        'core-process-metrics.cpu_cores': '{{share}} de {{cores}} núcleos',
        'core-process-metrics.cpu_cores_worth': 'equivale a {{share}} núcleos',
        'core-process-metrics.cpu_help':
          'ps mide cada proceso frente a un núcleo, por lo que cualquier proceso con hilos puede superar el 100 %. La cifra de CPU del resumen es una parte de toda la máquina y es una medida diferente.',
      }),
      hi: Object.freeze({
        'core-process-metrics.unavailable':
          'यहाँ प्रक्रिया सूची नहीं पढ़ी जा सकती।',
        'core-process-metrics.rank_largest': 'इस कॉलम में सबसे बड़ा।',
        'core-process-metrics.rank_second_largest':
          'इस कॉलम में दूसरा सबसे बड़ा।',
        'core-process-metrics.cpu_cores': '{{cores}} कोर में से {{share}}',
        'core-process-metrics.cpu_cores_worth': '{{share}} कोर के बराबर',
        'core-process-metrics.cpu_help':
          'ps हर प्रक्रिया को एक कोर के विरुद्ध मापता है, इसलिए थ्रेड वाली कोई भी चीज़ 100% पार कर सकती है। अवलोकन पर CPU आंकड़ा पूरी मशीन का हिस्सा है और अलग माप है।',
      }),
      ar: Object.freeze({
        'core-process-metrics.unavailable': 'تعذر قراءة قائمة العمليات هنا.',
        'core-process-metrics.rank_largest': 'الأكبر في هذا العمود.',
        'core-process-metrics.rank_second_largest':
          'ثاني أكبر قيمة في هذا العمود.',
        'core-process-metrics.cpu_cores': '{{share}} من {{cores}} أنوية',
        'core-process-metrics.cpu_cores_worth': 'ما يعادل {{share}} أنوية',
        'core-process-metrics.cpu_help':
          'يقيس ps كل عملية مقابل نواة واحدة، لذا يمكن لأي شيء متعدد الخيوط أن يتجاوز 100٪. رقم CPU في النظرة العامة هو حصة من الجهاز كله وهو قياس مختلف.',
      }),
      zh: Object.freeze({
        'core-process-metrics.unavailable': '无法在此读取进程列表。',
        'core-process-metrics.rank_largest': '此列中最大。',
        'core-process-metrics.rank_second_largest': '此列中第二大。',
        'core-process-metrics.cpu_cores': '{{cores}} 个核心中的 {{share}}',
        'core-process-metrics.cpu_cores_worth': '相当于 {{share}} 个核心',
        'core-process-metrics.cpu_help':
          'ps 按单个核心衡量每个进程，因此任何多线程进程都可能超过 100%。概览中的 CPU 数值是整台机器的占比，是不同的度量方式。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-process-metrics.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
