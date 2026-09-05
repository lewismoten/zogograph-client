/* Built-in backup package: safe provider-facing guidance. */
const BackupProviderMessageCard = {
  manifest: Object.freeze({
    id: 'core-backup-provider-message',
    locales: ['core-backup-provider-message.'],
    owner: 'core-backup',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-provider-message.title':
          'Message for your hosting provider',
        'core-backup-provider-message.help':
          'Explains what was measured and requests only safe actions. It deliberately does not ask anyone to delete anything in the backup directory.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-backup/locales/es/provider-message.json',
      hi: 'packages/core-backup/locales/hi/provider-message.json',
      ar: 'packages/core-backup/locales/ar/provider-message.json',
      zh: 'packages/core-backup/locales/zh/provider-message.json',
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      'core-backup-provider-message.' + key,
      {},
      this.manifest.catalogs.en['core-backup-provider-message.' + key],
    );
  },
  render() {},
};
Zogograph.cards.register(BackupProviderMessageCard);
