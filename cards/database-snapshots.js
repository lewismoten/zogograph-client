/* Core card package: consistent copies retained with operator notes. */
window.ZogographDatabaseSnapshotsCard = {
  manifest: Object.freeze({
    id: 'core-database-snapshots',
    locales: ['core-database-snapshots.'],
    helpers: [
      'target',
      'note',
      'renderTable',
      'whenSpan',
      'humanBytes',
      'remove',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-database-snapshots.empty':
          'No copies have been made. One is written when a note asks for it — a consistent copy of both databases, for looking at after the readings it is about have been rolled up.',
        'core-database-snapshots.file': 'File',
        'core-database-snapshots.made': 'Made',
        'core-database-snapshots.size': 'Size',
        'core-database-snapshots.delete': 'Delete',
        'core-database-snapshots.delete_confirm':
          'Delete {{name}}?\n\nIt is {{size}} and nothing else has a copy.',
        'core-database-snapshots.removed': 'Removed.',
      }),
      es: Object.freeze({
        'core-database-snapshots.empty':
          'Aún no se han creado copias. Se escribe una cuando una nota lo solicita: una copia coherente de ambas bases de datos.',
        'core-database-snapshots.file': 'Archivo',
        'core-database-snapshots.made': 'Creada',
        'core-database-snapshots.size': 'Tamaño',
        'core-database-snapshots.delete': 'Eliminar',
        'core-database-snapshots.delete_confirm':
          '¿Eliminar {{name}}?\n\nMide {{size}} y no hay ninguna otra copia.',
        'core-database-snapshots.removed': 'Eliminada.',
      }),
      hi: Object.freeze({
        'core-database-snapshots.empty':
          'अभी कोई कॉपी नहीं बनाई गई है। नोट के कहने पर दोनों डेटाबेस की एक सुसंगत कॉपी लिखी जाती है।',
        'core-database-snapshots.file': 'फ़ाइल',
        'core-database-snapshots.made': 'बनाई गई',
        'core-database-snapshots.size': 'आकार',
        'core-database-snapshots.delete': 'हटाएँ',
        'core-database-snapshots.delete_confirm':
          '{{name}} हटाएँ?\n\nयह {{size}} है और इसकी कोई दूसरी प्रति नहीं है।',
        'core-database-snapshots.removed': 'हटा दिया गया।',
      }),
      ar: Object.freeze({
        'core-database-snapshots.empty':
          'لم تُنشأ نسخ بعد. تُكتب نسخة متسقة من قاعدتي البيانات عندما تطلبها ملاحظة.',
        'core-database-snapshots.file': 'الملف',
        'core-database-snapshots.made': 'وقت الإنشاء',
        'core-database-snapshots.size': 'الحجم',
        'core-database-snapshots.delete': 'حذف',
        'core-database-snapshots.delete_confirm':
          'حذف {{name}}؟\n\nحجمه {{size}} ولا توجد له نسخة أخرى.',
        'core-database-snapshots.removed': 'أُزيلت.',
      }),
      zh: Object.freeze({
        'core-database-snapshots.empty':
          '尚未创建副本。备注请求时会写入两个数据库的一致副本。',
        'core-database-snapshots.file': '文件',
        'core-database-snapshots.made': '创建时间',
        'core-database-snapshots.size': '大小',
        'core-database-snapshots.delete': '删除',
        'core-database-snapshots.delete_confirm':
          '要删除 {{name}} 吗？\n\n它是 {{size}}，并且没有其他副本。',
        'core-database-snapshots.removed': '已移除。',
      }),
    }),
  }),

  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-database-snapshots.${key}`,
      values,
      this.manifest.catalogs.en[`core-database-snapshots.${key}`],
    );
  },

  render(data, helpers) {
    const { target, note, renderTable, whenSpan, humanBytes, remove } = helpers;
    const t = (key, values = {}) => this.text(key, values, helpers);
    if (!target) return;
    const files = data.files || [];
    if (!files.length) {
      target.replaceChildren(note(t('empty')));
      return;
    }
    const holder = document.createElement('div');
    holder.id = 'snapshotTable';
    target.replaceChildren(holder);
    renderTable(
      'snapshotTable',
      files,
      [
        {
          label: t('file'),
          className: 'name',
          render: (row) => row.name,
        },
        {
          label: t('made'),
          render: (row) => whenSpan(row.at),
        },
        {
          label: t('size'),
          className: 'size',
          render: (row) => humanBytes(row.bytes),
        },
        {
          label: '',
          className: 'row-actions',
          render: (row) => {
            const button = document.createElement('button');
            button.className = 'link danger';
            button.textContent = t('delete');
            button.disabled = !data.can_change;
            button.addEventListener('click', () => remove(row));
            return button;
          },
        },
      ],
      null,
      null,
      (row) => row.name,
    );
  },
};
