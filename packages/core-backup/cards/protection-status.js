/* Built-in backup package: measurement and action-safety labels. */
const BackupProtectionStatusCard = {
  manifest: Object.freeze({
    id: 'core-backup-protection-status',
    locales: ['core-backup-protection-status.'],
    owner: 'core-backup',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-protection-status.not_measured': 'not measured',
        'core-backup-protection-status.matched': 'Matched',
        'core-backup-protection-status.reach_here': 'you can do this here',
        'core-backup-protection-status.reach_root': 'needs root or WHM',
        'core-backup-protection-status.safe': 'Safe',
        'core-backup-protection-status.review': 'Review',
        'core-backup-protection-status.dangerous': 'Dangerous',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-backup/locales/es/protection-status.json',
      hi: 'packages/core-backup/locales/hi/protection-status.json',
      ar: 'packages/core-backup/locales/ar/protection-status.json',
      zh: 'packages/core-backup/locales/zh/protection-status.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const cardId = 'core-backup-protection-status.' + key;
    return i18n.t(cardId, values, this.manifest.catalogs.en[cardId]);
  },
};
Zogograph.cards.register(BackupProtectionStatusCard);
