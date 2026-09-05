/* Built-in backup package: data-quality and directory-detail copy. */
const BackupContentDetailsCard = {
  manifest: Object.freeze({
    id: 'core-backup-content-details',
    locales: ['core-backup-content-details.'],
    owner: 'core-backup',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-content-details.reading_last_walk':
          'Reading the last walk…',
        'core-backup-content-details.unattributed_help':
          "Some of this could not be tied to an account. Nothing under the backup root carried a name matching a directory in /home, and JetBackup's own records were not readable — so it is reported as unattributed rather than assigned to the only account on the machine, which would be a guess.",
        'core-backup-content-details.inferred_accounts_help':
          "Accounts inferred from directory names under the backup root — JetBackup's own metadata was not readable, so these are a reasonable guess rather than its records.",
        'core-backup-content-details.unmeasured_entries_help':
          '{{count}} entries here could not be measured before the walk ran out of time. They are listed with no size rather than omitted. Raise host.walk_seconds and run it again for a complete figure.',
        'core-backup-content-details.at_least': 'at least {{size}}',
        'core-backup-content-details.inside_path': 'Inside {{path}}',
        'core-backup-content-details.other_help':
          'Directories that look like backups but are not managed by anything: a tar.gz somebody made before an upgrade, a dated copy of a site, a database dump. Nothing rotates these, so they stay for ever and are usually the oldest large thing on the disk. Probable is the operative word — each is a guess from the name and shape, and worth looking at before acting.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-backup/locales/es/content-details.json',
      hi: 'packages/core-backup/locales/hi/content-details.json',
      ar: 'packages/core-backup/locales/ar/content-details.json',
      zh: 'packages/core-backup/locales/zh/content-details.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const cardId = 'core-backup-content-details.' + key;
    return i18n.t(cardId, values, this.manifest.catalogs.en[cardId]);
  },
};
Zogograph.cards.register(BackupContentDetailsCard);
