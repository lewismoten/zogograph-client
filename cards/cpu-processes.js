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
        'core-cpu-processes.help':
          'Processor use per process, read as root, so this is every account on the machine and not only this one. Percentages here are of {{core}}, the way {{top}} reports them — a process with four busy threads shows 400%, which is a fact about it rather than an error. That is the opposite convention to every other percentage on these pages, and the columns say so.',
        'core-cpu-processes.visibility_help':
          'A process is only here if it was alive for two consecutive readings or has burned real time since it started. Work that arrives and leaves inside one minute — a fork-per-request server, a tight cron loop — can hold a core permanently without a single one of its processes ever appearing in this table. If the machine is busy and this list is not, {{fork_rate}} is where that shows up.',
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
        'core-cpu-processes.help':
          'Uso de procesador por proceso, leído como root, así que incluye todas las cuentas de la máquina, no solo esta. Los porcentajes aquí son de {{core}}, como los informa {{top}}: un proceso con cuatro hilos ocupados muestra 400%, lo cual es un hecho y no un error. Es la convención opuesta a todos los demás porcentajes de estas páginas, y las columnas lo indican.',
        'core-cpu-processes.visibility_help':
          'Un proceso solo aparece aquí si estuvo vivo durante dos lecturas consecutivas o consumió tiempo real desde que comenzó. El trabajo que llega y se va en un minuto —un servidor que crea un proceso por solicitud, un cron ajustado— puede mantener un núcleo ocupado permanentemente sin que ninguno de sus procesos aparezca en esta tabla. Si la máquina está ocupada y esta lista no, {{fork_rate}} es donde se muestra.',
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
        'core-cpu-processes.help':
          'प्रति प्रोसेस प्रोसेसर उपयोग, root के रूप में पढ़ा गया है, इसलिए इसमें केवल यह नहीं बल्कि मशीन का हर खाता है। यहाँ प्रतिशत {{core}} के हैं, जिस तरह {{top}} उन्हें बताता है—चार व्यस्त थ्रेड वाला प्रोसेस 400% दिखाता है; यह उसके बारे में तथ्य है, त्रुटि नहीं। यह इन पृष्ठों के हर दूसरे प्रतिशत की उलटी परंपरा है और कॉलम ऐसा बताते हैं।',
        'core-cpu-processes.visibility_help':
          'कोई प्रोसेस केवल तभी यहाँ है यदि वह लगातार दो रीडिंग तक जीवित था या शुरू होने के बाद से वास्तविक समय जला चुका है। एक मिनट के अंदर आने-जाने वाला काम—हर अनुरोध पर fork करने वाला सर्वर, तंग cron लूप—किसी एक प्रोसेस के इस तालिका में आए बिना भी कोर को स्थायी रूप से व्यस्त रख सकता है। मशीन व्यस्त हो और यह सूची न हो, तो {{fork_rate}} में वह दिखता है।',
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
        'core-cpu-processes.help':
          'استخدام المعالج لكل عملية، مقروءًا بصلاحيات root، لذا يشمل كل حساب على الآلة لا هذا الحساب فقط. النسب هنا هي من {{core}}، بالطريقة التي يعرضها {{top}} — فالعملية ذات أربعة خيوط مشغولة تعرض 400%، وهذه حقيقة عنها لا خطأ. هذا عكس الاصطلاح في كل النسب الأخرى في هذه الصفحات، وتوضح الأعمدة ذلك.',
        'core-cpu-processes.visibility_help':
          'لا تظهر عملية هنا إلا إذا كانت حية في قراءتين متتاليتين أو استهلكت وقتًا فعليًا منذ بدايتها. العمل الذي يصل ويغادر خلال دقيقة — خادم يفرّع لكل طلب أو حلقة cron ضيقة — قد يشغل نواة باستمرار من دون أن تظهر أي من عملياته في هذا الجدول. إذا كانت الآلة مشغولة وليست هذه القائمة، فـ{{fork_rate}} هو المكان الذي يظهر فيه ذلك.',
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
        'core-cpu-processes.help':
          '按进程读取的处理器使用量，以 root 身份读取，因此这里显示机器上的所有账户，不仅是此账户。这里的百分比以 {{core}} 为单位，和 {{top}} 的报告方式一样——一个有四个忙碌线程的进程会显示 400%，这是事实而非错误。这与这些页面上所有其他百分比的惯例相反，列标题会说明这一点。',
        'core-cpu-processes.visibility_help':
          '一个进程只有在连续两次读数中都存活，或自启动后消耗过实际时间时才会出现在这里。在一分钟内到来又离开的工作——每个请求 fork 一次的服务器、紧密的 cron 循环——即使其任何进程从未出现在此表中，也能持续占用一个核心。如果机器很忙而此列表没有内容，请看 {{fork_rate}}。',
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
