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
        'core-cpu-cores.stolen_since_boot': '{{percent}}% stolen since boot',
        'core-cpu-cores.iowait_since_boot': '{{percent}}% iowait since boot',
        'core-cpu-cores.user_since_boot': '{{percent}}% user since boot',
        'core-cpu-cores.spread_not_measured':
          'No per-core percentages yet. Each one is a difference between two readings, so the first collection after this feature was added has nothing to compare against.',
        'core-cpu-cores.spread_one_core':
          "One core, so there is no distribution to look at — the whole-machine figure is the core's figure.",
        'core-cpu-cores.spread_shape':
          'Busiest core {{busiest, number}}%, quietest {{quietest, number}}%, {{average, number}}% on average across {{cores, number}} cores.',
        'core-cpu-cores.spread_all_pinned':
          '{{shape}} Every core is pinned: the machine has no processor headroom at all. Unlike a single busy core this does respond to more cores — but it is worth checking first that the work is work you wanted done.',
        'core-cpu-cores.spread_single_threaded':
          '{{shape}} That is the shape of a single-threaded bottleneck: one piece of work that cannot be split, holding things up while the machine looks mostly free. The whole-machine percentage will never look alarming however stuck it gets, and more cores would not move it.',
        'core-cpu-cores.spread_even':
          '{{shape}} The work is spreading evenly, so the only question left is how much of it there is.',
        'core-cpu-cores.spread_uneven':
          '{{shape}} Uneven, but not one core carrying everything — normal on a machine running a mixture of jobs, and worth a second look if the gap keeps widening.',
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
        'core-cpu-cores.stolen_since_boot':
          '{{percent}}% robado desde el arranque',
        'core-cpu-cores.iowait_since_boot':
          '{{percent}}% de espera de E/S desde el arranque',
        'core-cpu-cores.user_since_boot':
          '{{percent}}% de usuario desde el arranque',
        'core-cpu-cores.spread_not_measured':
          'Aún no hay porcentajes por núcleo. Cada uno es la diferencia entre dos lecturas, por lo que la primera recopilación tras añadir esta función no tiene nada con qué compararse.',
        'core-cpu-cores.spread_one_core':
          'Hay un núcleo, por lo que no existe una distribución que observar: la cifra de toda la máquina es la de ese núcleo.',
        'core-cpu-cores.spread_shape':
          'Núcleo más ocupado {{busiest, number}}%, más tranquilo {{quietest, number}}%, promedio de {{average, number}}% en {{cores, number}} núcleos.',
        'core-cpu-cores.spread_all_pinned':
          '{{shape}} Todos los núcleos están saturados: la máquina no tiene margen de procesador. A diferencia de un único núcleo ocupado, esto sí responde a más núcleos; pero primero conviene comprobar que el trabajo sea el que quería ejecutar.',
        'core-cpu-cores.spread_single_threaded':
          '{{shape}} Esta es la forma de un cuello de botella de un solo hilo: una tarea que no se puede dividir retiene todo mientras la máquina parece mayormente libre. El porcentaje de toda la máquina nunca parecerá alarmante, por atascado que esté, y más núcleos no lo resolverían.',
        'core-cpu-cores.spread_even':
          '{{shape}} El trabajo se reparte de forma uniforme, por lo que solo queda preguntar cuánto hay.',
        'core-cpu-cores.spread_uneven':
          '{{shape}} Es desigual, pero no hay un núcleo que cargue con todo; es normal en una máquina con una mezcla de trabajos y merece revisarse de nuevo si la diferencia sigue creciendo.',
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
        'core-cpu-cores.stolen_since_boot':
          'बूट के बाद से {{percent}}% लिया गया',
        'core-cpu-cores.iowait_since_boot':
          'बूट के बाद से {{percent}}% I/O प्रतीक्षा',
        'core-cpu-cores.user_since_boot':
          'बूट के बाद से {{percent}}% उपयोगकर्ता समय',
        'core-cpu-cores.spread_not_measured':
          'अभी प्रति-कोर प्रतिशत नहीं हैं। हर प्रतिशत दो रीडिंग के अंतर से बनता है, इसलिए इस सुविधा के जुड़ने के बाद के पहले संग्रह के पास तुलना के लिए कुछ नहीं है।',
        'core-cpu-cores.spread_one_core':
          'एक कोर है, इसलिए देखने के लिए कोई वितरण नहीं है—पूरी मशीन का आंकड़ा उसी कोर का आंकड़ा है।',
        'core-cpu-cores.spread_shape':
          'सबसे व्यस्त कोर {{busiest, number}}%, सबसे शांत {{quietest, number}}%, {{cores, number}} कोर में औसत {{average, number}}%।',
        'core-cpu-cores.spread_all_pinned':
          '{{shape}} हर कोर भरा हुआ है: मशीन में प्रोसेसर की कोई क्षमता शेष नहीं है। एक व्यस्त कोर के विपरीत यह अधिक कोर से सुधरता है, लेकिन पहले यह देखना उचित है कि चल रहा काम वही है जो आप चाहते थे।',
        'core-cpu-cores.spread_single_threaded':
          '{{shape}} यह सिंगल-थ्रेडेड अड़चन का रूप है: एक ऐसा काम जिसे बाँटा नहीं जा सकता, सबको रोके रखता है जबकि मशीन अधिकतर खाली दिखती है। पूरी मशीन का प्रतिशत कभी चिंताजनक नहीं दिखेगा, चाहे वह कितना भी अटका हो, और अधिक कोर इसे नहीं बदलेंगे।',
        'core-cpu-cores.spread_even':
          '{{shape}} काम समान रूप से फैल रहा है, इसलिए केवल यह प्रश्न बचता है कि काम कितना है।',
        'core-cpu-cores.spread_uneven':
          '{{shape}} असमान है, पर एक कोर सब कुछ नहीं उठा रहा। अलग-अलग काम चलाने वाली मशीन पर यह सामान्य है और अंतर बढ़ता रहे तो फिर से देखना चाहिए।',
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
        'core-cpu-cores.stolen_since_boot': '{{percent}}% مسروق منذ الإقلاع',
        'core-cpu-cores.iowait_since_boot':
          '{{percent}}% انتظار إدخال/إخراج منذ الإقلاع',
        'core-cpu-cores.user_since_boot': '{{percent}}% مستخدم منذ الإقلاع',
        'core-cpu-cores.spread_not_measured':
          'لا توجد نسب مئوية لكل نواة بعد. كل منها فرق بين قراءتين، لذلك لا توجد مقارنة للقراءة الأولى بعد إضافة هذه الميزة.',
        'core-cpu-cores.spread_one_core':
          'هناك نواة واحدة، فلا يوجد توزيع للنظر إليه؛ رقم الجهاز كله هو رقم تلك النواة.',
        'core-cpu-cores.spread_shape':
          'أكثر نواة انشغالاً {{busiest, number}}%، وأهدؤها {{quietest, number}}%، ومتوسط {{average, number}}% عبر {{cores, number}} أنوية.',
        'core-cpu-cores.spread_all_pinned':
          '{{shape}} كل النوى مثبّتة عند الحد: لا توجد سعة معالجة متبقية في الجهاز. بخلاف نواة مشغولة واحدة، تستجيب هذه الحالة لإضافة نوى، لكن من المفيد أولاً التأكد أن العمل هو ما أردت تشغيله.',
        'core-cpu-cores.spread_single_threaded':
          '{{shape}} هذا شكل اختناق أحادي الخيط: عمل واحد لا يمكن تقسيمه يعطل الأشياء بينما يبدو الجهاز خاليًا في معظمه. لن تبدو نسبة الجهاز كله مقلقة مهما اشتد التعطل، ولن تغير النوى الإضافية ذلك.',
        'core-cpu-cores.spread_even':
          '{{shape}} يتوزع العمل بالتساوي، لذا لا يبقى إلا سؤال مقدار العمل الموجود.',
        'core-cpu-cores.spread_uneven':
          '{{shape}} التوزيع غير متساوٍ، لكن نواة واحدة لا تحمل كل شيء؛ وهذا طبيعي على جهاز يشغل مزيجًا من الأعمال ويستحق نظرة ثانية إذا استمرت الفجوة في الاتساع.',
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
        'core-cpu-cores.stolen_since_boot': '自启动以来被占用 {{percent}}%',
        'core-cpu-cores.iowait_since_boot': '自启动以来 I/O 等待 {{percent}}%',
        'core-cpu-cores.user_since_boot': '自启动以来用户态 {{percent}}%',
        'core-cpu-cores.spread_not_measured':
          '尚无按核心划分的百分比。每个百分比都来自两个读数之间的差异，因此添加此功能后的第一次收集没有可比较的数据。',
        'core-cpu-cores.spread_one_core':
          '只有一个核心，因此没有可查看的分布；整台机器的数据就是该核心的数据。',
        'core-cpu-cores.spread_shape':
          '最繁忙核心 {{busiest, number}}%，最空闲 {{quietest, number}}%，{{cores, number}} 个核心平均 {{average, number}}%。',
        'core-cpu-cores.spread_all_pinned':
          '{{shape}} 每个核心都已满载：机器完全没有处理器余量。与单个忙碌核心不同，这种情况会因增加核心而改善；但应先确认这些工作确实是您想要执行的。',
        'core-cpu-cores.spread_single_threaded':
          '{{shape}} 这是单线程瓶颈的特征：一项无法拆分的工作拖住了其他内容，而机器看上去大多空闲。无论它卡得多严重，整机百分比都不会显得惊人，增加核心也不会改善它。',
        'core-cpu-cores.spread_even':
          '{{shape}} 工作分布均匀，因此唯一剩下的问题是工作量有多大。',
        'core-cpu-cores.spread_uneven':
          '{{shape}} 分布不均，但并非一个核心承担全部工作；在混合运行多种作业的机器上这很正常，如果差距持续扩大则值得再次查看。',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-cpu-cores.${key}`,
      values,
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
