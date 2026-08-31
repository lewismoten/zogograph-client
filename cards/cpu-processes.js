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
        'core-cpu-processes.empty':
          'No process has used measurable processor time between two readings.',
        'core-cpu-processes.summary':
          '{{processes, number}} processes using the processor, adding up to {{percent, number}}% of one core{{machine}}{{busy}}.',
        'core-cpu-processes.machine':
          ' — about {{percent, number}}% of the machine',
        'core-cpu-processes.busy':
          ', against {{percent, number}}% measured across all cores',
        'core-cpu-processes.state.R': 'running',
        'core-cpu-processes.state.S': 'sleeping',
        'core-cpu-processes.state.D': 'waiting on I/O',
        'core-cpu-processes.state.Z': 'zombie',
        'core-cpu-processes.state.T': 'stopped',
        'core-cpu-processes.state.I': 'idle kernel thread',
      }),
      es: Object.freeze({
        'core-cpu-processes.empty':
          'Ningún proceso ha usado tiempo de procesador medible entre dos lecturas.',
        'core-cpu-processes.summary':
          '{{processes, number}} procesos usan el procesador y suman {{percent, number}}% de un núcleo{{machine}}{{busy}}.',
        'core-cpu-processes.machine':
          ' — cerca de {{percent, number}}% de la máquina',
        'core-cpu-processes.busy':
          ', frente a {{percent, number}}% medido en todos los núcleos',
        'core-cpu-processes.state.R': 'en ejecución',
        'core-cpu-processes.state.S': 'en espera',
        'core-cpu-processes.state.D': 'esperando E/S',
        'core-cpu-processes.state.Z': 'zombi',
        'core-cpu-processes.state.T': 'detenido',
        'core-cpu-processes.state.I': 'hilo de kernel inactivo',
      }),
      hi: Object.freeze({
        'core-cpu-processes.empty':
          'दो रीडिंग के बीच किसी प्रक्रिया ने मापने योग्य प्रोसेसर समय उपयोग नहीं किया।',
        'core-cpu-processes.summary':
          '{{processes, number}} प्रक्रियाएँ प्रोसेसर उपयोग कर रही हैं, कुल एक कोर का {{percent, number}}%{{machine}}{{busy}}।',
        'core-cpu-processes.machine': ' — मशीन का लगभग {{percent, number}}%',
        'core-cpu-processes.busy':
          ', सभी कोर पर मापे गए {{percent, number}}% के विरुद्ध',
        'core-cpu-processes.state.R': 'चल रहा',
        'core-cpu-processes.state.S': 'प्रतीक्षा में',
        'core-cpu-processes.state.D': 'I/O की प्रतीक्षा',
        'core-cpu-processes.state.Z': 'ज़ॉम्बी',
        'core-cpu-processes.state.T': 'रुका हुआ',
        'core-cpu-processes.state.I': 'निष्क्रिय कर्नेल थ्रेड',
      }),
      ar: Object.freeze({
        'core-cpu-processes.empty':
          'لم تستخدم أي عملية وقت معالجة قابلًا للقياس بين قراءتين.',
        'core-cpu-processes.summary':
          'تستخدم {{processes, number}} عمليات المعالج، بمجموع {{percent, number}}% من نواة واحدة{{machine}}{{busy}}.',
        'core-cpu-processes.machine': ' — نحو {{percent, number}}% من الجهاز',
        'core-cpu-processes.busy':
          '، مقابل {{percent, number}}% مقاسة عبر جميع النوى',
        'core-cpu-processes.state.R': 'قيد التشغيل',
        'core-cpu-processes.state.S': 'نائم',
        'core-cpu-processes.state.D': 'ينتظر إدخال/إخراج',
        'core-cpu-processes.state.Z': 'زومبي',
        'core-cpu-processes.state.T': 'متوقف',
        'core-cpu-processes.state.I': 'خيط نواة خامل',
      }),
      zh: Object.freeze({
        'core-cpu-processes.empty':
          '两个读数之间没有进程使用可测量的处理器时间。',
        'core-cpu-processes.summary':
          '{{processes, number}} 个进程正在使用处理器，共占一个核心的 {{percent, number}}%{{machine}}{{busy}}。',
        'core-cpu-processes.machine': ' — 约占整台机器的 {{percent, number}}%',
        'core-cpu-processes.busy':
          '，而所有核心的测量值为 {{percent, number}}%',
        'core-cpu-processes.state.R': '运行中',
        'core-cpu-processes.state.S': '休眠',
        'core-cpu-processes.state.D': '等待 I/O',
        'core-cpu-processes.state.Z': '僵尸',
        'core-cpu-processes.state.T': '已停止',
        'core-cpu-processes.state.I': '空闲内核线程',
      }),
    }),
  }),
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
    const cpu = (key, values, fallback) =>
      i18n.t(`cpu.${key}`, values, fallback);

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
        label: cpu('pid', {}, 'PID'),
        className: 'size',
        render: (row) => row.pid,
      },
      { label: cpu('user', {}, 'User'), render: (row) => userCell(row.user) },
      {
        label: cpu('process', {}, 'Process'),
        render: (row) => namedThing('process', row.name),
      },
      {
        label: cpu('now_one_core', {}, 'CPU now (of one core)'),
        className: 'size',
        render: (row) =>
          row.cpu_percent == null
            ? '—'
            : `${Number(row.cpu_percent).toFixed(1)}%`,
      },
      {
        label: cpu('average_since_started', {}, 'Average since it started'),
        className: 'size',
        render: (row) =>
          row.cpu_lifetime_percent == null
            ? '—'
            : `${Number(row.cpu_lifetime_percent).toFixed(1)}%`,
      },
      { label: cpu('state', {}, 'State'), render: (row) => state(row.state) },
      {
        label: cpu('threads', {}, 'Threads'),
        className: 'size',
        render: (row) => row.threads ?? '—',
      },
      {
        label: cpu('running_for', {}, 'Running for'),
        className: 'size',
        render: (row) =>
          row.elapsed_seconds == null
            ? '—'
            : humanDuration(row.elapsed_seconds),
      },
      {
        label: cpu('resident', {}, 'Resident'),
        className: 'size',
        render: (row) => humanBytes(row.rss_bytes),
      },
      {
        label: cpu('command', {}, 'Command'),
        className: 'cmd',
        render: (row) => row.command,
      },
    ]);
  },
};
