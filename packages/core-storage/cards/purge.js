/* Built-in storage package: retention-row clearing dialog copy. */
const StoragePurgeCard = {
  manifest: Object.freeze({
    id: 'core-storage-purge',
    locales: ['core-storage-purge.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-purge.button_title':
          'Delete {{label}} older than a given age',
        'core-storage-purge.title': 'Clear old records',
        'core-storage-purge.age_in_days': 'Age in days',
        'core-storage-purge.invalid_days': 'That is not a number of days.',
        'core-storage-purge.zero_help': '0 deletes all {{label}}.{{protected}}',
        'core-storage-purge.protected_help':
          ' Readings near an alert are always kept.',
        'core-storage-purge.confirm':
          'Delete every one of {{label}}?\n\n{{why}}\n\nThis cannot be undone.',
        'core-storage-purge.removed':
          '{{count}} removed from {{label}}.{{protected}}',
        'core-storage-purge.protected':
          ' {{count}} kept because they sit beside an alert.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/purge.json',
      hi: 'packages/core-storage/locales/hi/purge.json',
      ar: 'packages/core-storage/locales/ar/purge.json',
      zh: 'packages/core-storage/locales/zh/purge.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const fallback = this.manifest.catalogs.en[`core-storage-purge.${key}`];
    return i18n.t(`core-storage-purge.${key}`, values, fallback);
  },
};
Zogograph.cards.register(StoragePurgeCard);
