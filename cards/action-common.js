/* Core package: shared action-button labels used by multiple cards. */
window.ZogographActionCommonCard = {
  manifest: Object.freeze({
    id: 'core-action-common',
    locales: ['core-action-common.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-action-common.view': 'View',
        'core-action-common.delete': 'Delete',
        'core-action-common.cancel': 'Cancel',
      }),
      es: Object.freeze({
        'core-action-common.view': 'Ver',
        'core-action-common.delete': 'Eliminar',
        'core-action-common.cancel': 'Cancelar',
      }),
      hi: Object.freeze({
        'core-action-common.view': 'देखें',
        'core-action-common.delete': 'हटाएँ',
        'core-action-common.cancel': 'रद्द करें',
      }),
      ar: Object.freeze({
        'core-action-common.view': 'عرض',
        'core-action-common.delete': 'احذف',
        'core-action-common.cancel': 'إلغاء',
      }),
      zh: Object.freeze({
        'core-action-common.view': '查看',
        'core-action-common.delete': '删除',
        'core-action-common.cancel': '取消',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-action-common.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
