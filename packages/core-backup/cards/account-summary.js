/* Built-in backup package: ownership and age summary table. */
const BackupAccountSummaryCard = {
  manifest: Object.freeze({
    id: 'core-backup-account-summary',
    locales: ['core-backup-account-summary.'],
    owner: 'core-backup',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-account-summary.account': 'Account',
        'core-backup-account-summary.live_data': 'Live data',
        'core-backup-account-summary.backups': 'Backups',
        'core-backup-account-summary.ratio': 'Ratio',
        'core-backup-account-summary.copies': 'Copies',
        'core-backup-account-summary.oldest': 'Oldest',
        'core-backup-account-summary.newest': 'Newest',
        'core-backup-account-summary.not_attributed': 'not attributed',
        'core-backup-account-summary.by_account': 'By account',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-backup/locales/es/account-summary.json',
      hi: 'packages/core-backup/locales/hi/account-summary.json',
      ar: 'packages/core-backup/locales/ar/account-summary.json',
      zh: 'packages/core-backup/locales/zh/account-summary.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const cardId = 'core-backup-account-summary.' + key;
    return i18n.t(cardId, values, this.manifest.catalogs.en[cardId]);
  },
};
Zogograph.cards.register(BackupAccountSummaryCard);
