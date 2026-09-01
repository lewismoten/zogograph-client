/* Core card package: this account's database footprint and projected growth. */
window.ZogographAccountDatabaseStorageCard = {
  manifest: Object.freeze({
    id: 'core-account-database-storage',
    locales: ['core-account-database-storage.'],
    helpers: [
      'target',
      'note',
      'section',
      'databaseStorageCard',
      'simpleTable',
      'databaseTableName',
      'tableFactsFor',
      'humanCount',
      'humanBytes',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-account-database-storage.title': 'This account’s database',
        'core-account-database-storage.all_reading_tiers': 'all reading tiers',
        'core-account-database-storage.breakdown': 'Where the space goes',
        'core-account-database-storage.table': 'Table',
        'core-account-database-storage.rows': 'Rows',
        'core-account-database-storage.data': 'Data',
        'core-account-database-storage.indexes': 'Indexes',
        'core-account-database-storage.total': 'Total',
        'core-account-database-storage.index_note':
          'Indexes are counted against the table they belong to. On a busy table an index can be close to half the total, and listing them separately makes every table look cheaper than it is.',
        'core-account-database-storage.safe_export': 'Download safe export',
        'core-account-database-storage.integrity_check':
          'Run full integrity check',
        'core-account-database-storage.rollup_now': 'Roll up old readings now',
        'core-account-database-storage.help':
          'A monitor that fills the disk it is watching has failed in the most embarrassing way available to it. This is what this account’s database holds, what each part of it costs, and — the part that is usually missing — what the file will settle at if nobody touches the settings again. A table set to keep 400 days that has been collecting for thirty is at under a tenth of its eventual size, and nothing else here says so.',
      }),
      es: Object.freeze({
        'core-account-database-storage.title': 'Base de datos de esta cuenta',
        'core-account-database-storage.all_reading_tiers':
          'todos los niveles de lecturas',
        'core-account-database-storage.breakdown': 'Dónde se usa el espacio',
        'core-account-database-storage.table': 'Tabla',
        'core-account-database-storage.rows': 'Filas',
        'core-account-database-storage.data': 'Datos',
        'core-account-database-storage.indexes': 'Índices',
        'core-account-database-storage.total': 'Total',
        'core-account-database-storage.index_note':
          'Los índices se cuentan con la tabla a la que pertenecen. Listarlos por separado hace que cada tabla parezca menor de lo que es.',
        'core-account-database-storage.safe_export':
          'Descargar exportación segura',
        'core-account-database-storage.integrity_check':
          'Ejecutar comprobación de integridad completa',
        'core-account-database-storage.rollup_now':
          'Consolidar lecturas antiguas ahora',
        'core-account-database-storage.help':
          'Un monitor que llena el disco que vigila ha fallado de la forma más embarazosa posible. Esto muestra qué contiene la base de datos de esta cuenta, qué cuesta cada parte y —lo que suele faltar— en qué tamaño se estabilizará el archivo si nadie vuelve a tocar la configuración. Una tabla configurada para conservar 400 días que lleva treinta recopilando está por debajo de una décima parte de su tamaño final, y nada más aquí lo indica.',
      }),
      hi: Object.freeze({
        'core-account-database-storage.title': 'इस खाते का डेटाबेस',
        'core-account-database-storage.all_reading_tiers': 'सभी रीडिंग स्तर',
        'core-account-database-storage.breakdown': 'स्थान कहाँ जाता है',
        'core-account-database-storage.table': 'तालिका',
        'core-account-database-storage.rows': 'पंक्तियाँ',
        'core-account-database-storage.data': 'डेटा',
        'core-account-database-storage.indexes': 'इंडेक्स',
        'core-account-database-storage.total': 'कुल',
        'core-account-database-storage.index_note':
          'इंडेक्स उनकी तालिका के साथ गिने जाते हैं। उन्हें अलग सूचीबद्ध करने पर तालिका वास्तव में जितनी है उससे छोटी लगती है।',
        'core-account-database-storage.safe_export':
          'सुरक्षित निर्यात डाउनलोड करें',
        'core-account-database-storage.integrity_check':
          'पूर्ण इंटीग्रिटी जाँच चलाएँ',
        'core-account-database-storage.rollup_now':
          'पुरानी रीडिंग अभी रोल-अप करें',
        'core-account-database-storage.help':
          'जो मॉनिटर जिस डिस्क को देख रहा है उसे भर दे, वह सबसे शर्मनाक तरीके से विफल हुआ है। यह बताता है इस खाते के डेटाबेस में क्या है, हर भाग की लागत और—जो प्रायः नहीं बताया जाता—सेटिंग फिर न छूने पर फ़ाइल किस आकार पर ठहरेगी। 400 दिन रखने वाली और तीस दिन से संग्रह कर रही तालिका अपने अंतिम आकार के दसवें हिस्से से भी कम है, और यहाँ कुछ और यह नहीं कहता।',
      }),
      ar: Object.freeze({
        'core-account-database-storage.title': 'قاعدة بيانات هذا الحساب',
        'core-account-database-storage.all_reading_tiers':
          'كل مستويات القراءات',
        'core-account-database-storage.breakdown': 'أين تذهب المساحة',
        'core-account-database-storage.table': 'الجدول',
        'core-account-database-storage.rows': 'الصفوف',
        'core-account-database-storage.data': 'البيانات',
        'core-account-database-storage.indexes': 'الفهارس',
        'core-account-database-storage.total': 'الإجمالي',
        'core-account-database-storage.index_note':
          'تُحسب الفهارس مع الجدول الذي تنتمي إليه. إدراجها منفصلة يجعل كل جدول يبدو أصغر من حقيقته.',
        'core-account-database-storage.safe_export': 'نزّل تصديرًا آمنًا',
        'core-account-database-storage.integrity_check':
          'شغّل فحص سلامة كاملًا',
        'core-account-database-storage.rollup_now':
          'جمّع القراءات القديمة الآن',
        'core-account-database-storage.help':
          'المراقب الذي يملأ القرص الذي يراقبه قد فشل بأكثر الطرق إحراجًا المتاحة له. هذا ما تحتفظ به قاعدة بيانات هذا الحساب، وما تكلفه كل قطعة منها، والجزء الذي يغيب غالبًا — إلى أي حجم يستقر الملف إن لم يلمس أحد الإعدادات ثانية. جدول مضبوط للاحتفاظ بـ400 يوم وقد جمع لمدة ثلاثين لا يبلغ عُشر حجمه النهائي، ولا يقول شيء آخر هنا ذلك.',
      }),
      zh: Object.freeze({
        'core-account-database-storage.title': '此帐户的数据库',
        'core-account-database-storage.all_reading_tiers': '所有读数层级',
        'core-account-database-storage.breakdown': '空间去向',
        'core-account-database-storage.table': '表',
        'core-account-database-storage.rows': '行数',
        'core-account-database-storage.data': '数据',
        'core-account-database-storage.indexes': '索引',
        'core-account-database-storage.total': '总计',
        'core-account-database-storage.index_note':
          '索引计入其所属的表。将索引单列会使每个表看起来比实际更小。',
        'core-account-database-storage.safe_export': '下载安全导出',
        'core-account-database-storage.integrity_check': '运行完整完整性检查',
        'core-account-database-storage.rollup_now': '立即汇总旧读数',
        'core-account-database-storage.help':
          '一个填满自己正在监视的磁盘的监控器，是以最尴尬的方式失败了。这里显示此账户数据库保存的内容、每个部分的成本，以及通常缺少的部分——如果以后无人再碰设置，文件会稳定在什么大小。一个设置为保留 400 天、已收集 30 天的表还不到最终大小的十分之一，这里没有其他地方会说明这一点。',
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-account-database-storage.${key}`,
      {},
      this.manifest.catalogs.en[`core-account-database-storage.${key}`],
    );
  },
  render(data, helpers) {
    const {
      target,
      note,
      section,
      databaseStorageCard,
      simpleTable,
      databaseTableName,
      tableFactsFor,
      humanCount,
      humanBytes,
      i18n,
    } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target || !data.account) return;
    const account = data.account;
    const parts = [
      section(
        t('core-account-database-storage.title', {}, 'This account’s database'),
        databaseStorageCard(account.file, {
          projected: account.projected_total,
          known: account.projected_known,
          growth: account.growth_per_year,
        }),
      ),
    ];
    if (!account.measured) parts.push(note(account.why));
    if (account.measured) {
      const rows = [
        ...account.entries
          .filter((entry) => !entry.shares_table || entry.key === 'raw_days')
          .map((entry) => [
            entry.shares_table ? 'vitals' : entry.table,
            entry.shares_table
              ? t(
                  'core-account-database-storage.all_reading_tiers',
                  {},
                  'all reading tiers',
                )
              : entry.label,
          ]),
        ...account.others.map((other) => [other.table, '']),
      ];
      const tables = [...new Set(rows.map(([table]) => table))]
        .map(
          (name) =>
            account.others.find((row) => row.table === name) ||
            tableFactsFor(account, name),
        )
        .filter(Boolean);
      parts.push(
        section(
          t(
            'core-account-database-storage.breakdown',
            {},
            'Where the space goes',
          ),
          simpleTable(
            [
              t('core-account-database-storage.table', {}, 'Table'),
              t('core-account-database-storage.rows', {}, 'Rows'),
              t('core-account-database-storage.data', {}, 'Data'),
              t('core-account-database-storage.indexes', {}, 'Indexes'),
              t('core-account-database-storage.total', {}, 'Total'),
            ],
            tables
              .sort((a, b) => b.total_bytes - a.total_bytes)
              .map((row) => [
                databaseTableName(row.table, false),
                humanCount(row.rows ?? 0),
                humanBytes(row.bytes),
                humanBytes(row.index_bytes),
                humanBytes(row.total_bytes),
              ]),
            ['name', 'size', 'size', 'size', 'size'],
          ),
        ),
      );
      parts.push(
        note(
          t(
            'core-account-database-storage.index_note',
            {},
            'Indexes are counted against the table they belong to.',
          ),
        ),
      );
    }
    target.replaceChildren(...parts);
  },
};
