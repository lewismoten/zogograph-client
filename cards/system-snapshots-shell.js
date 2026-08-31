/* Core card package: static shell copy for database copies. */
window.ZogographSystemSnapshotsShellCard = {
  manifest: Object.freeze({
    id: 'core-system-snapshots-shell',
    locales: ['core-system-snapshots-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-snapshots-shell.title': 'Database copies',
        'core-system-snapshots-shell.help':
          'Forensic copies, written beside the databases and deliberately outside every retention rule — the point of one is to survive the roll-up that would otherwise discard the readings it is about. Which also means nothing expires them, so they are listed here for somebody to look at.',
      }),
      es: Object.freeze({
        'core-system-snapshots-shell.title': 'Copias de bases de datos',
        'core-system-snapshots-shell.help':
          'Copias forenses escritas junto a las bases de datos y deliberadamente fuera de toda regla de retención: el propósito es sobrevivir a la consolidación que de otro modo descartaría las lecturas de las que tratan. Eso también significa que nada las vence, por lo que aparecen aquí para que alguien las revise.',
      }),
      hi: Object.freeze({
        'core-system-snapshots-shell.title': 'डेटाबेस प्रतियाँ',
        'core-system-snapshots-shell.help':
          'फोरेंसिक प्रतियाँ, डेटाबेस के पास लिखी गईं और जानबूझकर हर रिटेंशन नियम से बाहर—उनका उद्देश्य उस रोल-अप से बचना है जो अन्यथा उन रीडिंग को हटा देगा जिनके बारे में वे हैं। इसका अर्थ यह भी है कि वे कभी समाप्त नहीं होतीं, इसलिए यहाँ किसी के देखने के लिए सूचीबद्ध हैं।',
      }),
      ar: Object.freeze({
        'core-system-snapshots-shell.title': 'نسخ قواعد البيانات',
        'core-system-snapshots-shell.help':
          'نسخ جنائية مكتوبة بجوار قواعد البيانات وخارج كل قاعدة احتفاظ عمدًا — فالغرض من الواحدة أن تنجو من التجميع الذي قد يتخلص من القراءات التي تتعلق بها. وهذا يعني أيضًا أن لا شيء ينهي صلاحيتها، لذا سُردت هنا لكي ينظر إليها أحد.',
      }),
      zh: Object.freeze({
        'core-system-snapshots-shell.title': '数据库副本',
        'core-system-snapshots-shell.help':
          '取证副本写在数据库旁，并有意排除在所有保留规则之外——它们的目的就是在原本会丢弃相关读数的汇总中存活下来。这也意味着没有任何内容会让它们过期，因此列在这里供人查看。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-snapshots-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-snapshots-shell.${key}`],
    );
  },
  render() {},
};
