/* Built-in storage package: account database retention table copy. */
const StorageRetentionCard = {
  manifest: Object.freeze({
    id: 'core-storage-retention',
    locales: ['core-storage-retention.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-retention.title': 'How long it keeps things',
        'core-storage-retention.recommended_settings':
          'Use the recommended settings',
        'core-storage-retention.help':
          'Zero means {{forever}}, and for several of these that is the recommendation rather than an oversight — the note beside each one says what it is protecting. Readings are kept in tiers: full detail for the first stretch, then five-minute buckets, then hourly, then daily, each holding the worst moment in its bucket rather than an average. Nothing within a couple of hours of an alert is ever rolled up or cleared, whatever these say.',
        'core-storage-retention.not_writable':
          'lib/config.php is not writable by the web server, so these cannot be changed from here. Edit the file directly, or change it in setup.php.',
        'core-storage-retention.what': 'What',
        'core-storage-retention.holding_now': 'Holding now',
        'core-storage-retention.covers': 'Covers',
        'core-storage-retention.keep_for': 'Keep for',
        'core-storage-retention.recommended': 'Recommended',
        'core-storage-retention.settles_at': 'Settles at',
        'core-storage-retention.days_hint': 'Days. 0 means keep for ever.',
        'core-storage-retention.for_ever': 'for ever',
        'core-storage-retention.days_value': '{{days}} days',
        'core-storage-retention.recommended_marker': '(recommended)',
        'core-storage-retention.forever_recommended_help':
          'Keeping this for ever is the recommendation, not an oversight. {{why}}',
        'core-storage-retention.settled_now':
          'Already holding its full window, so this is roughly what it is now.',
        'core-storage-retention.grows_to':
          'Not full yet. This is what it grows to once it has been collecting long enough.',
        'core-storage-retention.forever_growth':
          'Kept for ever, so it does not settle. This is what a year of it costs.',
        'core-storage-retention.no_history':
          'Not enough history yet to measure a rate.',
        'core-storage-retention.total_settles':
          'Left as it is, the database settles near {{amount}}.',
        'core-storage-retention.total_grows':
          'Left as it is, the database settles near {{amount}} plus about {{growth}} a year from the things set to be kept for ever.',
        'core-storage-retention.shared_table_help':
          'These tiers share one table, so their sizes are parts of it rather than separate files.',
        'core-storage-retention.rate_per_year': 'per year',
        'core-storage-retention.yearly_growth': '+{{amount}} {{period}}',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/retention.json',
      hi: 'packages/core-storage/locales/hi/retention.json',
      ar: 'packages/core-storage/locales/ar/retention.json',
      zh: 'packages/core-storage/locales/zh/retention.json',
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      'core-storage-retention.' + key,
      {},
      this.manifest.catalogs.en['core-storage-retention.' + key],
    );
  },
  render({ key, values = {} }, { i18n }) {
    const fallback = this.manifest.catalogs.en['core-storage-retention.' + key];
    return i18n.t('core-storage-retention.' + key, values, fallback);
  },
};
Zogograph.cards.register(StorageRetentionCard);
