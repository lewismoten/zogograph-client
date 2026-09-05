/* Built-in backup package: action guidance labels. */
const BackupActionGuidanceCard = {
  manifest: Object.freeze({
    id: 'core-backup-action-guidance',
    locales: ['core-backup-action-guidance.'],
    owner: 'core-backup',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-action-guidance.no_suggestions':
          'Nothing to suggest from this reading.',
        'core-backup-action-guidance.reach_provider':
          'ask your hosting provider',
        'core-backup-action-guidance.safe_actions':
          'Safe — returns space the backup system already considers disposable',
        'core-backup-action-guidance.review_actions':
          'Worth reviewing — a decision, not a chore',
        'core-backup-action-guidance.dangerous_actions':
          'Dangerous — listed so it can be named, not recommended',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-backup/locales/es/action-guidance.json',
      hi: 'packages/core-backup/locales/hi/action-guidance.json',
      ar: 'packages/core-backup/locales/ar/action-guidance.json',
      zh: 'packages/core-backup/locales/zh/action-guidance.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const cardId = 'core-backup-action-guidance.' + key;
    return i18n.t(cardId, values, this.manifest.catalogs.en[cardId]);
  },
};
Zogograph.cards.register(BackupActionGuidanceCard);
