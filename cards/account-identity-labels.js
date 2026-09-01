/* Core card package: account identity and ownership labels. */
window.ZogographAccountIdentityLabelsCard = {
  manifest: Object.freeze({
    id: 'core-account-identity-labels',
    locales: ['core-account-identity-labels.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-account-identity-labels.this_account':
          'This account — the one this dashboard runs as.',
        'core-account-identity-labels.root': 'root',
        'core-account-identity-labels.root_unmanaged':
          'root. This dashboard cannot stop or change these.',
      }),
      es: Object.freeze({
        'core-account-identity-labels.this_account':
          'Esta cuenta — con la que se ejecuta este panel.',
        'core-account-identity-labels.root': 'root',
        'core-account-identity-labels.root_unmanaged':
          'root. Este panel no puede detener ni cambiar estos procesos.',
      }),
      hi: Object.freeze({
        'core-account-identity-labels.this_account':
          'यह खाता — जिससे यह डैशबोर्ड चलता है।',
        'core-account-identity-labels.root': 'root',
        'core-account-identity-labels.root_unmanaged':
          'root. यह डैशबोर्ड इन्हें रोक या बदल नहीं सकता।',
      }),
      ar: Object.freeze({
        'core-account-identity-labels.this_account':
          'هذا الحساب — الذي تعمل لوحة المعلومات باسمه.',
        'core-account-identity-labels.root': 'root',
        'core-account-identity-labels.root_unmanaged':
          'root. لا تستطيع لوحة المعلومات هذه إيقاف هذه العناصر أو تغييرها.',
      }),
      zh: Object.freeze({
        'core-account-identity-labels.this_account':
          '此帐户——运行此仪表板的帐户。',
        'core-account-identity-labels.root': 'root',
        'core-account-identity-labels.root_unmanaged':
          'root。此仪表板无法停止或更改这些项目。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-account-identity-labels.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
