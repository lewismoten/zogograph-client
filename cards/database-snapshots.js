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
      }),
      es: Object.freeze({
        'core-database-snapshots.empty':
          'Aún no se han creado copias. Se escribe una cuando una nota lo solicita: una copia coherente de ambas bases de datos.',
        'core-database-snapshots.file': 'Archivo',
        'core-database-snapshots.made': 'Creada',
        'core-database-snapshots.size': 'Tamaño',
        'core-database-snapshots.delete': 'Eliminar',
      }),
      hi: Object.freeze({
        'core-database-snapshots.empty':
          'अभी कोई कॉपी नहीं बनाई गई है। नोट के कहने पर दोनों डेटाबेस की एक सुसंगत कॉपी लिखी जाती है।',
        'core-database-snapshots.file': 'फ़ाइल',
        'core-database-snapshots.made': 'बनाई गई',
        'core-database-snapshots.size': 'आकार',
        'core-database-snapshots.delete': 'हटाएँ',
      }),
      ar: Object.freeze({
        'core-database-snapshots.empty':
          'لم تُنشأ نسخ بعد. تُكتب نسخة متسقة من قاعدتي البيانات عندما تطلبها ملاحظة.',
        'core-database-snapshots.file': 'الملف',
        'core-database-snapshots.made': 'وقت الإنشاء',
        'core-database-snapshots.size': 'الحجم',
        'core-database-snapshots.delete': 'حذف',
      }),
      zh: Object.freeze({
        'core-database-snapshots.empty':
          '尚未创建副本。备注请求时会写入两个数据库的一致副本。',
        'core-database-snapshots.file': '文件',
        'core-database-snapshots.made': '创建时间',
        'core-database-snapshots.size': '大小',
        'core-database-snapshots.delete': '删除',
      }),
    }),
  }),

  render(data, helpers) {
    const { target, note, renderTable, whenSpan, humanBytes, remove, i18n } =
      helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    const files = data.files || [];
    if (!files.length) {
      target.replaceChildren(
        note(
          t('core-database-snapshots.empty', {}, 'No copies have been made.'),
        ),
      );
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
          label: t('core-database-snapshots.file', {}, 'File'),
          className: 'name',
          render: (row) => row.name,
        },
        {
          label: t('core-database-snapshots.made', {}, 'Made'),
          render: (row) => whenSpan(row.at),
        },
        {
          label: t('core-database-snapshots.size', {}, 'Size'),
          className: 'size',
          render: (row) => humanBytes(row.bytes),
        },
        {
          label: '',
          className: 'row-actions',
          render: (row) => {
            const button = document.createElement('button');
            button.className = 'link danger';
            button.textContent = t(
              'core-database-snapshots.delete',
              {},
              'Delete',
            );
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
