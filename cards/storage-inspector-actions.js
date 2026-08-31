/* Core card package: action panel for an inspected storage path. */
window.ZogographStorageInspectorActionsCard = {
  manifest: Object.freeze({
    id: 'core-storage-inspector-actions',
    locales: ['core-storage-inspector-actions.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-inspector-actions.delete_directory':
          'Delete this directory and everything in it',
        'core-storage-inspector-actions.delete_file': 'Delete this file',
        'core-storage-inspector-actions.what_you_can_do':
          'What you can do with it',
      }),
      es: Object.freeze({
        'core-storage-inspector-actions.delete_directory':
          'Eliminar este directorio y todo lo que contiene',
        'core-storage-inspector-actions.delete_file': 'Eliminar este archivo',
        'core-storage-inspector-actions.what_you_can_do':
          'Lo que puedes hacer con ello',
      }),
      hi: Object.freeze({
        'core-storage-inspector-actions.delete_directory':
          'इस निर्देशिका और इसमें मौजूद सब कुछ हटाएँ',
        'core-storage-inspector-actions.delete_file': 'इस फ़ाइल को हटाएँ',
        'core-storage-inspector-actions.what_you_can_do':
          'इसके साथ आप क्या कर सकते हैं',
      }),
      ar: Object.freeze({
        'core-storage-inspector-actions.delete_directory':
          'احذف هذا الدليل وكل ما فيه',
        'core-storage-inspector-actions.delete_file': 'احذف هذا الملف',
        'core-storage-inspector-actions.what_you_can_do': 'ما يمكنك فعله به',
      }),
      zh: Object.freeze({
        'core-storage-inspector-actions.delete_directory':
          '删除此目录及其中的所有内容',
        'core-storage-inspector-actions.delete_file': '删除此文件',
        'core-storage-inspector-actions.what_you_can_do': '你可以对它做什么',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-storage-inspector-actions.${key}`,
      values,
      this.manifest.catalogs.en[`core-storage-inspector-actions.${key}`],
    );
  },
};
