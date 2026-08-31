/* Core card package: CPU-state summaries share the shell's chart and labels. */
window.ZogographCpuTimeCard = {
  manifest: Object.freeze({
    id: 'core-cpu-time',
    locales: ['core-cpu-time.'],
    helpers: [
      'setCard',
      'note',
      'tableTarget',
      'renderTable',
      'bandOf',
      'cpuStateMeaning',
      'emptyMessage',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-time.average_over_range':
          '{{percent}}% on average over this range',
        'core-cpu-time.state': 'State',
        'core-cpu-time.in_proc_stat': 'In /proc/stat',
        'core-cpu-time.now': 'Now',
        'core-cpu-time.what_it_means': 'What it means',
        'core-cpu-time.cores_unknown': 'cores unknown',
        'core-cpu-time.state_label.steal': 'Stolen by the host',
        'core-cpu-time.state_label.iowait': 'Waiting for storage',
        'core-cpu-time.state_label.softirq': 'Deferred interrupt work',
        'core-cpu-time.state_label.irq': 'Hardware interrupts',
        'core-cpu-time.state_label.system': 'Kernel, for a process',
        'core-cpu-time.state_label.user': 'Userland',
        'core-cpu-time.state_label.nice': 'Userland, low priority',
        'core-cpu-time.state_label.idle': 'Idle',
        'core-cpu-time.cores_count':
          '{{count}} core{{count, plural, one {} other {s}}}',
        'core-cpu-time.time_verdict_spread':
          'Percentages are of all {{cores}} together, so 100% would be the whole machine occupied.',
        'core-cpu-time.time_verdict_idle':
          'The machine is idle — {{busy}}% busy over the last reading. {{spread}}',
        'core-cpu-time.time_verdict_busy':
          '{{busy}}% busy, and most of it is {{state}} ({{percent}}%){{next}}. {{spread}}',
        'core-cpu-time.time_verdict_second': ', then {{state}} ({{percent}}%)',
        'core-cpu-time.time_verdict_state.user': 'ordinary work in userland',
        'core-cpu-time.time_verdict_state.nice': 'low-priority work',
        'core-cpu-time.time_verdict_state.system':
          'kernel work on behalf of processes',
        'core-cpu-time.time_verdict_state.iowait': 'waiting for storage',
        'core-cpu-time.time_verdict_state.steal':
          'time the hypervisor took away',
        'core-cpu-time.time_verdict_state.softirq': 'deferred interrupt work',
        'core-cpu-time.time_verdict_state.irq': 'hardware interrupts',
      }),
      es: Object.freeze({
        'core-cpu-time.average_over_range':
          '{{percent}}% de promedio en este intervalo',
        'core-cpu-time.state': 'Estado',
        'core-cpu-time.in_proc_stat': 'En /proc/stat',
        'core-cpu-time.now': 'Ahora',
        'core-cpu-time.what_it_means': 'Qué significa',
        'core-cpu-time.cores_unknown': 'núcleos desconocidos',
        'core-cpu-time.state_label.steal': 'Robado por el host',
        'core-cpu-time.state_label.iowait': 'Esperando almacenamiento',
        'core-cpu-time.state_label.softirq': 'Trabajo de interrupción diferida',
        'core-cpu-time.state_label.irq': 'Interrupciones de hardware',
        'core-cpu-time.state_label.system': 'Kernel, para un proceso',
        'core-cpu-time.state_label.user': 'Espacio de usuario',
        'core-cpu-time.state_label.nice': 'Espacio de usuario, baja prioridad',
        'core-cpu-time.state_label.idle': 'Inactivo',
        'core-cpu-time.cores_count':
          '{{count}} núcleo{{count, plural, one {} other {s}}}',
        'core-cpu-time.time_verdict_spread':
          'Los porcentajes son de {{cores}} en conjunto; 100% ocuparía toda la máquina.',
        'core-cpu-time.time_verdict_idle':
          'La máquina está inactiva: {{busy}}% ocupada en la última lectura. {{spread}}',
        'core-cpu-time.time_verdict_busy':
          '{{busy}}% ocupada, y la mayor parte es {{state}} ({{percent}}%){{next}}. {{spread}}',
        'core-cpu-time.time_verdict_second':
          ', después {{state}} ({{percent}}%)',
        'core-cpu-time.time_verdict_state.user': 'trabajo normal de usuario',
        'core-cpu-time.time_verdict_state.nice': 'trabajo de baja prioridad',
        'core-cpu-time.time_verdict_state.system':
          'trabajo del núcleo para procesos',
        'core-cpu-time.time_verdict_state.iowait': 'esperando almacenamiento',
        'core-cpu-time.time_verdict_state.steal':
          'tiempo tomado por el hipervisor',
        'core-cpu-time.time_verdict_state.softirq':
          'trabajo de interrupción diferida',
        'core-cpu-time.time_verdict_state.irq': 'interrupciones de hardware',
      }),
      hi: Object.freeze({
        'core-cpu-time.average_over_range': 'इस अवधि में औसतन {{percent}}%',
        'core-cpu-time.state': 'स्थिति',
        'core-cpu-time.in_proc_stat': '/proc/stat में',
        'core-cpu-time.now': 'अभी',
        'core-cpu-time.what_it_means': 'इसका अर्थ',
        'core-cpu-time.cores_unknown': 'कोर अज्ञात',
        'core-cpu-time.state_label.steal': 'होस्ट द्वारा लिया गया',
        'core-cpu-time.state_label.iowait': 'स्टोरेज की प्रतीक्षा',
        'core-cpu-time.state_label.softirq': 'स्थगित इंटरप्ट कार्य',
        'core-cpu-time.state_label.irq': 'हार्डवेयर इंटरप्ट',
        'core-cpu-time.state_label.system': 'प्रोसेस के लिए कर्नेल',
        'core-cpu-time.state_label.user': 'यूज़र स्पेस',
        'core-cpu-time.state_label.nice': 'यूज़र स्पेस, निम्न प्राथमिकता',
        'core-cpu-time.state_label.idle': 'निष्क्रिय',
        'core-cpu-time.cores_count': '{{count}} कोर',
        'core-cpu-time.time_verdict_spread':
          'प्रतिशत सभी {{cores}} का कुल है, इसलिए 100% पूरी मशीन व्यस्त है।',
        'core-cpu-time.time_verdict_idle':
          'मशीन निष्क्रिय है — पिछली रीडिंग में {{busy}}% व्यस्त। {{spread}}',
        'core-cpu-time.time_verdict_busy':
          '{{busy}}% व्यस्त है, और अधिकांश {{state}} ({{percent}}%) है{{next}}। {{spread}}',
        'core-cpu-time.time_verdict_second': ', फिर {{state}} ({{percent}}%)',
        'core-cpu-time.time_verdict_state.user': 'यूज़रस्पेस का सामान्य काम',
        'core-cpu-time.time_verdict_state.nice': 'कम प्राथमिकता का काम',
        'core-cpu-time.time_verdict_state.system': 'प्रोसेस के लिए कर्नेल काम',
        'core-cpu-time.time_verdict_state.iowait': 'स्टोरेज की प्रतीक्षा',
        'core-cpu-time.time_verdict_state.steal': 'हाइपरवाइज़र द्वारा लिया समय',
        'core-cpu-time.time_verdict_state.softirq': 'स्थगित इंटरप्ट काम',
        'core-cpu-time.time_verdict_state.irq': 'हार्डवेयर इंटरप्ट',
      }),
      ar: Object.freeze({
        'core-cpu-time.average_over_range':
          'متوسط {{percent}}% خلال هذا النطاق',
        'core-cpu-time.state': 'الحالة',
        'core-cpu-time.in_proc_stat': 'في /proc/stat',
        'core-cpu-time.now': 'الآن',
        'core-cpu-time.what_it_means': 'ما يعنيه',
        'core-cpu-time.cores_unknown': 'عدد النوى غير معروف',
        'core-cpu-time.state_label.steal': 'استولى عليه المضيف',
        'core-cpu-time.state_label.iowait': 'ينتظر التخزين',
        'core-cpu-time.state_label.softirq': 'عمل مقاطعة مؤجل',
        'core-cpu-time.state_label.irq': 'مقاطعات العتاد',
        'core-cpu-time.state_label.system': 'النواة، نيابة عن عملية',
        'core-cpu-time.state_label.user': 'مساحة المستخدم',
        'core-cpu-time.state_label.nice': 'مساحة المستخدم، أولوية منخفضة',
        'core-cpu-time.state_label.idle': 'خامل',
        'core-cpu-time.cores_count': '{{count}} نواة',
        'core-cpu-time.time_verdict_spread':
          'النسب تخص {{cores}} كلها معاً، لذا تعني 100٪ انشغال الآلة كاملة.',
        'core-cpu-time.time_verdict_idle':
          'الآلة خاملة — مشغولة بنسبة {{busy}}٪ في آخر قراءة. {{spread}}',
        'core-cpu-time.time_verdict_busy':
          'الآلة مشغولة بنسبة {{busy}}٪، ومعظم ذلك {{state}} ({{percent}}٪){{next}}. {{spread}}',
        'core-cpu-time.time_verdict_second': '، ثم {{state}} ({{percent}}٪)',
        'core-cpu-time.time_verdict_state.user': 'عمل عادي في مساحة المستخدم',
        'core-cpu-time.time_verdict_state.nice': 'عمل منخفض الأولوية',
        'core-cpu-time.time_verdict_state.system': 'عمل النواة للعمليات',
        'core-cpu-time.time_verdict_state.iowait': 'انتظار التخزين',
        'core-cpu-time.time_verdict_state.steal': 'وقت أخذه المشرف الافتراضي',
        'core-cpu-time.time_verdict_state.softirq': 'عمل مقاطعة مؤجل',
        'core-cpu-time.time_verdict_state.irq': 'مقاطعات العتاد',
      }),
      zh: Object.freeze({
        'core-cpu-time.average_over_range': '此范围内平均 {{percent}}%',
        'core-cpu-time.state': '状态',
        'core-cpu-time.in_proc_stat': '在 /proc/stat 中',
        'core-cpu-time.now': '现在',
        'core-cpu-time.what_it_means': '含义',
        'core-cpu-time.cores_unknown': '核心数未知',
        'core-cpu-time.state_label.steal': '被主机占用',
        'core-cpu-time.state_label.iowait': '正在等待存储',
        'core-cpu-time.state_label.softirq': '延迟中断处理',
        'core-cpu-time.state_label.irq': '硬件中断',
        'core-cpu-time.state_label.system': '内核（代表进程）',
        'core-cpu-time.state_label.user': '用户态',
        'core-cpu-time.state_label.nice': '低优先级用户态',
        'core-cpu-time.state_label.idle': '空闲',
        'core-cpu-time.cores_count': '{{count}} 个核心',
        'core-cpu-time.time_verdict_spread':
          '百分比是所有 {{cores}} 合计的结果，因此 100% 表示整台机器都被占用。',
        'core-cpu-time.time_verdict_idle':
          '机器处于空闲状态——上一次读数中忙碌 {{busy}}%。{{spread}}',
        'core-cpu-time.time_verdict_busy':
          '忙碌 {{busy}}%，其中大部分是{{state}}（{{percent}}%）{{next}}。{{spread}}',
        'core-cpu-time.time_verdict_second':
          '，其次是{{state}}（{{percent}}%）',
        'core-cpu-time.time_verdict_state.user': '用户空间中的普通工作',
        'core-cpu-time.time_verdict_state.nice': '低优先级工作',
        'core-cpu-time.time_verdict_state.system': '代表进程执行的内核工作',
        'core-cpu-time.time_verdict_state.iowait': '等待存储',
        'core-cpu-time.time_verdict_state.steal': '虚拟机监控程序占用的时间',
        'core-cpu-time.time_verdict_state.softirq': '延迟的中断工作',
        'core-cpu-time.time_verdict_state.irq': '硬件中断',
      }),
    }),
  }),
  render(input, helpers) {
    const { now, states, series, cores } = input;
    const {
      setCard,
      note,
      tableTarget,
      renderTable,
      bandOf,
      cpuStateMeaning,
      emptyMessage,
      i18n,
    } = helpers;
    const stateLabel = (meta) => {
      const key = String(meta?.short || '').trim();
      return key
        ? i18n.t(`core-cpu-time.state_label.${key}`, {}, meta.label)
        : meta.label;
    };
    const chartLabels = Object.fromEntries(
      Object.entries(states).map(([key, meta]) => [key, stateLabel(meta)]),
    );
    const bands = {
      steal: [5, 20],
      iowait: [15, 35],
      softirq: [10, 25],
      irq: [5, 15],
    };
    if (!now) {
      if (note) note.textContent = emptyMessage;
    } else {
      for (const [key, meta] of Object.entries(states)) {
        const value = Number(now[key]);
        const values = series
          .map((row) => Number(row[key]))
          .filter(Number.isFinite);
        const average = values.length
          ? values.reduce((sum, one) => sum + one, 0) / values.length
          : null;
        const band = bands[key]
          ? bandOf(value, bands[key][0], bands[key][1])
          : null;
        setCard(
          stateLabel(meta),
          Number.isFinite(value) ? `${value.toFixed(1)}%` : '—',
          average == null
            ? ''
            : i18n.t(
                'core-cpu-time.average_over_range',
                { percent: average.toFixed(1) },
                '{{percent}}% on average over this range',
              ),
          band === 'crit' ? 'crit' : band === 'warn' ? 'warn' : '',
        );
      }
    }
    if (now && note) note.textContent = cpuTimeVerdict(now, cores, i18n);
    if (!tableTarget) return { chartLabels };
    renderTable(
      tableTarget.id,
      Object.entries(states).map(([key, meta]) => ({
        key,
        label: stateLabel(meta),
        now: now ? Number(now[key]) : null,
        what: cpuStateMeaning(meta),
      })),
      [
        {
          label: i18n.t('core-cpu-time.state', {}, 'State'),
          render: (row) => row.label,
        },
        {
          label: i18n.t('core-cpu-time.in_proc_stat', {}, 'In /proc/stat'),
          className: 'cmd',
          render: (row) => row.key,
        },
        {
          label: i18n.t('core-cpu-time.now', {}, 'Now'),
          className: 'size',
          render: (row) =>
            row.now == null || !Number.isFinite(row.now)
              ? '—'
              : `${row.now.toFixed(1)}%`,
        },
        {
          label: i18n.t('core-cpu-time.what_it_means', {}, 'What it means'),
          render: (row) => row.what,
        },
      ],
    );
    return { chartLabels };
  },
};

