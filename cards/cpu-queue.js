/* Core card package: only declared dependencies are received from the shell. */
window.ZogographCpuQueueCard = {
  manifest: Object.freeze({
    id: 'core-cpu-queue',
    locales: ['core-cpu-queue.'],
    helpers: ['setCard', 'humanCount', 'i18n'],
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
      }),
    }),
  }),
  render(input, helpers) {
    const { now, sample } = input;
    const { setCard, humanCount, i18n } = helpers;
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
  },
};
