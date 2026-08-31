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
