/* Core card package: page shell for host-wide disk activity. */
window.ZogographHostIoShellCard = {
  manifest: Object.freeze({
    id: 'core-host-io-shell',
    locales: ['core-host-io-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-io-shell.title': 'How hard the disks are working',
        'core-host-io-shell.help':
          "Throughput and saturation per device, read by root — so this is every account on the server together. A busy line here says the disk is working, never that this account is the reason; the account's own traffic is on {{dashboard}}, and {{processes}} has its own page.",
        'core-host-io-shell.read': 'Read',
        'core-host-io-shell.written': 'Written',
        'core-host-io-shell.busiest_device': 'Busiest device % (right)',
        'core-host-io-shell.collector_missing':
          'The host collector is not installed.',
        'core-host-io-shell.needs_two_readings':
          'Needs two host readings before a rate can be worked out.',
        'core-host-io-shell.no_counters':
          'No device counters recorded for this range.',
        'core-host-io-shell.busiest_moment':
          'Busiest moment: {{rate}} at {{at}} — the busiest device was {{saturation}}% saturated.',
        'core-host-io-shell.counter_restarts':
          '{{readings, number}} readings could not be turned into a rate, because the counters restarted — that is a reboot.',
      }),
      es: Object.freeze({
        'core-host-io-shell.title': 'Cuánto están trabajando los discos',
        'core-host-io-shell.help':
          'Rendimiento y saturación por dispositivo, leídos por root; por tanto incluye todas las cuentas del servidor. Una línea ocupada indica que el disco trabaja, no que esta cuenta sea la causa; el tráfico de la cuenta está en {{dashboard}} y {{processes}} tiene su propia página.',
        'core-host-io-shell.read': 'Leído',
        'core-host-io-shell.written': 'Escrito',
        'core-host-io-shell.busiest_device':
          'Dispositivo más ocupado % (derecha)',
        'core-host-io-shell.collector_missing':
          'El recopilador del host no está instalado.',
        'core-host-io-shell.needs_two_readings':
          'Se necesitan dos lecturas del host para calcular una tasa.',
        'core-host-io-shell.no_counters':
          'No se registraron contadores de dispositivos para este intervalo.',
        'core-host-io-shell.busiest_moment':
          'Momento más ocupado: {{rate}} a las {{at}}; el dispositivo más ocupado estaba saturado al {{saturation}}%.',
        'core-host-io-shell.counter_restarts':
          '{{readings, number}} lecturas no pudieron convertirse en una tasa porque los contadores se reiniciaron: eso es un reinicio del sistema.',
      }),
      hi: Object.freeze({
        'core-host-io-shell.title': 'डिस्क कितनी मेहनत कर रही हैं',
        'core-host-io-shell.help':
          'प्रति डिवाइस थ्रूपुट और संतृप्ति, root द्वारा पढ़ी गई — इसलिए सर्वर के सभी खाते शामिल हैं। व्यस्त लाइन का अर्थ केवल डिस्क काम कर रही है; इस खाते का ट्रैफ़िक {{dashboard}} पर है और {{processes}} का अपना पृष्ठ है।',
        'core-host-io-shell.read': 'पढ़ा',
        'core-host-io-shell.written': 'लिखा गया',
        'core-host-io-shell.busiest_device': 'सबसे व्यस्त डिवाइस % (दायाँ)',
        'core-host-io-shell.collector_missing':
          'होस्ट कलेक्टर स्थापित नहीं है।',
        'core-host-io-shell.needs_two_readings':
          'दर निकालने से पहले दो होस्ट रीडिंग चाहिए।',
        'core-host-io-shell.no_counters':
          'इस सीमा के लिए कोई डिवाइस काउंटर दर्ज नहीं है।',
        'core-host-io-shell.busiest_moment':
          'सबसे व्यस्त क्षण: {{rate}} पर {{at}} — सबसे व्यस्त डिवाइस {{saturation}}% संतृप्त थी।',
        'core-host-io-shell.counter_restarts':
          '{{readings, number}} रीडिंग को दर में नहीं बदला जा सका क्योंकि काउंटर फिर से शुरू हुए — यह एक रीबूट है।',
      }),
      ar: Object.freeze({
        'core-host-io-shell.title': 'مدى انشغال الأقراص',
        'core-host-io-shell.help':
          'معدل النقل والتشبع لكل جهاز، يقرؤه root، لذا يشمل كل حسابات الخادم. السطر المشغول هنا يعني أن القرص يعمل لا أن هذا الحساب هو السبب؛ حركة الحساب في {{dashboard}} و{{processes}} لها صفحتها الخاصة.',
        'core-host-io-shell.read': 'قراءة',
        'core-host-io-shell.written': 'كتابة',
        'core-host-io-shell.busiest_device': 'أكثر الأجهزة انشغالًا % (يمين)',
        'core-host-io-shell.collector_missing': 'جامع المضيف غير مثبّت.',
        'core-host-io-shell.needs_two_readings':
          'يلزم قراءتان للمضيف قبل احتساب المعدل.',
        'core-host-io-shell.no_counters':
          'لم تُسجَّل عدادات أجهزة لهذا النطاق.',
        'core-host-io-shell.busiest_moment':
          'أكثر اللحظات انشغالًا: {{rate}} عند {{at}} — كان أكثر الأجهزة انشغالًا مشبعًا بنسبة {{saturation}}%.',
        'core-host-io-shell.counter_restarts':
          'تعذر تحويل {{readings, number}} قراءة إلى معدل لأن العدادات أعيد تشغيلها — وهذا يعني إعادة تشغيل.',
      }),
      zh: Object.freeze({
        'core-host-io-shell.title': '磁盘工作负载',
        'core-host-io-shell.help':
          '由 root 读取的每个设备的吞吐量和饱和度，因此包含服务器上的所有账户。此处忙碌的线表示磁盘正在工作，并不表示由此账户造成；账户自身流量在 {{dashboard}}，{{processes}} 有单独页面。',
        'core-host-io-shell.read': '读取',
        'core-host-io-shell.written': '写入',
        'core-host-io-shell.busiest_device': '最繁忙设备 %（右轴）',
        'core-host-io-shell.collector_missing': '未安装主机采集器。',
        'core-host-io-shell.needs_two_readings':
          '需要两次主机读数才能计算速率。',
        'core-host-io-shell.no_counters': '此范围内没有记录设备计数器。',
        'core-host-io-shell.busiest_moment':
          '最繁忙时刻：{{at}} 的速率为 {{rate}}——最繁忙设备饱和度为 {{saturation}}%。',
        'core-host-io-shell.counter_restarts':
          '{{readings, number}} 次读数无法转换为速率，因为计数器已重置——这意味着发生了重启。',
      }),
    }),
  }),
  text(key, helpers, values = {}) {
    return helpers.i18n.t(
      `core-host-io-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-host-io-shell.${key}`],
    );
  },
  render() {},
};
