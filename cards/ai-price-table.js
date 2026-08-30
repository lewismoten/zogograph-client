/* Core card package: price-record table and its row controls. */
window.ZogographAiPriceTableCard = {
  manifest: Object.freeze({
    id: 'core-ai-price-table',
    locales: ['core-ai-price-table.'],
    helpers: [
      'renderTable',
      'sort',
      'useRecord',
      'editRecord',
      'duplicateRecord',
      'removeRecord',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-price-table.provider': 'Provider',
        'core-ai-price-table.model': 'Model',
        'core-ai-price-table.input': 'In',
        'core-ai-price-table.output': 'Out',
        'core-ai-price-table.cached': 'Cached',
        'core-ai-price-table.in_use': ' in use',
        'core-ai-price-table.use_title':
          'Use this active price record on the Model page.',
        'core-ai-price-table.edit_title':
          'Edit the {{date}} price record for {{model}}',
        'core-ai-price-table.duplicate_title':
          'Duplicate this price record, then choose its next active date.',
        'core-ai-price-table.remove_title':
          'Remove the {{date}} price record for {{model}} permanently.',
      }),
      es: Object.freeze({
        'core-ai-price-table.provider': 'Proveedor',
        'core-ai-price-table.model': 'Modelo',
        'core-ai-price-table.input': 'Entrada',
        'core-ai-price-table.output': 'Salida',
        'core-ai-price-table.cached': 'En caché',
        'core-ai-price-table.in_use': ' en uso',
        'core-ai-price-table.use_title': 'Usar este precio activo en Modelo.',
        'core-ai-price-table.edit_title':
          'Editar el precio de {{model}} del {{date}}',
        'core-ai-price-table.duplicate_title': 'Duplicar este precio.',
        'core-ai-price-table.remove_title':
          'Eliminar el precio de {{model}} del {{date}}.',
      }),
      hi: Object.freeze({
        'core-ai-price-table.provider': 'प्रदाता',
        'core-ai-price-table.model': 'मॉडल',
        'core-ai-price-table.input': 'इनपुट',
        'core-ai-price-table.output': 'आउटपुट',
        'core-ai-price-table.cached': 'कैश',
        'core-ai-price-table.in_use': ' उपयोग में',
        'core-ai-price-table.use_title':
          'मॉडल पृष्ठ पर इस सक्रिय मूल्य का उपयोग करें।',
        'core-ai-price-table.edit_title':
          '{{model}} का {{date}} मूल्य संपादित करें',
        'core-ai-price-table.duplicate_title':
          'इस मूल्य रिकॉर्ड को डुप्लिकेट करें।',
        'core-ai-price-table.remove_title':
          '{{model}} का {{date}} मूल्य हटाएँ।',
      }),
      ar: Object.freeze({
        'core-ai-price-table.provider': 'المزوّد',
        'core-ai-price-table.model': 'النموذج',
        'core-ai-price-table.input': 'الإدخال',
        'core-ai-price-table.output': 'الإخراج',
        'core-ai-price-table.cached': 'المخزن مؤقتًا',
        'core-ai-price-table.in_use': ' قيد الاستخدام',
        'core-ai-price-table.use_title':
          'استخدم سجل السعر النشط في صفحة النموذج.',
        'core-ai-price-table.edit_title': 'حرر سعر {{model}} بتاريخ {{date}}',
        'core-ai-price-table.duplicate_title': 'انسخ سجل السعر هذا.',
        'core-ai-price-table.remove_title':
          'أزل سعر {{model}} بتاريخ {{date}}.',
      }),
      zh: Object.freeze({
        'core-ai-price-table.provider': '提供商',
        'core-ai-price-table.model': '模型',
        'core-ai-price-table.input': '输入',
        'core-ai-price-table.output': '输出',
        'core-ai-price-table.cached': '缓存',
        'core-ai-price-table.in_use': ' 使用中',
        'core-ai-price-table.use_title': '在模型页面使用这条当前价格记录。',
        'core-ai-price-table.edit_title': '编辑 {{date}} 的 {{model}} 价格记录',
        'core-ai-price-table.duplicate_title': '复制此价格记录。',
        'core-ai-price-table.remove_title':
          '永久移除 {{date}} 的 {{model}} 价格记录。',
      }),
    }),
  }),
  render(input, helpers) {
    const {
      renderTable,
      sort,
      useRecord,
      editRecord,
      duplicateRecord,
      removeRecord,
      i18n,
    } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const sortMark = (field) =>
      input.sortState.field === field
        ? input.sortState.direction > 0
          ? ' ↑'
          : ' ↓'
        : '';
    const columns = [
      {
        label: t('core-ai-price-table.provider', {}, 'Provider'),
        render: (row) => row.provider,
        onSort: () => sort('provider'),
        sortMark: sortMark('provider'),
      },
      {
        label: t('core-ai-price-table.model', {}, 'Model'),
        className: 'name',
        onSort: () => sort('model'),
        sortMark: sortMark('model'),
        render: (row) => {
          const span = document.createElement('span');
          span.textContent = row.model;
          if (row.current) {
            const mark = document.createElement('span');
            mark.className = 'band-ok small';
            mark.textContent = t('core-ai-price-table.in_use', {}, ' in use');
            span.appendChild(mark);
          }
          return span;
        },
      },
      ...[
        ['in', 'input', 'In'],
        ['out', 'output', 'Out'],
        ['cached', 'cached', 'Cached'],
      ].map(([field, key, fallback]) => ({
        label: t(`core-ai-price-table.${key}`, {}, fallback),
        className: 'size',
        render: (row) => `${row[field]}¢/M`,
        onSort: () => sort(field),
        sortMark: sortMark(field),
      })),
      {
        label: '',
        className: 'row-actions',
        render: (row) => {
          const wrap = document.createDocumentFragment();
          const button = (
            glyph,
            title,
            action,
            className = 'link icon-action',
          ) => {
            const item = document.createElement('button');
            item.className = className;
            item.textContent = glyph;
            item.title = title;
            item.addEventListener('click', () => action(row));
            wrap.appendChild(item);
          };
          if (row.current) {
            button(
              '✓',
              t(
                'core-ai-price-table.use_title',
                {},
                'Use this active price record on the Model page.',
              ),
              useRecord,
            );
          }
          button(
            '✎',
            t(
              'core-ai-price-table.edit_title',
              { date: row.active_from, model: row.model },
              'Edit the {{date}} price record for {{model}}',
            ),
            editRecord,
          );
          button(
            '⧉',
            t(
              'core-ai-price-table.duplicate_title',
              {},
              'Duplicate this price record, then choose its next active date.',
            ),
            duplicateRecord,
          );
          button(
            '⌫',
            t(
              'core-ai-price-table.remove_title',
              { date: row.active_from, model: row.model },
              'Remove the {{date}} price record for {{model}} permanently.',
            ),
            removeRecord,
            'link danger icon-action',
          );
          return wrap;
        },
      },
    ];
    renderTable(
      input.target,
      input.rows,
      columns,
      null,
      (row) => (row.current ? 'row-ok' : ''),
      (row) => row.key,
    );
  },
};
