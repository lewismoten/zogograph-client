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
      'note',
      'showFormDialog',
      'forgetRecord',
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
        'core-ai-price-table.filled':
          'Prices for {{model}} filled in. Save on the Model page to keep them.',
        'core-ai-price-table.remove_warning':
          'Remove the {{date}} price record for {{provider}}/{{model}}? This cannot be undone.',
        'core-ai-price-table.remove_dialog': 'Remove price record',
        'core-ai-price-table.remove_action': 'Remove record',
        'core-ai-price-table.removed': 'Removed.',
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
        'core-ai-price-table.filled':
          'Se rellenaron los precios de {{model}}. Guarde en la página Modelo para conservarlos.',
        'core-ai-price-table.remove_warning':
          '¿Eliminar el precio de {{provider}}/{{model}} del {{date}}? No se puede deshacer.',
        'core-ai-price-table.remove_dialog': 'Eliminar precio',
        'core-ai-price-table.remove_action': 'Eliminar precio',
        'core-ai-price-table.removed': 'Eliminado.',
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
        'core-ai-price-table.filled':
          '{{model}} की कीमतें भर दी गईं। उन्हें रखने के लिए मॉडल पृष्ठ पर सहेजें।',
        'core-ai-price-table.remove_warning':
          '{{provider}}/{{model}} का {{date}} मूल्य रिकॉर्ड हटाएं? इसे पूर्ववत नहीं किया जा सकता।',
        'core-ai-price-table.remove_dialog': 'मूल्य रिकॉर्ड हटाएं',
        'core-ai-price-table.remove_action': 'रिकॉर्ड हटाएं',
        'core-ai-price-table.removed': 'हटा दिया गया।',
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
        'core-ai-price-table.filled':
          'مُلئت أسعار {{model}}. احفظ في صفحة النموذج للاحتفاظ بها.',
        'core-ai-price-table.remove_warning':
          'أزل سجل سعر {{provider}}/{{model}} بتاريخ {{date}}؟ لا يمكن التراجع عن ذلك.',
        'core-ai-price-table.remove_dialog': 'إزالة سجل السعر',
        'core-ai-price-table.remove_action': 'إزالة السجل',
        'core-ai-price-table.removed': 'أُزيل.',
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
        'core-ai-price-table.filled':
          '已填入 {{model}} 的价格。请在“模型”页面保存以保留它们。',
        'core-ai-price-table.remove_warning':
          '移除 {{provider}}/{{model}} 在 {{date}} 的价格记录？此操作无法撤销。',
        'core-ai-price-table.remove_dialog': '移除价格记录',
        'core-ai-price-table.remove_action': '移除记录',
        'core-ai-price-table.removed': '已移除。',
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
      note,
      showFormDialog,
      forgetRecord,
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
              (record) =>
                useRecord(
                  record,
                  t(
                    'core-ai-price-table.filled',
                    { model: record.model },
                    'Prices for {{model}} filled in. Save on the Model page to keep them.',
                  ),
                ),
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
            (record) => {
              const warning = note(
                t(
                  'core-ai-price-table.remove_warning',
                  {
                    date: record.active_from,
                    provider: record.provider,
                    model: record.model,
                  },
                  'Remove the {{date}} price record for {{provider}}/{{model}}? This cannot be undone.',
                ),
              );
              showFormDialog(
                t(
                  'core-ai-price-table.remove_dialog',
                  {},
                  'Remove price record',
                ),
                [warning],
                () =>
                  forgetRecord(
                    record,
                    t('core-ai-price-table.removed', {}, 'Removed.'),
                  ),
                t('core-ai-price-table.remove_action', {}, 'Remove record'),
              );
            },
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
