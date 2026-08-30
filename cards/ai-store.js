/* Core card package: the assistant transcript, data, and asset store. */
window.ZogographAiStoreCard = {
  manifest: Object.freeze({
    id: 'core-ai-store',
    locales: ['core-ai-store.'],
    helpers: [
      'body',
      'assistant',
      'compactButton',
      'note',
      'section',
      'databaseStorageCard',
      'aiForgetForm',
      'loadAiAssets',
      'renderTable',
      'humanCount',
      'humanBytes',
      'ago',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-store.empty':
          'Nothing has been asked yet, so {{assistant}} has no database. One is made the first time a conversation starts, next to the account’s own.',
        'core-ai-store.file': 'The file',
        'core-ai-store.holds': 'What {{assistant}} is keeping',
        'core-ai-store.space': 'Where the space goes',
        'core-ai-store.pictures': 'Pictures it has made',
        'core-ai-store.forget': 'Forget old conversations',
        'core-ai-store.rows': 'Rows',
        'core-ai-store.oldest': 'Oldest',
        'core-ai-store.newest': 'Newest',
        'core-ai-store.what': 'What it is',
        'core-ai-store.table': 'Table',
        'core-ai-store.data': 'Data',
        'core-ai-store.indexes': 'Indexes',
        'core-ai-store.total': 'Total',
      }),
      es: Object.freeze({
        'core-ai-store.empty':
          'Aún no se ha preguntado nada, así que {{assistant}} no tiene base de datos.',
        'core-ai-store.file': 'El archivo',
        'core-ai-store.holds': 'Lo que conserva {{assistant}}',
        'core-ai-store.space': 'Dónde va el espacio',
        'core-ai-store.pictures': 'Imágenes que ha creado',
        'core-ai-store.forget': 'Olvidar conversaciones antiguas',
        'core-ai-store.rows': 'Filas',
        'core-ai-store.oldest': 'Más antigua',
        'core-ai-store.newest': 'Más reciente',
        'core-ai-store.what': 'Qué es',
        'core-ai-store.table': 'Tabla',
        'core-ai-store.data': 'Datos',
        'core-ai-store.indexes': 'Índices',
        'core-ai-store.total': 'Total',
      }),
      hi: Object.freeze({
        'core-ai-store.empty':
          'अभी कुछ नहीं पूछा गया है, इसलिए {{assistant}} का डेटाबेस नहीं है।',
        'core-ai-store.file': 'फ़ाइल',
        'core-ai-store.holds': '{{assistant}} क्या रख रहा है',
        'core-ai-store.space': 'जगह कहाँ जाती है',
        'core-ai-store.pictures': 'इसके बनाए चित्र',
        'core-ai-store.forget': 'पुरानी बातचीत भूलें',
        'core-ai-store.rows': 'पंक्तियाँ',
        'core-ai-store.oldest': 'सबसे पुराना',
        'core-ai-store.newest': 'सबसे नया',
        'core-ai-store.what': 'यह क्या है',
        'core-ai-store.table': 'तालिका',
        'core-ai-store.data': 'डेटा',
        'core-ai-store.indexes': 'इंडेक्स',
        'core-ai-store.total': 'कुल',
      }),
      ar: Object.freeze({
        'core-ai-store.empty':
          'لم يُسأل شيء بعد، لذا لا توجد قاعدة بيانات لـ {{assistant}}.',
        'core-ai-store.file': 'الملف',
        'core-ai-store.holds': 'ما يحتفظ به {{assistant}}',
        'core-ai-store.space': 'أين تذهب المساحة',
        'core-ai-store.pictures': 'الصور التي أنشأها',
        'core-ai-store.forget': 'انسَ المحادثات القديمة',
        'core-ai-store.rows': 'الصفوف',
        'core-ai-store.oldest': 'الأقدم',
        'core-ai-store.newest': 'الأحدث',
        'core-ai-store.what': 'ما هو',
        'core-ai-store.table': 'الجدول',
        'core-ai-store.data': 'البيانات',
        'core-ai-store.indexes': 'الفهارس',
        'core-ai-store.total': 'الإجمالي',
      }),
      zh: Object.freeze({
        'core-ai-store.empty':
          '尚未提出任何问题，因此 {{assistant}} 尚无数据库。',
        'core-ai-store.file': '文件',
        'core-ai-store.holds': '{{assistant}} 保留的内容',
        'core-ai-store.space': '空间去向',
        'core-ai-store.pictures': '它创建的图片',
        'core-ai-store.forget': '删除旧对话',
        'core-ai-store.rows': '行',
        'core-ai-store.oldest': '最早',
        'core-ai-store.newest': '最新',
        'core-ai-store.what': '内容',
        'core-ai-store.table': '表',
        'core-ai-store.data': '数据',
        'core-ai-store.indexes': '索引',
        'core-ai-store.total': '总计',
      }),
    }),
  }),
  render(data, helpers) {
    const {
      body,
      assistant,
      compactButton,
      note,
      section,
      databaseStorageCard,
      aiForgetForm,
      loadAiAssets,
      renderTable,
      humanCount,
      humanBytes,
      ago,
      i18n,
    } = helpers;
    const who = data.who?.name ?? assistant;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    if (!data.file.exists) {
      body.replaceChildren(
        note(
          t(
            'core-ai-store.empty',
            { assistant: who },
            'Nothing has been asked yet, so {{assistant}} has no database. One is made the first time a conversation starts, next to the account’s own.',
          ),
        ),
      );
      compactButton.hidden = true;
      return;
    }
    compactButton.hidden = false;
    const holds = document.createElement('div');
    holds.id = 'aiHoldsTable';
    const parts = [
      section(
        t('core-ai-store.file', {}, 'The file'),
        databaseStorageCard(data.file, null),
      ),
    ];
    if (data.why) parts.push(note(data.why));
    parts.push(
      section(
        t(
          'core-ai-store.holds',
          { assistant: who },
          'What {{assistant}} is keeping',
        ),
        holds,
      ),
    );
    if (data.measured) {
      const tables = document.createElement('div');
      tables.id = 'aiTablesTable';
      parts.push(
        section(t('core-ai-store.space', {}, 'Where the space goes'), tables),
      );
    } else if (data.table_why) parts.push(note(data.table_why));
    const gallery = document.createElement('div');
    gallery.id = 'aiAssets';
    parts.push(
      section(t('core-ai-store.pictures', {}, 'Pictures it has made'), gallery),
      section(
        t('core-ai-store.forget', {}, 'Forget old conversations'),
        aiForgetForm(who),
      ),
    );
    body.replaceChildren(...parts);
    loadAiAssets().catch(() => {});
    renderTable(
      'aiHoldsTable',
      data.holds,
      [
        { label: '', render: (row) => row.label },
        {
          label: t('core-ai-store.rows', {}, 'Rows'),
          className: 'size',
          render: (row) => humanCount(row.rows),
        },
        {
          label: t('core-ai-store.oldest', {}, 'Oldest'),
          render: (row) => (row.oldest ? ago(row.oldest) : '—'),
        },
        {
          label: t('core-ai-store.newest', {}, 'Newest'),
          render: (row) => (row.newest ? ago(row.newest) : '—'),
        },
        {
          label: t('core-ai-store.what', {}, 'What it is'),
          className: 'muted',
          render: (row) => row.note,
        },
      ],
      null,
      null,
      (row) => row.key,
    );
    if (!data.measured) return;
    const tableRows = Object.entries(data.tables)
      .map(([table, facts]) => ({ table, ...facts }))
      .sort((left, right) => right.total_bytes - left.total_bytes);
    renderTable(
      'aiTablesTable',
      tableRows,
      [
        {
          label: t('core-ai-store.table', {}, 'Table'),
          className: 'name',
          render: (row) => row.table,
        },
        {
          label: t('core-ai-store.rows', {}, 'Rows'),
          className: 'size',
          render: (row) => humanCount(row.rows),
        },
        {
          label: t('core-ai-store.data', {}, 'Data'),
          className: 'size',
          render: (row) => humanBytes(row.bytes),
        },
        {
          label: t('core-ai-store.indexes', {}, 'Indexes'),
          className: 'size',
          render: (row) => humanBytes(row.index_bytes),
        },
        {
          label: t('core-ai-store.total', {}, 'Total'),
          className: 'size',
          render: (row) => humanBytes(row.total_bytes),
        },
      ],
      null,
      null,
      (row) => row.table,
    );
  },
};
