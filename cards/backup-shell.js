/* Core card package: static page shells for backup discovery and recovery. */
window.ZogographBackupShellCard = {
  manifest: Object.freeze({
    id: 'core-backup-shell',
    locales: ['core-backup-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-shell.storage_title': 'Backup storage',
        'core-backup-shell.storage_help':
          'Backups are usually the largest thing on a server and the last place anybody looks, because they are not in /home. This asks four questions: how much space, whose, how old, and what is safe to do about it.',
        'core-backup-shell.actions_title': 'What can be done about it',
        'core-backup-shell.actions_help':
          'Sorted by how safe each one is, not by how much it would free. Anything marked destructive removes the only copy of something — the whole point of a backup is that it is there on the day nobody expected to need it.',
        'core-backup-shell.other_title': 'Other probable backups',
        'core-backup-shell.other_help':
          'Directories that look like backups but are not managed by anything: a tar.gz somebody made before an upgrade, a dated copy of a site, a database dump. Nothing rotates these, so they stay for ever and are usually the oldest large thing on the disk. Probable is the operative word — each is a guess from the name and shape, and worth looking at before acting.',
      }),
      es: Object.freeze({
        'core-backup-shell.storage_title':
          'Almacenamiento de copias de seguridad',
        'core-backup-shell.storage_help':
          'Las copias de seguridad suelen ser lo más grande de un servidor y lo último que alguien mira, porque no están en /home. Esta página responde cuatro preguntas: cuánto espacio ocupan, de quién son, qué antigüedad tienen y qué es seguro hacer con ellas.',
        'core-backup-shell.actions_title': 'Qué se puede hacer',
        'core-backup-shell.actions_help':
          'Se ordenan por la seguridad de cada acción, no por cuánto liberarían. Toda acción destructiva elimina la única copia de algo; la razón de una copia de seguridad es que esté disponible el día que nadie esperaba necesitarla.',
        'core-backup-shell.other_title': 'Otras posibles copias de seguridad',
        'core-backup-shell.other_help':
          'Directorios que parecen copias de seguridad pero que no gestiona nada: un tar.gz creado antes de una actualización, una copia fechada de un sitio o un volcado de base de datos. No se rotan, por lo que suelen ser lo más antiguo y grande del disco. Probable es la palabra clave: cada uno es una conjetura basada en su nombre y forma, y conviene revisarlo antes de actuar.',
      }),
      hi: Object.freeze({
        'core-backup-shell.storage_title': 'बैकअप स्टोरेज',
        'core-backup-shell.storage_help':
          'बैकअप अक्सर सर्वर की सबसे बड़ी चीज़ होते हैं और सबसे अंत में देखे जाते हैं, क्योंकि वे /home में नहीं होते। यह चार प्रश्न पूछता है: कितनी जगह, किसकी, कितने पुराने और इनके बारे में क्या सुरक्षित है।',
        'core-backup-shell.actions_title': 'इसके बारे में क्या किया जा सकता है',
        'core-backup-shell.actions_help':
          'इन्हें इस आधार पर क्रमित किया गया है कि कौन-सा सुरक्षित है, न कि कितना स्थान खाली करेगा। विनाशकारी चिह्नित कार्रवाई किसी चीज़ की एकमात्र प्रति हटा देती है।',
        'core-backup-shell.other_title': 'अन्य संभावित बैकअप',
        'core-backup-shell.other_help':
          'ऐसी डायरेक्टरियाँ जो बैकअप जैसी दिखती हैं लेकिन किसी के द्वारा प्रबंधित नहीं हैं: अपग्रेड से पहले बना tar.gz, साइट की दिनांकित कॉपी या डेटाबेस डंप। इन्हें कोई नहीं घुमाता, इसलिए वे अक्सर डिस्क की सबसे पुरानी बड़ी चीज़ होती हैं। संभावित शब्द महत्वपूर्ण है: हर एक नाम और आकार के आधार पर अनुमान है, इसलिए कार्रवाई से पहले जाँचें।',
      }),
      ar: Object.freeze({
        'core-backup-shell.storage_title': 'تخزين النسخ الاحتياطية',
        'core-backup-shell.storage_help':
          'النسخ الاحتياطية غالبًا أكبر ما في الخادم وآخر ما يفحصه الناس لأنها ليست في /home. يجيب هذا القسم عن أربعة أسئلة: كم من المساحة، ولمن، وكم عمرها، وما الإجراء الآمن بشأنها.',
        'core-backup-shell.actions_title': 'ما الذي يمكن فعله',
        'core-backup-shell.actions_help':
          'تُرتب حسب أمان كل إجراء لا حسب المساحة التي سيوفرها. الإجراء المعلّم بأنه تدميري يزيل النسخة الوحيدة من شيء ما.',
        'core-backup-shell.other_title': 'نسخ احتياطية محتملة أخرى',
        'core-backup-shell.other_help':
          'مجلدات تبدو كنسخ احتياطية لكن لا يديرها شيء: ملف tar.gz أُنشئ قبل ترقية أو نسخة مؤرخة من موقع أو تفريغ قاعدة بيانات. لا شيء يدوّرها، لذا غالبًا ما تكون أقدم الأشياء الكبيرة على القرص. كلمة محتملة مهمة: كل منها تخمين من الاسم والشكل ويستحق المراجعة قبل الإجراء.',
      }),
      zh: Object.freeze({
        'core-backup-shell.storage_title': '备份存储',
        'core-backup-shell.storage_help':
          '备份通常是服务器上最大的内容，也是最后才被查看的内容，因为它们不在 /home 中。此处回答四个问题：占用多少空间、属于谁、有多旧，以及可以安全采取什么操作。',
        'core-backup-shell.actions_title': '可以如何处理',
        'core-backup-shell.actions_help':
          '此处按操作的安全程度排序，而非按可释放的空间排序。标记为破坏性的操作会删除某项内容的唯一副本。',
        'core-backup-shell.other_title': '其他可能的备份',
        'core-backup-shell.other_help':
          '看起来像备份、但没有任何管理程序处理的目录：升级前创建的 tar.gz、带日期的网站副本或数据库转储。没有任何机制轮换它们，因此它们常是磁盘上最老的大型内容。可能是关键词：每项都只是根据名称和形状做出的猜测，操作前值得检查。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-backup-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-backup-shell.${key}`],
    );
  },
  render() {},
};
