/* Core card package: one gauge per CPU core. */
window.ZogographCpuCoresCard = {
  manifest: Object.freeze({
    id: 'core-cpu-cores',
    locales: ['core-cpu-cores.'],
    helpers: ['target', 'buildGauge', 'cpuCoreShare', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-cores.title': 'Each core',
        'core-cpu-cores.no_per_core_lines':
          'No per-core lines in this reading. /proc/stat on this kernel did not break the counters down by core, so only the whole-machine figure exists.',
        'core-cpu-cores.core_label': 'Core {{core, number}}',
        'core-cpu-cores.not_measured_yet': 'not measured yet',
        'core-cpu-cores.busiest_core': 'Busiest core',
        'core-cpu-cores.average_core': 'Average core',
        'core-cpu-cores.quietest_core': 'Quietest core',
        'core-cpu-cores.cores_at_90': 'Cores at 90%+',
      }),
      es: Object.freeze({
        'core-cpu-cores.title': 'Cada núcleo',
        'core-cpu-cores.no_per_core_lines':
          'No hay líneas por núcleo en esta lectura. /proc/stat de este kernel no desglosa los contadores por núcleo, por lo que solo existe la cifra de toda la máquina.',
        'core-cpu-cores.core_label': 'Núcleo {{core, number}}',
        'core-cpu-cores.not_measured_yet': 'aún sin medir',
        'core-cpu-cores.busiest_core': 'Núcleo más ocupado',
        'core-cpu-cores.average_core': 'Núcleo promedio',
        'core-cpu-cores.quietest_core': 'Núcleo menos ocupado',
        'core-cpu-cores.cores_at_90': 'Núcleos al 90%+',
      }),
      hi: Object.freeze({
        'core-cpu-cores.title': 'हर कोर',
        'core-cpu-cores.no_per_core_lines':
          'इस रीडिंग में प्रति-कोर पंक्तियाँ नहीं हैं। इस कर्नेल का /proc/stat काउंटरों को कोर के अनुसार नहीं बाँटता, इसलिए केवल पूरी मशीन का आंकड़ा उपलब्ध है।',
        'core-cpu-cores.core_label': 'कोर {{core, number}}',
        'core-cpu-cores.not_measured_yet': 'अभी नहीं मापा गया',
        'core-cpu-cores.busiest_core': 'सबसे व्यस्त कोर',
        'core-cpu-cores.average_core': 'औसत कोर',
        'core-cpu-cores.quietest_core': 'सबसे शांत कोर',
        'core-cpu-cores.cores_at_90': '90%+ पर कोर',
      }),
      ar: Object.freeze({
        'core-cpu-cores.title': 'كل نواة',
        'core-cpu-cores.no_per_core_lines':
          'لا توجد أسطر لكل نواة في هذه القراءة. لم يقسم ‎/proc/stat في هذه النواة العدادات حسب النواة، لذا لا يوجد سوى رقم الجهاز كله.',
        'core-cpu-cores.core_label': 'النواة {{core, number}}',
        'core-cpu-cores.not_measured_yet': 'لم تُقَس بعد',
        'core-cpu-cores.busiest_core': 'أكثر نواة انشغالاً',
        'core-cpu-cores.average_core': 'متوسط النواة',
        'core-cpu-cores.quietest_core': 'أهدأ نواة',
        'core-cpu-cores.cores_at_90': 'أنوية عند 90%+',
      }),
      zh: Object.freeze({
        'core-cpu-cores.title': '每个核心',
        'core-cpu-cores.no_per_core_lines':
          '此读数没有按核心划分的数据。该内核中的 /proc/stat 没有按核心拆分计数器，因此只有整台机器的数据。',
        'core-cpu-cores.core_label': '核心 {{core, number}}',
        'core-cpu-cores.not_measured_yet': '尚未测量',
        'core-cpu-cores.busiest_core': '最繁忙核心',
        'core-cpu-cores.average_core': '平均核心',
        'core-cpu-cores.quietest_core': '最空闲核心',
        'core-cpu-cores.cores_at_90': '90%+ 的核心',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cpu-cores.${key}`,
      {},
      this.manifest.catalogs.en[`core-cpu-cores.${key}`],
    );
  },
  render(cores, helpers) {
    const { target, buildGauge, cpuCoreShare, i18n } = helpers;
    const chartLabels = {
      busiest: i18n.t('core-cpu-cores.busiest_core'),
      average: i18n.t('core-cpu-cores.average_core'),
      quietest: i18n.t('core-cpu-cores.quietest_core'),
      atNinety: i18n.t('core-cpu-cores.cores_at_90'),
    };
    if (!target) return { chartLabels };
    if (!cores.length) {
      target.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent: i18n.t('core-cpu-cores.no_per_core_lines'),
        }),
      );
      return { chartLabels };
    }
    target.replaceChildren(
      ...cores.map((core) => {
        const value =
          core.busy_percent == null ? null : Number(core.busy_percent);
        return buildGauge({
          label: i18n.t('core-cpu-cores.core_label', { core: core.cpu }),
          value,
          warn: 75,
          crit: 90,
          sub:
            value == null
              ? i18n.t('core-cpu-cores.not_measured_yet')
              : cpuCoreShare(core),
        });
      }),
    );
    return { chartLabels };
  },
};
