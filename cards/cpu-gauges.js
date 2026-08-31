/* Core card package: host and account CPU gauges share shell rendering helpers. */
window.ZogographCpuGaugesCard = {
  manifest: Object.freeze({
    id: 'core-cpu-gauges',
    locales: ['core-cpu-gauges.'],
    helpers: ['target', 'buildGauge', 'count', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-gauges.title': 'CPU',
        'core-cpu-gauges.host_cpu': 'Host CPU',
        'core-cpu-gauges.collector_arc':
          'dark arc: collector tree {{percent}}%',
        'core-cpu-gauges.host_total':
          'total, of every core on the machine{{cores, plural, =0 {} other { ({{cores}})}}}',
        'core-cpu-gauges.workload_excluding_monitor':
          ' · {{percent}}% workload excluding monitor',
        'core-cpu-gauges.account_cpu': "This account's CPU",
        'core-cpu-gauges.account_allowance':
          'of what this account may use, not of the machine',
        'core-cpu-gauges.load_per_core': 'Load per core',
        'core-cpu-gauges.runnable_arc':
          'dark arc: {{count}} collector task{{count, plural, one {} other {s}}} runnable now, not a load-average share',
        'core-cpu-gauges.load_over_cores': '{{load}} over {{cores}}',
        'core-cpu-gauges.collector_runnable':
          ' · collector tree has {{count}} runnable now',
        'core-cpu-gauges.core_share': 'Share of the cores',
        'core-cpu-gauges.core_share_summary':
          "{{mine}} of the machine's {{total}}",
      }),
      es: Object.freeze({
        'core-cpu-gauges.title': 'CPU',
        'core-cpu-gauges.host_cpu': 'CPU del host',
        'core-cpu-gauges.collector_arc':
          'arco oscuro: árbol del recopilador {{percent}}%',
        'core-cpu-gauges.host_total':
          'total, de todos los núcleos de la máquina{{cores, plural, =0 {} other { ({{cores}})}}}',
        'core-cpu-gauges.workload_excluding_monitor':
          ' · {{percent}}% de carga sin el monitor',
        'core-cpu-gauges.account_cpu': 'CPU de esta cuenta',
        'core-cpu-gauges.account_allowance':
          'de lo que esta cuenta puede usar, no de la máquina',
        'core-cpu-gauges.load_per_core': 'Carga por núcleo',
        'core-cpu-gauges.runnable_arc':
          'arco oscuro: {{count}} tarea{{count, plural, one {} other {s}}} del recopilador ejecutable ahora, no una parte de la media de carga',
        'core-cpu-gauges.load_over_cores': '{{load}} sobre {{cores}}',
        'core-cpu-gauges.collector_runnable':
          ' · el árbol del recopilador tiene {{count}} ejecutable ahora',
        'core-cpu-gauges.core_share': 'Proporción de núcleos',
        'core-cpu-gauges.core_share_summary':
          '{{mine}} de los {{total}} de la máquina',
      }),
      hi: Object.freeze({
        'core-cpu-gauges.title': 'CPU',
        'core-cpu-gauges.host_cpu': 'होस्ट CPU',
        'core-cpu-gauges.collector_arc': 'गहरा चाप: कलेक्टर ट्री {{percent}}%',
        'core-cpu-gauges.host_total':
          'कुल, मशीन के हर कोर का{{cores, plural, =0 {} other { ({{cores}})}}}',
        'core-cpu-gauges.workload_excluding_monitor':
          ' · मॉनिटर को छोड़कर {{percent}}% वर्कलोड',
        'core-cpu-gauges.account_cpu': 'इस खाते का CPU',
        'core-cpu-gauges.account_allowance':
          'यह खाता जितना उपयोग कर सकता है उसका, मशीन का नहीं',
        'core-cpu-gauges.load_per_core': 'प्रति कोर लोड',
        'core-cpu-gauges.runnable_arc':
          'गहरा चाप: {{count}} कलेक्टर कार्य अभी चलने योग्य है, लोड औसत का हिस्सा नहीं',
        'core-cpu-gauges.load_over_cores': '{{cores}} पर {{load}}',
        'core-cpu-gauges.collector_runnable':
          ' · कलेक्टर ट्री में {{count}} अभी चलने योग्य है',
        'core-cpu-gauges.core_share': 'कोरों का हिस्सा',
        'core-cpu-gauges.core_share_summary':
          'मशीन के {{total}} में से {{mine}}',
      }),
      ar: Object.freeze({
        'core-cpu-gauges.title': 'المعالج',
        'core-cpu-gauges.host_cpu': 'معالج المضيف',
        'core-cpu-gauges.collector_arc': 'قوس داكن: شجرة الجامع {{percent}}%',
        'core-cpu-gauges.host_total':
          'الإجمالي، من كل نواة في الآلة{{cores, plural, =0 {} other { ({{cores}})}}}',
        'core-cpu-gauges.workload_excluding_monitor':
          ' · حمل {{percent}}% دون المراقب',
        'core-cpu-gauges.account_cpu': 'معالج هذا الحساب',
        'core-cpu-gauges.account_allowance':
          'مما يُسمح لهذا الحساب باستخدامه، لا من الآلة',
        'core-cpu-gauges.load_per_core': 'الحمل لكل نواة',
        'core-cpu-gauges.runnable_arc':
          'قوس داكن: {{count}} مهمة جامع قابلة للتشغيل الآن، وليست حصة من متوسط الحمل',
        'core-cpu-gauges.load_over_cores': '{{load}} على {{cores}}',
        'core-cpu-gauges.collector_runnable':
          ' · لدى شجرة الجامع {{count}} قابلة للتشغيل الآن',
        'core-cpu-gauges.core_share': 'حصة الأنوية',
        'core-cpu-gauges.core_share_summary': '{{mine}} من {{total}} للآلة',
      }),
      zh: Object.freeze({
        'core-cpu-gauges.title': 'CPU',
        'core-cpu-gauges.host_cpu': '主机 CPU',
        'core-cpu-gauges.collector_arc': '深色弧：采集器树 {{percent}}%',
        'core-cpu-gauges.host_total':
          '总计，占机器所有核心{{cores, plural, =0 {} other { ({{cores}})}}}',
        'core-cpu-gauges.workload_excluding_monitor':
          ' · 不含监控器的工作负载 {{percent}}%',
        'core-cpu-gauges.account_cpu': '此帐户的 CPU',
        'core-cpu-gauges.account_allowance': '占此帐户可用额度，而非整台机器',
        'core-cpu-gauges.load_per_core': '每核心负载',
        'core-cpu-gauges.runnable_arc':
          '深色弧：{{count}} 个采集器任务当前可运行，不是负载平均值的一部分',
        'core-cpu-gauges.load_over_cores': '{{load}} / {{cores}}',
        'core-cpu-gauges.collector_runnable':
          ' · 采集器树当前有 {{count}} 个可运行',
        'core-cpu-gauges.core_share': '核心份额',
        'core-cpu-gauges.core_share_summary':
          '机器 {{total}} 个核心中的 {{mine}} 个',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cpu-gauges.${key}`,
      {},
      this.manifest.catalogs.en[`core-cpu-gauges.${key}`],
    );
  },
  render(input, helpers) {
    const { sample, accountVitals } = input;
    const { target, buildGauge, count, i18n } = helpers;
    if (!target) return;
    const t = (key, values = {}) => i18n.t(`core-cpu-gauges.${key}`, values);

    const cores = Number(sample?.cpu_cores) || 0;
    const load1 = sample?.load1 == null ? null : Number(sample.load1);
    const dials = [];

    if (sample?.cpu_percent != null) {
      const monitoringCpu =
        sample.monitoring_cpu_percent == null
          ? null
          : Number(sample.monitoring_cpu_percent);
      const workloadCpu =
        monitoringCpu == null
          ? null
          : Math.max(0, Number(sample.cpu_percent) - monitoringCpu);
      dials.push({
        label: t('host_cpu'),
        value: Number(sample.cpu_percent),
        warn: 70,
        crit: 90,
        overlay: monitoringCpu,
        overlayLabel:
          monitoringCpu == null
            ? null
            : t('collector_arc', { percent: monitoringCpu.toFixed(1) }),
        sub:
          t('host_total', { cores }) +
          (workloadCpu == null
            ? ''
            : t('workload_excluding_monitor', {
                percent: workloadCpu.toFixed(1),
              })),
      });
    }

    if (accountVitals.cpu_percent != null) {
      dials.push({
        label: t('account_cpu'),
        value: Number(accountVitals.cpu_percent),
        warn: 70,
        crit: 90,
        sub: t('account_allowance'),
      });
    }

    if (cores > 0 && load1 != null) {
      const monitorRunnable =
        sample.monitoring_runnable == null
          ? null
          : Number(sample.monitoring_runnable);
      dials.push({
        label: t('load_per_core'),
        value: (load1 / cores) * 100,
        warn: 100,
        crit: 150,
        max: 200,
        overlay:
          monitorRunnable == null ? null : (monitorRunnable / cores) * 100,
        overlayLabel:
          monitorRunnable == null
            ? null
            : t('runnable_arc', { count: monitorRunnable }),
        sub:
          t('load_over_cores', {
            load: load1.toFixed(2),
            cores: count(cores, 'core'),
          }) +
          (monitorRunnable == null
            ? ''
            : t('collector_runnable', { count: monitorRunnable })),
      });
    }

    const myCores = Number(accountVitals.cpu_cores) || 0;
    if (myCores > 0 && cores > 0 && myCores < cores) {
      dials.push({
        label: t('core_share'),
        value: (myCores / cores) * 100,
        warn: 60,
        crit: 85,
        sub: t('core_share_summary', { mine: myCores, total: cores }),
      });
    }

    target.replaceChildren(...dials.map(buildGauge));
  },
};
