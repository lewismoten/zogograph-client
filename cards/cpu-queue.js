/* Core card package: only declared dependencies are received from the shell. */
window.ZogographCpuQueueCard = {
  manifest: Object.freeze({
    id: 'core-cpu-queue',
    locales: ['core-cpu-queue.'],
    helpers: ['setCard', 'humanCount', 'count', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-queue.wanting_to_run': 'Wanting to run',
        'core-cpu-queue.across_cores': 'across {{count, number}} cores',
        'core-cpu-queue.cores_unknown': 'cores unknown',
        'core-cpu-queue.blocked_in_kernel': 'Blocked in the kernel',
        'core-cpu-queue.uninterruptible_storage':
          'uninterruptible — waiting on storage',
        'core-cpu-queue.load_one_minute': 'Load, one minute',
        'core-cpu-queue.queue_not_cpu': 'a queue length, not a CPU reading',
        'core-cpu-queue.waiting_for_core': 'Waiting for a core',
        'core-cpu-queue.no_pressure_stall':
          'no pressure stall info on this kernel',
        'core-cpu-queue.last_ten_seconds': 'share of the last ten seconds',
        'core-cpu-queue.context_switches': 'Context switches',
        'core-cpu-queue.scheduler_change': 'the scheduler changing its mind',
        'core-cpu-queue.interrupts': 'Interrupts',
        'core-cpu-queue.devices_attention': 'devices asking for attention',
        'core-cpu-queue.new_processes': 'New processes',
        'core-cpu-queue.forks_execs_second': 'forks and execs per second',
        'core-cpu-queue.blocked_on_io': 'Blocked on I/O',
        'core-cpu-queue.waiting_for_core_percent': 'Waiting for a core (%)',
        'core-cpu-queue.context_switches_second': 'Context switches/s',
        'core-cpu-queue.interrupts_second': 'Interrupts/s',
        'core-cpu-queue.new_processes_second': 'New processes/s',
        'core-cpu-queue.queue_verdict_waiting':
          'Nothing to compare yet — the queue is read from two consecutive collections.',
        'core-cpu-queue.queue_verdict_storage':
          'Load is {{load}} on {{cores}}, but only {{running}} task(s) want a core and {{blocked}} are blocked in the kernel. That load is storage, not processor: blocked tasks count towards it and use no CPU at all. A faster processor would change nothing here.',
        'core-cpu-queue.queue_verdict_runnable':
          '{{running}} runnable across {{cores}}. Nothing is stalled — everything gets a turn — but each turn is shorter, and every job takes longer than it would on an idle machine. This is what “slow but nothing is broken” looks like from underneath.',
        'core-cpu-queue.queue_verdict_blocked':
          '{{blocked}} in uninterruptible sleep. They are waiting on I/O, cannot be killed while they wait, and are inflating the load average without touching the processor.',
        'core-cpu-queue.queue_verdict_clear':
          'Nothing is queueing: {{running}} tasks runnable {{cores}}, {{blocked}} blocked.',
        'core-cpu-queue.queue_unknown_count': 'an unknown number of',
      }),
      es: Object.freeze({
        'core-cpu-queue.wanting_to_run': 'Esperando ejecutar',
        'core-cpu-queue.across_cores': 'en {{count, number}} núcleos',
        'core-cpu-queue.cores_unknown': 'núcleos desconocidos',
        'core-cpu-queue.blocked_in_kernel': 'Bloqueado en el kernel',
        'core-cpu-queue.uninterruptible_storage':
          'no interrumpible — esperando almacenamiento',
        'core-cpu-queue.load_one_minute': 'Carga, un minuto',
        'core-cpu-queue.queue_not_cpu': 'longitud de cola, no lectura de CPU',
        'core-cpu-queue.waiting_for_core': 'Esperando un núcleo',
        'core-cpu-queue.no_pressure_stall':
          'no hay información de presión en este kernel',
        'core-cpu-queue.last_ten_seconds':
          'proporción de los últimos diez segundos',
        'core-cpu-queue.context_switches': 'Cambios de contexto',
        'core-cpu-queue.scheduler_change':
          'el planificador cambiando de opinión',
        'core-cpu-queue.interrupts': 'Interrupciones',
        'core-cpu-queue.devices_attention': 'dispositivos pidiendo atención',
        'core-cpu-queue.new_processes': 'Procesos nuevos',
        'core-cpu-queue.forks_execs_second': 'forks y execs por segundo',
        'core-cpu-queue.blocked_on_io': 'Bloqueado en E/S',
        'core-cpu-queue.waiting_for_core_percent': 'Esperando un núcleo (%)',
        'core-cpu-queue.context_switches_second': 'Cambios de contexto/s',
        'core-cpu-queue.interrupts_second': 'Interrupciones/s',
        'core-cpu-queue.new_processes_second': 'Procesos nuevos/s',
        'core-cpu-queue.queue_verdict_waiting':
          'Aún no hay nada que comparar: la cola se lee de dos recopilaciones consecutivas.',
        'core-cpu-queue.queue_verdict_storage':
          'La carga es {{load}} en {{cores}}, pero solo {{running}} tarea(s) quieren un núcleo y {{blocked}} están bloqueadas en el kernel. Esa carga es almacenamiento, no procesador.',
        'core-cpu-queue.queue_verdict_runnable':
          '{{running}} ejecutables en {{cores}}. Nada está detenido, pero cada turno es más corto y cada trabajo tarda más.',
        'core-cpu-queue.queue_verdict_blocked':
          '{{blocked}} en suspensión ininterrumpible. Esperan E/S y aumentan la carga sin tocar el procesador.',
        'core-cpu-queue.queue_verdict_clear':
          'Nada está haciendo cola: {{running}} tareas ejecutables {{cores}}, {{blocked}} bloqueadas.',
        'core-cpu-queue.queue_unknown_count': 'un número desconocido de',
      }),
      hi: Object.freeze({
        'core-cpu-queue.wanting_to_run': 'चलने की प्रतीक्षा',
        'core-cpu-queue.across_cores': '{{count, number}} कोर में',
        'core-cpu-queue.cores_unknown': 'कोर अज्ञात',
        'core-cpu-queue.blocked_in_kernel': 'कर्नेल में अवरुद्ध',
        'core-cpu-queue.uninterruptible_storage':
          'बाधित न किया जा सकने वाला — स्टोरेज की प्रतीक्षा',
        'core-cpu-queue.load_one_minute': 'लोड, एक मिनट',
        'core-cpu-queue.queue_not_cpu': 'कतार की लंबाई, CPU रीडिंग नहीं',
        'core-cpu-queue.waiting_for_core': 'कोर की प्रतीक्षा',
        'core-cpu-queue.no_pressure_stall':
          'इस कर्नेल में दबाव-रुकावट जानकारी नहीं',
        'core-cpu-queue.last_ten_seconds': 'पिछले दस सेकंड का हिस्सा',
        'core-cpu-queue.context_switches': 'कॉन्टेक्स्ट स्विच',
        'core-cpu-queue.scheduler_change': 'शेड्यूलर का निर्णय बदलना',
        'core-cpu-queue.interrupts': 'इंटरप्ट',
        'core-cpu-queue.devices_attention': 'उपकरण ध्यान माँग रहे हैं',
        'core-cpu-queue.new_processes': 'नई प्रक्रियाएँ',
        'core-cpu-queue.forks_execs_second': 'प्रति सेकंड forks और execs',
        'core-cpu-queue.blocked_on_io': 'I/O पर अवरुद्ध',
        'core-cpu-queue.waiting_for_core_percent': 'कोर की प्रतीक्षा (%)',
        'core-cpu-queue.context_switches_second': 'कॉन्टेक्स्ट स्विच/से',
        'core-cpu-queue.interrupts_second': 'इंटरप्ट/से',
        'core-cpu-queue.new_processes_second': 'नई प्रक्रियाएँ/से',
        'core-cpu-queue.queue_verdict_waiting':
          'तुलना के लिए अभी कुछ नहीं है — कतार लगातार दो संग्रहों से पढ़ी जाती है।',
        'core-cpu-queue.queue_verdict_storage':
          'लोड {{cores}} पर {{load}} है, लेकिन केवल {{running}} कार्य को कोर चाहिए और {{blocked}} कर्नेल में ब्लॉक हैं। यह स्टोरेज लोड है, प्रोसेसर नहीं।',
        'core-cpu-queue.queue_verdict_runnable':
          '{{cores}} में {{running}} चलने योग्य हैं। कुछ अटका नहीं है, पर हर बारी छोटी है और हर काम अधिक समय लेता है।',
        'core-cpu-queue.queue_verdict_blocked':
          '{{blocked}} I/O की प्रतीक्षा कर रहे हैं और CPU छुए बिना लोड बढ़ा रहे हैं।',
        'core-cpu-queue.queue_verdict_clear':
          'कुछ कतार में नहीं है: {{running}} कार्य चलने योग्य {{cores}}, {{blocked}} ब्लॉक हैं।',
        'core-cpu-queue.queue_unknown_count': 'अज्ञात संख्या में',
      }),
      ar: Object.freeze({
        'core-cpu-queue.wanting_to_run': 'يريد التشغيل',
        'core-cpu-queue.across_cores': 'عبر {{count, number}} نوى',
        'core-cpu-queue.cores_unknown': 'عدد النوى غير معروف',
        'core-cpu-queue.blocked_in_kernel': 'محجوب في النواة',
        'core-cpu-queue.uninterruptible_storage':
          'غير قابل للمقاطعة — ينتظر التخزين',
        'core-cpu-queue.load_one_minute': 'الحمل، دقيقة واحدة',
        'core-cpu-queue.queue_not_cpu': 'طول قائمة انتظار، لا قراءة معالج',
        'core-cpu-queue.waiting_for_core': 'ينتظر نواة',
        'core-cpu-queue.no_pressure_stall':
          'لا توجد معلومات توقف الضغط في هذه النواة',
        'core-cpu-queue.last_ten_seconds': 'حصة آخر عشر ثوانٍ',
        'core-cpu-queue.context_switches': 'تبديلات السياق',
        'core-cpu-queue.scheduler_change': 'المجدول يغير رأيه',
        'core-cpu-queue.interrupts': 'المقاطعات',
        'core-cpu-queue.devices_attention': 'أجهزة تطلب الانتباه',
        'core-cpu-queue.new_processes': 'عمليات جديدة',
        'core-cpu-queue.forks_execs_second': 'fork وexec في الثانية',
        'core-cpu-queue.blocked_on_io': 'محجوب على إدخال/إخراج',
        'core-cpu-queue.waiting_for_core_percent': 'ينتظر نواة (%)',
        'core-cpu-queue.context_switches_second': 'تبديلات السياق/ث',
        'core-cpu-queue.interrupts_second': 'مقاطعات/ث',
        'core-cpu-queue.new_processes_second': 'عمليات جديدة/ث',
        'core-cpu-queue.queue_verdict_waiting':
          'لا شيء للمقارنة بعد — تُقرأ القائمة من مجموعتين متتاليتين.',
        'core-cpu-queue.queue_verdict_storage':
          'الحمل {{load}} على {{cores}}، لكن {{running}} مهمة فقط تريد نواة و{{blocked}} محجوبة في النواة. هذا حمل تخزين لا معالج.',
        'core-cpu-queue.queue_verdict_runnable':
          '{{running}} جاهزة للتشغيل عبر {{cores}}. لا شيء متعطل، لكن كل دور أقصر وكل عمل يستغرق أطول.',
        'core-cpu-queue.queue_verdict_blocked':
          '{{blocked}} تنتظر الإدخال/الإخراج وتزيد الحمل من دون لمس المعالج.',
        'core-cpu-queue.queue_verdict_clear':
          'لا شيء في الطابور: {{running}} مهمة قابلة للتشغيل {{cores}}، و{{blocked}} محجوبة.',
        'core-cpu-queue.queue_unknown_count': 'عدد غير معروف من',
      }),
      zh: Object.freeze({
        'core-cpu-queue.wanting_to_run': '等待运行',
        'core-cpu-queue.across_cores': '跨 {{count, number}} 个核心',
        'core-cpu-queue.cores_unknown': '核心数未知',
        'core-cpu-queue.blocked_in_kernel': '内核中阻塞',
        'core-cpu-queue.uninterruptible_storage': '不可中断 — 正在等待存储',
        'core-cpu-queue.load_one_minute': '一分钟负载',
        'core-cpu-queue.queue_not_cpu': '队列长度，不是 CPU 读数',
        'core-cpu-queue.waiting_for_core': '等待核心',
        'core-cpu-queue.no_pressure_stall': '该内核没有压力停顿信息',
        'core-cpu-queue.last_ten_seconds': '最近十秒的占比',
        'core-cpu-queue.context_switches': '上下文切换',
        'core-cpu-queue.scheduler_change': '调度程序在改变主意',
        'core-cpu-queue.interrupts': '中断',
        'core-cpu-queue.devices_attention': '设备请求关注',
        'core-cpu-queue.new_processes': '新进程',
        'core-cpu-queue.forks_execs_second': '每秒 fork 和 exec',
        'core-cpu-queue.blocked_on_io': '在 I/O 上阻塞',
        'core-cpu-queue.waiting_for_core_percent': '等待核心 (%)',
        'core-cpu-queue.context_switches_second': '上下文切换/秒',
        'core-cpu-queue.interrupts_second': '中断/秒',
        'core-cpu-queue.new_processes_second': '新进程/秒',
        'core-cpu-queue.queue_verdict_waiting':
          '尚无内容可比较——队列由连续两次采集读取。',
        'core-cpu-queue.queue_verdict_storage':
          '{{cores}} 上的负载为 {{load}}，但只有 {{running}} 个任务想要核心，{{blocked}} 个在内核中阻塞。这是存储负载而不是处理器负载。',
        'core-cpu-queue.queue_verdict_runnable':
          '{{cores}} 上有 {{running}} 个可运行任务。没有任何任务停滞，但每次轮转更短，每项工作都更久。',
        'core-cpu-queue.queue_verdict_blocked':
          '{{blocked}} 个正在等待 I/O，并且在不使用处理器的情况下抬高负载。',
        'core-cpu-queue.queue_verdict_clear':
          '没有排队：{{running}} 个可运行任务 {{cores}}，{{blocked}} 个被阻塞。',
        'core-cpu-queue.queue_unknown_count': '未知数量的',
      }),
    }),
  }),
  render(input, helpers) {
    const { now, sample } = input;
    const { setCard, humanCount, count, i18n } = helpers;
    const t = (key, values, fallback) =>
      i18n.t(`core-cpu-queue.${key}`, values, fallback);
    const cores = Number(sample.cpu_cores) || 0;
    const running = now?.procs_running ?? null;
    const blocked = now?.procs_blocked ?? null;
    const load = sample.load1 == null ? null : Number(sample.load1);

    setCard(
      t('wanting_to_run', {}, 'Wanting to run'),
      running == null ? '—' : String(running),
      cores
        ? t('across_cores', { count: cores }, 'across {{count, number}} cores')
        : t('cores_unknown', {}, 'cores unknown'),
      running != null && cores && running > cores * 2 ? 'warn' : '',
    );
    setCard(
      t('blocked_in_kernel', {}, 'Blocked in the kernel'),
      blocked == null ? '—' : String(blocked),
      t('uninterruptible_storage', {}, 'uninterruptible — waiting on storage'),
      blocked != null && blocked >= 4 ? 'warn' : '',
    );
    setCard(
      t('load_one_minute', {}, 'Load, one minute'),
      load == null ? '—' : load.toFixed(2),
      t('queue_not_cpu', {}, 'a queue length, not a CPU reading'),
    );

    const pressure = now?.cpu_pressure_some ?? null;
    setCard(
      t('waiting_for_core', {}, 'Waiting for a core'),
      pressure == null ? '—' : `${Number(pressure).toFixed(0)}%`,
      pressure == null
        ? t('no_pressure_stall', {}, 'no pressure stall info on this kernel')
        : t('last_ten_seconds', {}, 'share of the last ten seconds'),
      pressure != null && pressure >= 20 ? 'warn' : '',
    );
    setCard(
      t('context_switches', {}, 'Context switches'),
      now?.ctxt_rate == null ? '—' : `${humanCount(now.ctxt_rate)}/s`,
      t('scheduler_change', {}, 'the scheduler changing its mind'),
    );
    setCard(
      t('interrupts', {}, 'Interrupts'),
      now?.intr_rate == null ? '—' : `${humanCount(now.intr_rate)}/s`,
      t('devices_attention', {}, 'devices asking for attention'),
    );
    setCard(
      t('new_processes', {}, 'New processes'),
      now?.fork_rate == null ? '—' : `${humanCount(now.fork_rate)}/s`,
      t('forks_execs_second', {}, 'forks and execs per second'),
    );

    const verdict = (() => {
      if (!now) {
        return t(
          'queue_verdict_waiting',
          {},
          'Nothing to compare yet — the queue is read from two consecutive collections.',
        );
      }
      if (
        load != null &&
        cores > 0 &&
        load > cores &&
        Number.isFinite(blocked) &&
        blocked >= 2 &&
        Number.isFinite(running) &&
        running <= cores
      ) {
        return t(
          'queue_verdict_storage',
          {
            load: load.toFixed(2),
            cores: count(cores, 'core'),
            running,
            blocked: count(blocked, 'task'),
          },
          'Load is {{load}} on {{cores}}, but only {{running}} task(s) want a core and {{blocked}} are blocked in the kernel. That load is storage, not processor: blocked tasks count towards it and use no CPU at all. A faster processor would change nothing here.',
        );
      }
      if (Number.isFinite(running) && cores > 0 && running > cores * 2) {
        return t(
          'queue_verdict_runnable',
          { running: count(running, 'task'), cores: count(cores, 'core') },
          '{{running}} runnable across {{cores}}. Nothing is stalled — everything gets a turn — but each turn is shorter, and every job takes longer than it would on an idle machine. This is what “slow but nothing is broken” looks like from underneath.',
        );
      }
      if (Number.isFinite(blocked) && blocked >= 4) {
        return t(
          'queue_verdict_blocked',
          { blocked: count(blocked, 'task') },
          '{{blocked}} in uninterruptible sleep. They are waiting on I/O, cannot be killed while they wait, and are inflating the load average without touching the processor.',
        );
      }
      return t(
        'queue_verdict_clear',
        {
          running: Number.isFinite(running)
            ? running
            : t('queue_unknown_count', {}, 'an unknown number of'),
          cores: cores
            ? t(
                'across_cores',
                { count: cores },
                'across {{count, number}} cores',
              )
            : '',
          blocked: Number.isFinite(blocked)
            ? blocked
            : t('queue_unknown_count', {}, 'an unknown number of'),
        },
        'Nothing is queueing: {{running}} tasks runnable {{cores}}, {{blocked}} blocked.',
      );
    })();

    return {
      verdict,
      chartLabels: {
        wantingToRun: t('wanting_to_run', {}, 'Wanting to run'),
        blockedOnIo: t('blocked_on_io', {}, 'Blocked on I/O'),
        loadOneMinute: t('load_one_minute', {}, 'Load, one minute'),
        waitingForCorePercent: t(
          'waiting_for_core_percent',
          {},
          'Waiting for a core (%)',
        ),
        contextSwitchesSecond: t(
          'context_switches_second',
          {},
          'Context switches/s',
        ),
        interruptsSecond: t('interrupts_second', {}, 'Interrupts/s'),
        newProcessesSecond: t('new_processes_second', {}, 'New processes/s'),
      },
    };
  },
};
