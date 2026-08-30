/* Core card package: the root collector database visible to this account. */
window.ZogographHostDatabaseStorageCard = {
  manifest: Object.freeze({
    id: 'core-host-database-storage',
    locales: ['core-host-database-storage.'],
    helpers: [
      'target',
      'note',
      'section',
      'databaseStorageCard',
      'simpleTable',
      'databaseTableName',
      'humanCount',
      'humanBytes',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-database-storage.unreadable':
          'Present but not readable by this account, or not there at all.',
        'core-host-database-storage.file': 'The file',
        'core-host-database-storage.breakdown': 'Where the space goes',
        'core-host-database-storage.table': 'Table',
        'core-host-database-storage.rows': 'Rows',
        'core-host-database-storage.data': 'Data',
        'core-host-database-storage.indexes': 'Indexes',
        'core-host-database-storage.total': 'Total',
        'core-host-database-storage.help':
          'Written by root’s collector, and read-only from here. This account can usually open it to read and should never be the thing that rewrites it — a rewrite started from here would leave a file the collector could no longer write. Where it cannot be opened at all, that is said rather than shown as zeros.',
      }),
      es: Object.freeze({
        'core-host-database-storage.unreadable':
          'Está presente pero esta cuenta no puede leerla, o no existe.',
        'core-host-database-storage.file': 'El archivo',
        'core-host-database-storage.breakdown': 'Dónde se usa el espacio',
        'core-host-database-storage.table': 'Tabla',
        'core-host-database-storage.rows': 'Filas',
        'core-host-database-storage.data': 'Datos',
        'core-host-database-storage.indexes': 'Índices',
        'core-host-database-storage.total': 'Total',
        'core-host-database-storage.help':
          'La escribe el recolector de root y aquí es de solo lectura. Esta cuenta nunca debe reescribirla; el recolector podría dejar de poder escribir el archivo.',
      }),
      hi: Object.freeze({
        'core-host-database-storage.unreadable':
          'यह मौजूद है लेकिन यह खाता इसे पढ़ नहीं सकता, या यह बिल्कुल मौजूद नहीं है।',
        'core-host-database-storage.file': 'फ़ाइल',
        'core-host-database-storage.breakdown': 'स्थान कहाँ जाता है',
        'core-host-database-storage.table': 'तालिका',
        'core-host-database-storage.rows': 'पंक्तियाँ',
        'core-host-database-storage.data': 'डेटा',
        'core-host-database-storage.indexes': 'इंडेक्स',
        'core-host-database-storage.total': 'कुल',
        'core-host-database-storage.help':
          'इसे root का कलेक्टर लिखता है और यहाँ यह केवल पढ़ने योग्य है। इस खाते को इसे कभी फिर से नहीं लिखना चाहिए।',
      }),
      ar: Object.freeze({
        'core-host-database-storage.unreadable':
          'قد تكون موجودة لكن هذا الحساب لا يستطيع قراءتها، أو قد لا تكون موجودة أصلًا.',
        'core-host-database-storage.file': 'الملف',
        'core-host-database-storage.breakdown': 'أين تذهب المساحة',
        'core-host-database-storage.table': 'الجدول',
        'core-host-database-storage.rows': 'الصفوف',
        'core-host-database-storage.data': 'البيانات',
        'core-host-database-storage.indexes': 'الفهارس',
        'core-host-database-storage.total': 'الإجمالي',
        'core-host-database-storage.help':
          'يكتبها جامع root، وهي للقراءة فقط من هنا. لا ينبغي لهذا الحساب إعادة كتابتها أبدًا.',
      }),
      zh: Object.freeze({
        'core-host-database-storage.unreadable':
          '该数据库存在但此帐户无法读取，或者根本不存在。',
        'core-host-database-storage.file': '文件',
        'core-host-database-storage.breakdown': '空间去向',
        'core-host-database-storage.table': '表',
        'core-host-database-storage.rows': '行数',
        'core-host-database-storage.data': '数据',
        'core-host-database-storage.indexes': '索引',
        'core-host-database-storage.total': '总计',
        'core-host-database-storage.help':
          '该数据库由 root 采集器写入，在此处为只读。此帐户不应重写它。',
      }),
    }),
  }),

  render(data, helpers) {
    const {
      target,
      note,
      section,
      databaseStorageCard,
      simpleTable,
      databaseTableName,
      humanCount,
      humanBytes,
      i18n,
    } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!data.host) {
      target.replaceChildren(
        note(
          data.host_why ||
            t(
              'core-host-database-storage.unreadable',
              {},
              'Present but not readable by this account, or not there at all.',
            ),
        ),
      );
      return;
    }
    const parts = [
      section(
        t('core-host-database-storage.file', {}, 'The file'),
        databaseStorageCard(data.host.file, null),
      ),
    ];
    if (!data.host.measured) parts.push(note(data.host.why));
    else {
      const tables = Object.entries(data.host.tables)
        .map(([name, facts]) => ({ table: name, ...facts }))
        .sort((a, b) => b.total_bytes - a.total_bytes);
      parts.push(
        section(
          t('core-host-database-storage.breakdown', {}, 'Where the space goes'),
          simpleTable(
            [
              t('core-host-database-storage.table', {}, 'Table'),
              t('core-host-database-storage.rows', {}, 'Rows'),
              t('core-host-database-storage.data', {}, 'Data'),
              t('core-host-database-storage.indexes', {}, 'Indexes'),
              t('core-host-database-storage.total', {}, 'Total'),
            ],
            tables.map((row) => [
              databaseTableName(row.table, true),
              humanCount(row.rows ?? 0),
              humanBytes(row.bytes),
              humanBytes(row.index_bytes),
              humanBytes(row.total_bytes),
            ]),
            ['name', 'size', 'size', 'size', 'size'],
          ),
        ),
      );
    }
    parts.push(
      note(
        t(
          'core-host-database-storage.help',
          {},
          'Written by root’s collector, and read-only from here.',
        ),
      ),
    );
    target.replaceChildren(...parts);
  },
};