function cpuTimeVerdict(now, cores, i18n) {
  const busy = Number(now.busy);
  const spread = i18n.t(
    'core-cpu-time.time_verdict_spread',
    {
      cores: cores
        ? i18n.t(
            'core-cpu-time.cores_count',
            { count: cores },
            '{{count}} cores',
          )
        : i18n.t('core-cpu-time.cores_unknown', {}, 'cores unknown'),
    },
    'Percentages are of all {{cores}} together, so 100% would be the whole machine occupied.',
  );
  if (busy < 5)
    return i18n.t(
      'core-cpu-time.time_verdict_idle',
      { busy: busy.toFixed(1), spread },
      'The machine is idle — {{busy}}% busy over the last reading. {{spread}}',
    );
  const parts = [
    ['user', 'ordinary work in userland'],
    ['nice', 'low-priority work'],
    ['system', 'kernel work on behalf of processes'],
    ['iowait', 'waiting for storage'],
    ['steal', 'time the hypervisor took away'],
    ['softirq', 'deferred interrupt work'],
    ['irq', 'hardware interrupts'],
  ].map(([key, fallback]) => [
    key,
    i18n.t(`core-cpu-time.time_verdict_state.${key}`, {}, fallback),
  ]);
  const ranked = parts
    .map(([key, words]) => [Number(now[key]) || 0, words])
    .sort((a, b) => b[0] - a[0]);
  const [top, topWords] = ranked[0];
  const [second, secondWords] = ranked[1];
  const next =
    second >= top / 3
      ? i18n.t(
          'core-cpu-time.time_verdict_second',
          { state: secondWords, percent: second.toFixed(1) },
          ', then {{state}} ({{percent}}%)',
        )
      : '';
  return i18n.t(
    'core-cpu-time.time_verdict_busy',
    {
      busy: busy.toFixed(0),
      state: topWords,
      percent: top.toFixed(1),
      next,
      spread,
    },
    '{{busy}}% busy, and most of it is {{state}} ({{percent}}%){{next}}. {{spread}}',
  );
}
