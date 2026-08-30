/* Core card package: one gauge per CPU core. */
window.ZogographCpuCoresCard = {
  manifest: Object.freeze({
    id: 'core-cpu-cores',
    locales: ['core-cpu-cores.'],
    helpers: ['target', 'buildGauge', 'cpuCoreShare', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-cores.no_per_core_lines':
          'No per-core lines in this reading. /proc/stat on this kernel did not break the counters down by core, so only the whole-machine figure exists.',
        'core-cpu-cores.core_label': 'Core {{core, number}}',
        'core-cpu-cores.not_measured_yet': 'not measured yet',
      }),
      es: Object.freeze({
        'core-cpu-cores.no_per_core_lines':
          'No hay líneas por núcleo en esta lectura. /proc/stat de este kernel no desglosa los contadores por núcleo, por lo que solo existe la cifra de toda la máquina.',
        'core-cpu-cores.core_label': 'Núcleo {{core, number}}',
        'core-cpu-cores.not_measured_yet': 'aún sin medir',
      }),
      hi: Object.freeze({
        'core-cpu-cores.no_per_core_lines':
          'इस रीडिंग में प्रति-कोर पंक्तियाँ नहीं हैं। इस कर्नेल का /proc/stat काउंटरों को कोर के अनुसार नहीं बाँटता, इसलिए केवल पूरी मशीन का आंकड़ा उपलब्ध है।',
        'core-cpu-cores.core_label': 'कोर {{core, number}}',
        'core-cpu-cores.not_measured_yet': 'अभी नहीं मापा गया',
      }),
      ar: Object.freeze({
        'core-cpu-cores.no_per_core_lines':
          'لا توجد أسطر لكل نواة في هذه القراءة. لم يقسم ‎/proc/stat في هذه النواة العدادات حسب النواة، لذا لا يوجد سوى رقم الجهاز كله.',
        'core-cpu-cores.core_label': 'النواة {{core, number}}',
        'core-cpu-cores.not_measured_yet': 'لم تُقَس بعد',
      }),
      zh: Object.freeze({
        'core-cpu-cores.no_per_core_lines':
          '此读数没有按核心划分的数据。该内核中的 /proc/stat 没有按核心拆分计数器，因此只有整台机器的数据。',
        'core-cpu-cores.core_label': '核心 {{core, number}}',
        'core-cpu-cores.not_measured_yet': '尚未测量',
      }),
    }),
  }),
  render(cores, helpers) {
    const { target, buildGauge, cpuCoreShare, i18n } = helpers;
    if (!target) return;
    if (!cores.length) {
      target.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent: i18n.t('core-cpu-cores.no_per_core_lines'),
        }),
      );
      return;
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
  },
};
