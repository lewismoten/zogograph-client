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
        'core-action-common.open': 'Open',
        'core-action-common.edit': 'Edit',
        'core-action-common.forget': 'Forget',
        'core-action-common.save': 'Save it',
        'core-action-common.add': 'Add it',
        'core-action-common.close': 'Close',
      }),
      es: Object.freeze({
        'core-action-common.view': 'Ver',
        'core-action-common.delete': 'Eliminar',
        'core-action-common.cancel': 'Cancelar',
        'core-action-common.open': 'Abrir',
        'core-action-common.edit': 'Editar',
        'core-action-common.forget': 'Olvidar',
        'core-action-common.save': 'Guardar',
        'core-action-common.add': 'Añadir',
        'core-action-common.close': 'Cerrar',
      }),
      hi: Object.freeze({
        'core-action-common.view': 'देखें',
        'core-action-common.delete': 'हटाएँ',
        'core-action-common.cancel': 'रद्द करें',
        'core-action-common.open': 'खोलें',
        'core-action-common.edit': 'संपादित करें',
        'core-action-common.forget': 'भूलें',
        'core-action-common.save': 'सहेजें',
        'core-action-common.add': 'जोड़ें',
        'core-action-common.close': 'बंद करें',
      }),
      ar: Object.freeze({
        'core-action-common.view': 'عرض',
        'core-action-common.delete': 'احذف',
        'core-action-common.cancel': 'إلغاء',
        'core-action-common.open': 'فتح',
        'core-action-common.edit': 'حرّر',
        'core-action-common.forget': 'انسَ',
        'core-action-common.save': 'احفظ',
        'core-action-common.add': 'أضف',
        'core-action-common.close': 'إغلاق',
      }),
      zh: Object.freeze({
        'core-action-common.view': '查看',
        'core-action-common.delete': '删除',
        'core-action-common.cancel': '取消',
        'core-action-common.open': '打开',
        'core-action-common.edit': '编辑',
        'core-action-common.forget': '忘记',
        'core-action-common.save': '保存',
        'core-action-common.add': '添加',
        'core-action-common.close': '关闭',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-action-common.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
