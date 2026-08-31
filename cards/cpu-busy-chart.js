/* Core card package: the shell supplies the CPU-specific empty-state helper. */
window.ZogographCpuBusyChartCard = {
  manifest: Object.freeze({
    id: 'core-cpu-busy-chart',
    locales: ['core-cpu-busy-chart.'],
    helpers: ['chart', 'drawLineChart', 'percentAxis', 'empty', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-busy-chart.title': 'Busy, and what it was busy with',
        'core-cpu-busy-chart.help':
          'The total busy line is split into the states that make it up. The monitor tree and the remaining workload are also shown: together they explain the total, while alerts continue to use total host CPU. Percentages are of every core added together, so 100% is the whole machine occupied rather than one core of it.',
        'core-cpu-busy-chart.empty': 'No CPU readings.',
        'core-cpu-busy-chart.busy': 'Busy',
        'core-cpu-busy-chart.monitoring_tree': 'Monitoring tree',
        'core-cpu-busy-chart.workload_excluding_monitor':
          'Workload (excl. monitor)',
        'core-cpu-busy-chart.userland': 'Userland',
        'core-cpu-busy-chart.kernel': 'Kernel',
        'core-cpu-busy-chart.waiting_for_storage': 'Waiting for storage',
        'core-cpu-busy-chart.stolen_by_host': 'Stolen by the host',
      }),
      es: Object.freeze({
        'core-cpu-busy-chart.title': 'Ocupación y en qué estuvo ocupada',
        'core-cpu-busy-chart.help':
          'La línea total de ocupación se divide en los estados que la componen. También se muestran el árbol del monitor y la carga de trabajo restante: juntos explican el total, mientras que las alertas siguen usando la CPU total del host. Los porcentajes suman todos los núcleos, por lo que 100% significa que toda la máquina está ocupada, no un solo núcleo.',
        'core-cpu-busy-chart.empty': 'No hay lecturas de CPU.',
        'core-cpu-busy-chart.busy': 'Ocupado',
        'core-cpu-busy-chart.monitoring_tree': 'Árbol de supervisión',
        'core-cpu-busy-chart.workload_excluding_monitor':
          'Carga de trabajo (sin monitor)',
        'core-cpu-busy-chart.userland': 'Espacio de usuario',
        'core-cpu-busy-chart.kernel': 'Kernel',
        'core-cpu-busy-chart.waiting_for_storage': 'Esperando almacenamiento',
        'core-cpu-busy-chart.stolen_by_host': 'Robado por el host',
      }),
      hi: Object.freeze({
        'core-cpu-busy-chart.title': 'व्यस्तता और उसका कारण',
        'core-cpu-busy-chart.help':
          'कुल व्यस्त रेखा को बनाने वाली अवस्थाओं में बाँटा गया है। मॉनिटर ट्री और बचा हुआ कार्यभार भी दिखता है: वे मिलकर कुल को समझाते हैं, जबकि अलर्ट कुल होस्ट CPU का उपयोग करते रहते हैं। प्रतिशत सभी कोरों का जोड़ा है, इसलिए 100% का अर्थ पूरी मशीन व्यस्त है, एक कोर नहीं।',
        'core-cpu-busy-chart.empty': 'कोई CPU रीडिंग नहीं है।',
        'core-cpu-busy-chart.busy': 'व्यस्त',
        'core-cpu-busy-chart.monitoring_tree': 'निगरानी ट्री',
        'core-cpu-busy-chart.workload_excluding_monitor':
          'कार्यभार (मॉनिटर को छोड़कर)',
        'core-cpu-busy-chart.userland': 'यूज़र स्पेस',
        'core-cpu-busy-chart.kernel': 'कर्नेल',
        'core-cpu-busy-chart.waiting_for_storage': 'स्टोरेज की प्रतीक्षा',
        'core-cpu-busy-chart.stolen_by_host': 'होस्ट द्वारा लिया गया',
      }),
      ar: Object.freeze({
        'core-cpu-busy-chart.title': 'الانشغال وما كان مشغولًا به',
        'core-cpu-busy-chart.help':
          'يُقسّم خط الانشغال الإجمالي إلى الحالات التي تكوّنه. تظهر أيضًا شجرة المراقب وعبء العمل الباقي: وهما معًا يفسران الإجمالي، فيما تستمر التنبيهات في استخدام إجمالي معالج المضيف. النسب هي مجموع كل الأنوية، لذا 100% تعني أن الآلة كلها مشغولة لا نواة واحدة.',
        'core-cpu-busy-chart.empty': 'لا توجد قراءات للمعالج.',
        'core-cpu-busy-chart.busy': 'مشغول',
        'core-cpu-busy-chart.monitoring_tree': 'شجرة المراقبة',
        'core-cpu-busy-chart.workload_excluding_monitor':
          'عبء العمل (دون المراقب)',
        'core-cpu-busy-chart.userland': 'مساحة المستخدم',
        'core-cpu-busy-chart.kernel': 'النواة',
        'core-cpu-busy-chart.waiting_for_storage': 'ينتظر التخزين',
        'core-cpu-busy-chart.stolen_by_host': 'استولى عليه المضيف',
      }),
      zh: Object.freeze({
        'core-cpu-busy-chart.title': '忙碌程度及其原因',
        'core-cpu-busy-chart.help':
          '总忙碌线按构成它的状态拆分。还会显示监控树和其余工作负载：它们共同解释总量，而警报仍使用主机 CPU 总量。百分比是所有核心相加的结果，因此 100% 表示整台机器被占用，而不是一个核心。',
        'core-cpu-busy-chart.empty': '没有 CPU 读数。',
        'core-cpu-busy-chart.busy': '繁忙',
        'core-cpu-busy-chart.monitoring_tree': '监控树',
        'core-cpu-busy-chart.workload_excluding_monitor':
          '工作负载（不含监控）',
        'core-cpu-busy-chart.userland': '用户空间',
        'core-cpu-busy-chart.kernel': '内核',
        'core-cpu-busy-chart.waiting_for_storage': '正在等待存储',
        'core-cpu-busy-chart.stolen_by_host': '被主机占用',
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-cpu-busy-chart.${key}`,
      {},
      this.manifest.catalogs.en[`core-cpu-busy-chart.${key}`],
    );
  },

  render(data, helpers) {
    const { chart, drawLineChart, percentAxis, empty, i18n } = helpers;
    if (!chart) return;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    drawLineChart(
      chart,
      data.series || [],
      [
        { key: 'busy', label: t('core-cpu-busy-chart.busy', {}, 'Busy') },
        {
          key: 'monitoring_cpu_percent',
          label: t(
            'core-cpu-busy-chart.monitoring_tree',
            {},
            'Monitoring tree',
          ),
        },
        {
          key: 'workload_cpu_percent',
          label: t(
            'core-cpu-busy-chart.workload_excluding_monitor',
            {},
            'Workload (excl. monitor)',
          ),
        },
        {
          key: 'user',
          label: t('core-cpu-busy-chart.userland', {}, 'Userland'),
        },
        { key: 'system', label: t('core-cpu-busy-chart.kernel', {}, 'Kernel') },
        {
          key: 'iowait',
          label: t(
            'core-cpu-busy-chart.waiting_for_storage',
            {},
            'Waiting for storage',
          ),
        },
        {
          key: 'steal',
          label: t(
            'core-cpu-busy-chart.stolen_by_host',
            {},
            'Stolen by the host',
          ),
        },
      ],
      {
        axis: percentAxis,
        zeroBased: true,
        max: 100,
        empty:
          empty(data) || t('core-cpu-busy-chart.empty', {}, 'No CPU readings.'),
      },
    );
  },
};
