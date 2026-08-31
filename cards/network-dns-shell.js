/* Core card package: static controls and guidance for live DNS lookups. */
window.ZogographNetworkDnsShellCard = {
  manifest: Object.freeze({
    id: 'core-network-dns-shell',
    locales: ['core-network-dns-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-network-dns-shell.title': 'Name lookups',
        'core-network-dns-shell.run_again': 'Run them again',
        'core-network-dns-shell.help':
          'Run when you open this page rather than read from a record — ‘was it working five minutes ago’ is never the question. A nameserver that is busy and answering is a completely different situation from one that is busy and timing out, and only asking it can tell them apart.',
        'core-network-dns-shell.lookup': 'Look it up',
        'core-network-dns-shell.name_placeholder': 'git.example.com',
        'core-network-dns-shell.server_placeholder': 'server (optional)',
      }),
      es: Object.freeze({
        'core-network-dns-shell.title': 'Consultas de nombres',
        'core-network-dns-shell.run_again': 'Ejecutarlas de nuevo',
        'core-network-dns-shell.help':
          'Se ejecutan al abrir esta página, no se leen de un registro: «¿funcionaba hace cinco minutos?» nunca es la pregunta. Un servidor de nombres ocupado que responde es muy distinto de uno ocupado que agota el tiempo de espera; solo al preguntarle se distinguen.',
        'core-network-dns-shell.lookup': 'Buscar',
        'core-network-dns-shell.name_placeholder': 'git.example.com',
        'core-network-dns-shell.server_placeholder': 'servidor (opcional)',
      }),
      hi: Object.freeze({
        'core-network-dns-shell.title': 'नाम लुकअप',
        'core-network-dns-shell.run_again': 'फिर चलाएँ',
        'core-network-dns-shell.help':
          'यह पेज खोलने पर इन्हें चलाया जाता है, किसी रिकॉर्ड से नहीं पढ़ा जाता—‘क्या यह पाँच मिनट पहले काम कर रहा था?’ कभी वास्तविक प्रश्न नहीं है। व्यस्त होते हुए भी उत्तर देने वाला नेमसर्वर, व्यस्त होकर टाइम आउट होने वाले से बिल्कुल अलग स्थिति है; केवल उससे पूछकर ही अंतर पता चलता है।',
        'core-network-dns-shell.lookup': 'खोजें',
        'core-network-dns-shell.name_placeholder': 'git.example.com',
        'core-network-dns-shell.server_placeholder': 'सर्वर (वैकल्पिक)',
      }),
      ar: Object.freeze({
        'core-network-dns-shell.title': 'عمليات بحث الأسماء',
        'core-network-dns-shell.run_again': 'شغّلها مجددًا',
        'core-network-dns-shell.help':
          'تُشغّل عند فتح هذه الصفحة بدل قراءتها من سجل؛ فالسؤال «هل كانت تعمل قبل خمس دقائق؟» ليس هو السؤال أبدًا. خادم أسماء مشغول ويجيب يختلف تمامًا عن خادم مشغول تنتهي مهلة استجابته، ولا يميّز بينهما إلا سؤاله مباشرةً.',
        'core-network-dns-shell.lookup': 'ابحث عنه',
        'core-network-dns-shell.name_placeholder': 'git.example.com',
        'core-network-dns-shell.server_placeholder': 'الخادم (اختياري)',
      }),
      zh: Object.freeze({
        'core-network-dns-shell.title': '名称查询',
        'core-network-dns-shell.run_again': '再次运行',
        'core-network-dns-shell.help':
          '打开本页时会立即运行，而不是读取历史记录——“它五分钟前是否可用？”从来不是问题。繁忙但仍在响应的名称服务器，与繁忙且超时的名称服务器完全不同；只有实际查询才能分辨二者。',
        'core-network-dns-shell.lookup': '查询',
        'core-network-dns-shell.name_placeholder': 'git.example.com',
        'core-network-dns-shell.server_placeholder': '服务器（可选）',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-network-dns-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-network-dns-shell.${key}`],
    );
  },
  render() {},
};
