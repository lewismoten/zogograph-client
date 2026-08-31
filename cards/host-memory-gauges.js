/* Core card package: memory headroom, swap state, and account share gauges. */
window.ZogographHostMemoryGaugesCard = {
  manifest: Object.freeze({
    id: 'core-host-memory-gauges',
    locales: ['core-host-memory-gauges.'],
    helpers: ['target', 'buildGauge', 'humanBytes', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-memory-gauges.title': 'Memory',
        'core-host-memory-gauges.memory_committed': 'Memory committed',
        'core-host-memory-gauges.swap_used': 'Swap used',
        'core-host-memory-gauges.account_memory_share': "This account's share",
        'core-host-memory-gauges.account_memory_share_sub':
          "may use {{amount}} of the machine's {{total}}",
        'core-host-memory-gauges.collector_arc':
          'dark arc: collector tree {{amount}} PSS',
        'core-host-memory-gauges.committed_sub':
          '{{committed}} of {{total}} · {{available}} still available',
        'core-host-memory-gauges.non_monitor':
          ' · {{amount}} non-monitor committed',
        'core-host-memory-gauges.monitoring_processes':
          ' · {{count, number}} monitoring processes',
        'core-host-memory-gauges.swap_used_sub': '{{used}} of {{total}}',
        'core-host-memory-gauges.swap_under_pressure': ' · under pressure',
        'core-host-memory-gauges.swap_no_pressure': ' · no pressure',
        'core-host-memory-gauges.swap_not_configured':
          'no swap configured on this machine',
      }),
      es: Object.freeze({
        'core-host-memory-gauges.title': 'Memoria',
        'core-host-memory-gauges.memory_committed': 'Memoria comprometida',
        'core-host-memory-gauges.swap_used': 'Intercambio usado',
        'core-host-memory-gauges.account_memory_share': 'Parte de esta cuenta',
        'core-host-memory-gauges.account_memory_share_sub':
          'puede usar {{amount}} de los {{total}} de la máquina',
        'core-host-memory-gauges.collector_arc':
          'arco oscuro: árbol del recopilador {{amount}} PSS',
        'core-host-memory-gauges.committed_sub':
          '{{committed}} de {{total}} · aún hay {{available}} disponibles',
        'core-host-memory-gauges.non_monitor':
          ' · {{amount}} comprometidos sin monitorización',
        'core-host-memory-gauges.monitoring_processes':
          ' · {{count, number}} procesos de monitorización',
        'core-host-memory-gauges.swap_used_sub': '{{used}} de {{total}}',
        'core-host-memory-gauges.swap_under_pressure': ' · bajo presión',
        'core-host-memory-gauges.swap_no_pressure': ' · sin presión',
        'core-host-memory-gauges.swap_not_configured':
          'no hay intercambio configurado en esta máquina',
      }),
      hi: Object.freeze({
        'core-host-memory-gauges.title': 'मेमोरी',
        'core-host-memory-gauges.memory_committed': 'प्रतिबद्ध मेमोरी',
        'core-host-memory-gauges.swap_used': 'उपयोग किया गया स्वैप',
        'core-host-memory-gauges.account_memory_share': 'इस खाते का हिस्सा',
        'core-host-memory-gauges.account_memory_share_sub':
          'मशीन के {{total}} में से {{amount}} उपयोग कर सकता है',
        'core-host-memory-gauges.collector_arc':
          'गहरा चाप: कलेक्टर ट्री {{amount}} PSS',
        'core-host-memory-gauges.committed_sub':
          '{{committed}} / {{total}} · {{available}} अभी उपलब्ध',
        'core-host-memory-gauges.non_monitor':
          ' · {{amount}} गैर-मॉनिटर प्रतिबद्ध',
        'core-host-memory-gauges.monitoring_processes':
          ' · {{count, number}} मॉनिटरिंग प्रक्रियाएँ',
        'core-host-memory-gauges.swap_used_sub': '{{used}} / {{total}}',
        'core-host-memory-gauges.swap_under_pressure': ' · दबाव में',
        'core-host-memory-gauges.swap_no_pressure': ' · कोई दबाव नहीं',
        'core-host-memory-gauges.swap_not_configured':
          'इस मशीन पर कोई स्वैप कॉन्फ़िगर नहीं है',
      }),
      ar: Object.freeze({
        'core-host-memory-gauges.title': 'الذاكرة',
        'core-host-memory-gauges.memory_committed': 'الذاكرة الملتزم بها',
        'core-host-memory-gauges.swap_used': 'التبديل المستخدم',
        'core-host-memory-gauges.account_memory_share': 'حصة هذا الحساب',
        'core-host-memory-gauges.account_memory_share_sub':
          'يمكنه استخدام {{amount}} من {{total}} للجهاز',
        'core-host-memory-gauges.collector_arc':
          'قوس داكن: شجرة الجامع {{amount}} PSS',
        'core-host-memory-gauges.committed_sub':
          '{{committed}} من {{total}} · ما زال {{available}} متاحًا',
        'core-host-memory-gauges.non_monitor':
          ' · {{amount}} ملتزم خارج المراقبة',
        'core-host-memory-gauges.monitoring_processes':
          ' · {{count, number}} عمليات مراقبة',
        'core-host-memory-gauges.swap_used_sub': '{{used}} من {{total}}',
        'core-host-memory-gauges.swap_under_pressure': ' · تحت ضغط',
        'core-host-memory-gauges.swap_no_pressure': ' · بلا ضغط',
        'core-host-memory-gauges.swap_not_configured':
          'لا توجد مساحة تبديل مهيأة على هذا الجهاز',
      }),
      zh: Object.freeze({
        'core-host-memory-gauges.title': '内存',
        'core-host-memory-gauges.memory_committed': '已提交内存',
        'core-host-memory-gauges.swap_used': '已用交换空间',
        'core-host-memory-gauges.account_memory_share': '此账户的份额',
        'core-host-memory-gauges.account_memory_share_sub':
          '可使用机器 {{total}} 中的 {{amount}}',
        'core-host-memory-gauges.collector_arc':
          '深色弧：采集器树 {{amount}} PSS',
        'core-host-memory-gauges.committed_sub':
          '{{committed}} / {{total}} · 仍有 {{available}} 可用',
        'core-host-memory-gauges.non_monitor': ' · {{amount}} 非监控已提交内存',
        'core-host-memory-gauges.monitoring_processes':
          ' · {{count, number}} 个监控进程',
        'core-host-memory-gauges.swap_used_sub': '{{used}} / {{total}}',
        'core-host-memory-gauges.swap_under_pressure': ' · 存在压力',
        'core-host-memory-gauges.swap_no_pressure': ' · 无压力',
        'core-host-memory-gauges.swap_not_configured': '此机器未配置交换空间',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-memory-gauges.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-memory-gauges.${key}`],
    );
  },
  render(input, helpers) {
    const { sample, swapUsed, swap, accountVitals } = input;
    const { target, buildGauge, humanBytes, i18n } = helpers;
    const t = (key, values = {}) =>
      i18n.t(`core-host-memory-gauges.${key}`, values);
    if (!target) return;

    const total = Number(sample.mem_total) || 0;
    const available = Number(sample.mem_available) || 0;
    const swapTotal = Number(sample.swap_total) || 0;
    const dials = [];

    if (total > 0) {
      const committed = Math.max(0, total - available);
      const monitoringPss =
        sample.monitoring_pss_bytes == null
          ? null
          : Number(sample.monitoring_pss_bytes);
      const workloadCommitted =
        monitoringPss == null ? null : Math.max(0, committed - monitoringPss);
      dials.push({
        label: t('memory_committed'),
        value: (committed / total) * 100,
        warn: 80,
        crit: 92,
        overlay: monitoringPss == null ? null : (monitoringPss / total) * 100,
        overlayLabel:
          monitoringPss == null
            ? null
            : t('collector_arc', { amount: humanBytes(monitoringPss) }),
        sub:
          t('committed_sub', {
            committed: humanBytes(committed),
            total: humanBytes(total),
            available: humanBytes(available),
          }) +
          (workloadCommitted == null
            ? ''
            : t('non_monitor', { amount: humanBytes(workloadCommitted) })) +
          (sample.monitoring_processes == null
            ? ''
            : t('monitoring_processes', {
                count: sample.monitoring_processes,
              })),
      });
    }

    if (swapTotal > 0) {
      dials.push({
        label: t('swap_used'),
        value: (swapUsed / swapTotal) * 100,
        warn: swap?.state === 'pressure' ? 1 : 60,
        crit: swap?.state === 'pressure' ? 25 : 90,
        sub:
          t('swap_used_sub', {
            used: humanBytes(swapUsed),
            total: humanBytes(swapTotal),
          }) +
          (swap?.state === 'pressure'
            ? t('swap_under_pressure')
            : t('swap_no_pressure')),
      });
    } else {
      dials.push({
        label: t('swap_used'),
        value: null,
        warn: 60,
        crit: 90,
        sub: t('swap_not_configured'),
      });
    }

    const mine = Number(accountVitals.mem_total_bytes) || 0;
    if (total > 0 && mine > 0 && mine < total) {
      dials.push({
        label: t('account_memory_share'),
        value: (mine / total) * 100,
        warn: 60,
        crit: 85,
        sub: t('account_memory_share_sub', {
          amount: humanBytes(mine),
          total: humanBytes(total),
        }),
      });
    }
    target.replaceChildren(...dials.map(buildGauge));
  },
};
