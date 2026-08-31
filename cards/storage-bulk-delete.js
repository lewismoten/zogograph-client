/* Core card package: selected-path deletion controls and result notices. */
window.ZogographStorageBulkDeleteCard = {
  manifest: Object.freeze({
    id: 'core-storage-bulk-delete',
    locales: ['core-storage-bulk-delete.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-bulk-delete.select_row': 'Select {{name}}',
        'core-storage-bulk-delete.delete_selected': 'Delete selected',
        'core-storage-bulk-delete.delete_selected_count':
          'Delete {{count, number}} selected',
        'core-storage-bulk-delete.delete_permanently':
          'Delete {{count, number}} permanently?',
        'core-storage-bulk-delete.delete_more': '…and {{count, number}} more',
        'core-storage-bulk-delete.delete_size': 'That is {{bytes}}',
        'core-storage-bulk-delete.delete_recursive':
          'and {{count, number}} will be emptied recursively',
        'core-storage-bulk-delete.cannot_undo': 'This cannot be undone.',
        'core-storage-bulk-delete.removed':
          'Removed {{count, number}}, freeing {{bytes}}.',
        'core-storage-bulk-delete.remove_failed':
          '{{count, number}} could not be removed: {{detail}}',
      }),
      es: Object.freeze({
        'core-storage-bulk-delete.select_row': 'Seleccionar {{name}}',
        'core-storage-bulk-delete.delete_selected': 'Eliminar selección',
        'core-storage-bulk-delete.delete_selected_count':
          'Eliminar {{count, number}} elementos seleccionados',
        'core-storage-bulk-delete.delete_permanently':
          '¿Eliminar {{count, number}} permanentemente?',
        'core-storage-bulk-delete.delete_more': '…y {{count, number}} más',
        'core-storage-bulk-delete.delete_size': 'Ocupan {{bytes}}',
        'core-storage-bulk-delete.delete_recursive':
          'y se vaciarán {{count, number}} de forma recursiva',
        'core-storage-bulk-delete.cannot_undo':
          'Esta acción no se puede deshacer.',
        'core-storage-bulk-delete.removed':
          'Se eliminaron {{count, number}}, liberando {{bytes}}.',
        'core-storage-bulk-delete.remove_failed':
          'No se pudieron eliminar {{count, number}}: {{detail}}',
      }),
      hi: Object.freeze({
        'core-storage-bulk-delete.select_row': '{{name}} चुनें',
        'core-storage-bulk-delete.delete_selected': 'चयनित हटाएँ',
        'core-storage-bulk-delete.delete_selected_count':
          'चुने गए {{count, number}} हटाएँ',
        'core-storage-bulk-delete.delete_permanently':
          'क्या {{count, number}} को स्थायी रूप से हटाना है?',
        'core-storage-bulk-delete.delete_more': '…और {{count, number}}',
        'core-storage-bulk-delete.delete_size': 'यह {{bytes}} है',
        'core-storage-bulk-delete.delete_recursive':
          'और {{count, number}} को पुनरावर्ती रूप से खाली किया जाएगा',
        'core-storage-bulk-delete.cannot_undo':
          'इसे पूर्ववत नहीं किया जा सकता।',
        'core-storage-bulk-delete.removed':
          '{{count, number}} हटाए गए, {{bytes}} खाली हुए।',
        'core-storage-bulk-delete.remove_failed':
          '{{count, number}} हटाए नहीं जा सके: {{detail}}',
      }),
      ar: Object.freeze({
        'core-storage-bulk-delete.select_row': 'حدد {{name}}',
        'core-storage-bulk-delete.delete_selected': 'احذف المحدد',
        'core-storage-bulk-delete.delete_selected_count':
          'احذف {{count, number}} المحدد',
        'core-storage-bulk-delete.delete_permanently':
          'حذف {{count, number}} نهائيًا؟',
        'core-storage-bulk-delete.delete_more': '…و{{count, number}} أخرى',
        'core-storage-bulk-delete.delete_size': 'حجم ذلك {{bytes}}',
        'core-storage-bulk-delete.delete_recursive':
          'وسيُفرغ {{count, number}} بشكل متكرر',
        'core-storage-bulk-delete.cannot_undo': 'لا يمكن التراجع عن ذلك.',
        'core-storage-bulk-delete.removed':
          'أُزيل {{count, number}}، مع تحرير {{bytes}}.',
        'core-storage-bulk-delete.remove_failed':
          'تعذر إزالة {{count, number}}: {{detail}}',
      }),
      zh: Object.freeze({
        'core-storage-bulk-delete.select_row': '选择 {{name}}',
        'core-storage-bulk-delete.delete_selected': '删除所选项',
        'core-storage-bulk-delete.delete_selected_count':
          '删除所选 {{count, number}} 项',
        'core-storage-bulk-delete.delete_permanently':
          '要永久删除 {{count, number}} 吗？',
        'core-storage-bulk-delete.delete_more':
          '…以及另外 {{count, number}} 项',
        'core-storage-bulk-delete.delete_size': '共 {{bytes}}',
        'core-storage-bulk-delete.delete_recursive':
          '并将递归清空 {{count, number}} 项',
        'core-storage-bulk-delete.cannot_undo': '此操作无法撤销。',
        'core-storage-bulk-delete.removed':
          '已删除 {{count, number}} 项，释放 {{bytes}}。',
        'core-storage-bulk-delete.remove_failed':
          '无法删除 {{count, number}} 项：{{detail}}',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const fallback =
      this.manifest.catalogs.en[`core-storage-bulk-delete.${key}`];
    return i18n.t(`core-storage-bulk-delete.${key}`, values, fallback);
  },
};
