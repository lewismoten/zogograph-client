/* Built-in backup package: availability and disk-growth status. */
const BackupGrowthStatusCard = {
  manifest: Object.freeze({
    id: 'core-backup-growth-status',
    locales: ['core-backup-growth-status.'],
    owner: 'core-backup',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-growth-status.nothing_recorded': 'Nothing recorded.',
        'core-backup-growth-status.this_filesystem': 'this filesystem',
        'core-backup-growth-status.used_space_on':
          '{{percent}}% of the used space on {{filesystem}}',
        'core-backup-growth-status.share_unknown':
          'Share of the disk unknown — no filesystem reading recorded.',
        'core-backup-growth-status.walk_incomplete':
          'The disk walk ran out of time, so this total is a lower bound.',
        'core-backup-growth-status.directories_not_opened':
          '{{count}} directories under the backup roots were never opened.',
        'core-backup-growth-status.day_ago': '24 hours ago',
        'core-backup-growth-status.week_ago': '7 days ago',
        'core-backup-growth-status.grew_over': '{{bytes}} over {{duration}}',
        'core-backup-growth-status.growth': 'Growth',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-backup/locales/es/growth-status.json',
      hi: 'packages/core-backup/locales/hi/growth-status.json',
      ar: 'packages/core-backup/locales/ar/growth-status.json',
      zh: 'packages/core-backup/locales/zh/growth-status.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const cardId = 'core-backup-growth-status.' + key;
    return i18n.t(cardId, values, this.manifest.catalogs.en[cardId]);
  },
};
Zogograph.cards.register(BackupGrowthStatusCard);
