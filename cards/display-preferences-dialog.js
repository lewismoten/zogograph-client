/* Core card package: display-preferences dialog title and submit control. */
window.ZogographDisplayPreferencesDialogCard = {
  manifest: Object.freeze({
    id: 'core-display-preferences-dialog',
    locales: ['core-display-preferences-dialog.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-display-preferences-dialog.preferences': 'Display preferences',
        'core-display-preferences-dialog.apply': 'Apply display choices',
      }),
      es: Object.freeze({
        'core-display-preferences-dialog.preferences':
          'Preferencias de visualización',
        'core-display-preferences-dialog.apply':
          'Aplicar opciones de visualización',
      }),
      hi: Object.freeze({
        'core-display-preferences-dialog.preferences': 'डिस्प्ले प्राथमिकताएँ',
        'core-display-preferences-dialog.apply': 'डिस्प्ले विकल्प लागू करें',
      }),
      ar: Object.freeze({
        'core-display-preferences-dialog.preferences': 'تفضيلات العرض',
        'core-display-preferences-dialog.apply': 'طبّق خيارات العرض',
      }),
      zh: Object.freeze({
        'core-display-preferences-dialog.preferences': '显示首选项',
        'core-display-preferences-dialog.apply': '应用显示选项',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-display-preferences-dialog.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
