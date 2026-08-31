/* Core card package: one gauge per CPU core. */
window.ZogographCpuCoresCard = {
  manifest: Object.freeze({
    id: 'core-cpu-cores',
    locales: ['core-cpu-cores.'],
    helpers: ['target', 'buildGauge', 'cpuCoreShare', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-cores.title': 'Each core',
        'core-cpu-cores.help':
          'The distribution the average has already thrown away. Eight cores at 12% and one core at 100% beside seven idle ones are the same whole-machine reading and completely different problems: the second is a single-threaded bottleneck, it will never make the overall percentage look alarming however stuck it gets, and buying more cores makes it exactly as slow as it was.',
        'core-cpu-cores.history_title':
          'Hottest, coolest and average, over time',
        'core-cpu-cores.history_help':
          'Three lines rather than one per core: on a machine with thirty-two of them the individual lines are a legend rather than a chart. The gap between the top line and the bottom one is the whole finding — when it is wide the work is not spreading, and when the three sit together the machine is loaded evenly and the only question left is how much.',
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
        'core-cpu-cores.help':
          'La distribución que el promedio ya descartó. Ocho núcleos al 12% y uno al 100% junto a siete inactivos dan la misma lectura de toda la máquina, pero son problemas completamente distintos: el segundo es un cuello de botella de un solo hilo, nunca hará que el porcentaje general parezca alarmante por mucho que se atasque, y comprar más núcleos lo deja exactamente igual de lento.',
        'core-cpu-cores.history_title':
          'El más caliente, el más frío y el promedio a lo largo del tiempo',
        'core-cpu-cores.history_help':
          'Tres líneas en vez de una por núcleo: en una máquina con treinta y dos, las líneas individuales serían una leyenda y no un gráfico. La diferencia entre la línea superior y la inferior es todo el hallazgo: si es grande, el trabajo no se está repartiendo; si las tres van juntas, la máquina tiene una carga uniforme y solo queda saber cuánto.',
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
        'core-cpu-cores.help':
          'वह वितरण जिसे औसत पहले ही फेंक चुका है। 12% पर आठ कोर और सात निष्क्रिय कोरों के साथ 100% पर एक कोर पूरी मशीन की वही रीडिंग हैं, मगर बिल्कुल अलग समस्याएँ: दूसरी एकल-थ्रेड बाधा है; कितना भी अटके, कुल प्रतिशत कभी खतरनाक नहीं दिखेगा; और अधिक कोर खरीदने पर भी यह उतनी ही धीमी रहेगी।',
        'core-cpu-cores.history_title':
          'समय के साथ सबसे गर्म, सबसे ठंडा और औसत',
        'core-cpu-cores.history_help':
          'हर कोर की एक लाइन के बजाय तीन लाइनें: बत्तीस कोर वाली मशीन पर अलग-अलग लाइनें चार्ट नहीं, लीजेंड बन जातीं। सबसे ऊपर और सबसे नीचे की लाइन का अंतर ही पूरा निष्कर्ष है—चौड़ा हो तो काम फैल नहीं रहा, और तीनों साथ हों तो मशीन समान रूप से लोड है और केवल मात्रा का प्रश्न बचता है।',
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
        'core-cpu-cores.help':
          'التوزيع الذي أضاعه المتوسط بالفعل. ثماني أنوية عند 12% ونواة واحدة عند 100% إلى جوار سبع خاملة تعطي القراءة نفسها للآلة كلها، لكنها مشكلتان مختلفتان تمامًا: الثانية عنق زجاجة أحادي الخيط، ولن تجعل النسبة الإجمالية تبدو مقلقة مهما تعثرت، وشراء المزيد من الأنوية يجعلها بالبطء نفسه تمامًا.',
        'core-cpu-cores.history_title':
          'الأكثر سخونة وبرودة والمتوسط عبر الزمن',
        'core-cpu-cores.history_help':
          'ثلاثة خطوط بدل خط لكل نواة: في آلة بها اثنتان وثلاثون نواة تصبح الخطوط المفردة مفتاحًا لا مخططًا. الفجوة بين الخط الأعلى والأدنى هي النتيجة كلها — حين تتسع لا ينتشر العمل، وحين تتقارب الثلاثة تكون الآلة محملة بالتساوي ولا يبقى سوى سؤال كم هو الحمل.',
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
        'core-cpu-cores.help':
          '平均值已经丢弃的分布。八个核心处于 12%、一个核心处于 100%、旁边还有七个空闲核心，得出的整机读数相同，问题却完全不同：后者是单线程瓶颈，无论卡得多严重，整体百分比都不会显得惊人；买更多核心也不会让它更快。',
        'core-cpu-cores.history_title': '随时间变化的最热、最冷和平均值',
        'core-cpu-cores.history_help':
          '每个核心不画一条线，而是画三条：在有三十二个核心的机器上，单独的线会变成图例而非图表。顶线与底线之间的差距就是完整结论——差距大表示工作没有分散，三条线靠在一起表示机器负载均匀，剩下的问题只有负载多少。',
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
