/* Core card package: processor use by process. */
window.ZogographCpuProcessesCard = {
  manifest: Object.freeze({
    id: 'core-cpu-processes',
    locales: ['core-cpu-processes.'],
    helpers: [
      'noteTarget',
      'tableTarget',
      'renderTable',
      'userCell',
      'namedThing',
      'humanDuration',
      'humanBytes',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-processes.title': 'What is burning it',
        'core-cpu-processes.empty':
          'No process has used measurable processor time between two readings.',
        'core-cpu-processes.summary':
          '{{processes, number}} processes using the processor, adding up to {{percent, number}}% of one core{{machine}}{{busy}}.',
        'core-cpu-processes.machine':
          ' — about {{percent, number}}% of the machine',
        'core-cpu-processes.busy':
          ', against {{percent, number}}% measured across all cores',
        'core-cpu-processes.state.R': 'running',
        'core-cpu-processes.pid': 'PID',
        'core-cpu-processes.user': 'User',
        'core-cpu-processes.process': 'Process',
        'core-cpu-processes.now_one_core': 'CPU now (of one core)',
        'core-cpu-processes.average_since_started': 'Average since it started',
        'core-cpu-processes.state': 'State',
        'core-cpu-processes.threads': 'Threads',
        'core-cpu-processes.running_for': 'Running for',
        'core-cpu-processes.resident': 'Resident',
        'core-cpu-processes.command': 'Command',
        'core-cpu-processes.state.S': 'sleeping',
        'core-cpu-processes.state.D': 'waiting on I/O',
        'core-cpu-processes.state.Z': 'zombie',
        'core-cpu-processes.state.T': 'stopped',
        'core-cpu-processes.state.I': 'idle kernel thread',
      }),
      es: Object.freeze({
        'core-cpu-processes.title': 'Qué lo está consumiendo',
        'core-cpu-processes.empty':
          'Ningún proceso ha usado tiempo de procesador medible entre dos lecturas.',
        'core-cpu-processes.summary':
          '{{processes, number}} procesos usan el procesador y suman {{percent, number}}% de un núcleo{{machine}}{{busy}}.',
        'core-cpu-processes.machine':
          ' — cerca de {{percent, number}}% de la máquina',
        'core-cpu-processes.busy':
          ', frente a {{percent, number}}% medido en todos los núcleos',
        'core-cpu-processes.state.R': 'en ejecución',
        'core-cpu-processes.pid': 'PID',
        'core-cpu-processes.user': 'Usuario',
        'core-cpu-processes.process': 'Proceso',
        'core-cpu-processes.now_one_core': 'CPU actual (de un núcleo)',
        'core-cpu-processes.average_since_started': 'Promedio desde que inició',
        'core-cpu-processes.state': 'Estado',
        'core-cpu-processes.threads': 'Hilos',
        'core-cpu-processes.running_for': 'En ejecución desde hace',
        'core-cpu-processes.resident': 'Residente',
        'core-cpu-processes.command': 'Comando',
        'core-cpu-processes.state.S': 'en espera',
        'core-cpu-processes.state.D': 'esperando E/S',
        'core-cpu-processes.state.Z': 'zombi',
        'core-cpu-processes.state.T': 'detenido',
        'core-cpu-processes.state.I': 'hilo de kernel inactivo',
      }),
      hi: Object.freeze({
        'core-cpu-processes.title': 'इसे कौन जला रहा है',
        'core-cpu-processes.empty':
          'दो रीडिंग के बीच किसी प्रक्रिया ने मापने योग्य प्रोसेसर समय उपयोग नहीं किया।',
        'core-cpu-processes.summary':
          '{{processes, number}} प्रक्रियाएँ प्रोसेसर उपयोग कर रही हैं, कुल एक कोर का {{percent, number}}%{{machine}}{{busy}}।',
        'core-cpu-processes.machine': ' — मशीन का लगभग {{percent, number}}%',
        'core-cpu-processes.busy':
          ', सभी कोर पर मापे गए {{percent, number}}% के विरुद्ध',
        'core-cpu-processes.state.R': 'चल रहा',
        'core-cpu-processes.pid': 'PID',
        'core-cpu-processes.user': 'उपयोगकर्ता',
        'core-cpu-processes.process': 'प्रक्रिया',
        'core-cpu-processes.now_one_core': 'वर्तमान CPU (एक कोर का)',
        'core-cpu-processes.average_since_started': 'शुरू होने के बाद का औसत',
        'core-cpu-processes.state': 'स्थिति',
        'core-cpu-processes.threads': 'थ्रेड',
        'core-cpu-processes.running_for': 'चलने की अवधि',
        'core-cpu-processes.resident': 'रेज़िडेंट',
        'core-cpu-processes.command': 'कमांड',
        'core-cpu-processes.state.S': 'प्रतीक्षा में',
        'core-cpu-processes.state.D': 'I/O की प्रतीक्षा',
        'core-cpu-processes.state.Z': 'ज़ॉम्बी',
        'core-cpu-processes.state.T': 'रुका हुआ',
        'core-cpu-processes.state.I': 'निष्क्रिय कर्नेल थ्रेड',
      }),
      ar: Object.freeze({
        'core-cpu-processes.title': 'ما الذي يحرقه',
        'core-cpu-processes.empty':
          'لم تستخدم أي عملية وقت معالجة قابلًا للقياس بين قراءتين.',
        'core-cpu-processes.summary':
          'تستخدم {{processes, number}} عمليات المعالج، بمجموع {{percent, number}}% من نواة واحدة{{machine}}{{busy}}.',
        'core-cpu-processes.machine': ' — نحو {{percent, number}}% من الجهاز',
        'core-cpu-processes.busy':
          '، مقابل {{percent, number}}% مقاسة عبر جميع النوى',
        'core-cpu-processes.state.R': 'قيد التشغيل',
        'core-cpu-processes.pid': 'PID',
        'core-cpu-processes.user': 'المستخدم',
        'core-cpu-processes.process': 'العملية',
        'core-cpu-processes.now_one_core': 'المعالج الآن (من نواة واحدة)',
        'core-cpu-processes.average_since_started': 'المتوسط منذ البدء',
        'core-cpu-processes.state': 'الحالة',
        'core-cpu-processes.threads': 'الخيوط',
        'core-cpu-processes.running_for': 'مدة التشغيل',
        'core-cpu-processes.resident': 'المقيم',
        'core-cpu-processes.command': 'الأمر',
        'core-cpu-processes.state.S': 'نائم',
        'core-cpu-processes.state.D': 'ينتظر إدخال/إخراج',
        'core-cpu-processes.state.Z': 'زومبي',
        'core-cpu-processes.state.T': 'متوقف',
        'core-cpu-processes.state.I': 'خيط نواة خامل',
      }),
      zh: Object.freeze({
        'core-cpu-processes.title': '是什么在消耗它',
        'core-cpu-processes.empty':
          '两个读数之间没有进程使用可测量的处理器时间。',
        'core-cpu-processes.summary':
          '{{processes, number}} 个进程正在使用处理器，共占一个核心的 {{percent, number}}%{{machine}}{{busy}}。',
        'core-cpu-processes.machine': ' — 约占整台机器的 {{percent, number}}%',
        'core-cpu-processes.busy':
          '，而所有核心的测量值为 {{percent, number}}%',
        'core-cpu-processes.state.R': '运行中',
        'core-cpu-processes.pid': 'PID',
        'core-cpu-processes.user': '用户',
        'core-cpu-processes.process': '进程',
        'core-cpu-processes.now_one_core': '当前 CPU（一个核心）',
        'core-cpu-processes.average_since_started': '自启动以来平均值',
        'core-cpu-processes.state': '状态',
        'core-cpu-processes.threads': '线程',
        'core-cpu-processes.running_for': '已运行',
        'core-cpu-processes.resident': '常驻内存',
        'core-cpu-processes.command': '命令',
        'core-cpu-processes.state.S': '休眠',
        'core-cpu-processes.state.D': '等待 I/O',
        'core-cpu-processes.state.Z': '僵尸',
        'core-cpu-processes.state.T': '已停止',
        'core-cpu-processes.state.I': '空闲内核线程',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cpu-processes.${key}`,
      {},
      this.manifest.catalogs.en[`core-cpu-processes.${key}`],
    );
  },
  render(data, helpers) {
    const {
      noteTarget,
      tableTarget,
      renderTable,
      userCell,
      namedThing,
      humanDuration,
      humanBytes,
      i18n,
    } = helpers;
    if (!noteTarget || !tableTarget) return;
    const rows = data.processes ?? [];
    const cores = Number(data.sample?.cpu_cores) || 0;
    const accounted = rows.reduce(
      (sum, row) => sum + (Number(row.cpu_percent) || 0),
      0,
    );
    const busy = data.now ? Number(data.now.busy) : null;
    const t = (key, values, fallback) =>
      i18n.t(`core-cpu-processes.${key}`, values, fallback);

    noteTarget.textContent = !rows.length
      ? t(
          'empty',
          {},
          'No process has used measurable processor time between two readings.',
        )
      : t(
          'summary',
          {
            processes: rows.length,
            percent: Math.round(accounted),
            machine: cores
              ? t(
                  'machine',
                  { percent: Math.round(accounted / cores) },
                  ' — about {{percent, number}}% of the machine',
                )
              : '',
            busy:
              busy == null
                ? ''
                : t(
                    'busy',
                    { percent: Math.round(busy) },
                    ', against {{percent, number}}% measured across all cores',
                  ),
          },
          '{{processes, number}} processes using the processor, adding up to {{percent, number}}% of one core{{machine}}{{busy}}.',
        );

    const stateWords = {
      R: 'running',
      S: 'sleeping',
      D: 'waiting on I/O',
      Z: 'zombie',
      T: 'stopped',
      I: 'idle kernel thread',
    };
    const state = (value) =>
      stateWords[value]
        ? t(`state.${value}`, {}, stateWords[value])
        : value || '—';
    renderTable(tableTarget, rows, [
      {
        label: t('pid', {}, 'PID'),
        className: 'size',
        render: (row) => row.pid,
      },
      { label: t('user', {}, 'User'), render: (row) => userCell(row.user) },
      {
        label: t('process', {}, 'Process'),
        render: (row) => namedThing('process', row.name),
      },
      {
        label: t('now_one_core', {}, 'CPU now (of one core)'),
        className: 'size',
        render: (row) =>
          row.cpu_percent == null
            ? '—'
            : `${Number(row.cpu_percent).toFixed(1)}%`,
      },
      {
        label: t('average_since_started', {}, 'Average since it started'),
        className: 'size',
        render: (row) =>
          row.cpu_lifetime_percent == null
            ? '—'
            : `${Number(row.cpu_lifetime_percent).toFixed(1)}%`,
      },
      { label: t('state', {}, 'State'), render: (row) => state(row.state) },
      {
        label: t('threads', {}, 'Threads'),
        className: 'size',
        render: (row) => row.threads ?? '—',
      },
      {
        label: t('running_for', {}, 'Running for'),
        className: 'size',
        render: (row) =>
          row.elapsed_seconds == null
            ? '—'
            : humanDuration(row.elapsed_seconds),
      },
      {
        label: t('resident', {}, 'Resident'),
        className: 'size',
        render: (row) => humanBytes(row.rss_bytes),
      },
      {
        label: t('command', {}, 'Command'),
        className: 'cmd',
        render: (row) => row.command,
      },
    ]);
  },
};
