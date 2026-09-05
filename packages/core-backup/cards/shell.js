/* Built-in backup package: static discovery and recovery shell copy. */
const BackupShellCard = {
  manifest: Object.freeze({
    id: 'core-backup-shell',
    locales: ['core-backup-shell.'],
    owner: 'core-backup',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-shell.storage_title': 'Backup storage',
        'core-backup-shell.storage_help':
          'Backups are usually the largest thing on a server and the last place anybody looks, because they are not in /home. This asks four questions: how much space, whose, how old, and what is safe to do about it.',
        'core-backup-shell.actions_title': 'What can be done about it',
        'core-backup-shell.actions_help':
          'Sorted by how safe each one is, not by how much it would free. Anything marked destructive removes the only copy of something — the whole point of a backup is that it is there on the day nobody expected to need it.',
        'core-backup-shell.other_title': 'Other probable backups',
        'core-backup-shell.other_help':
          'Directories that look like backups but are not managed by anything: a tar.gz somebody made before an upgrade, a dated copy of a site, a database dump. Nothing rotates these, so they stay for ever and are usually the oldest large thing on the disk. Probable is the operative word — each is a guess from the name and shape, and worth looking at before acting.',
        'core-backup-shell.path': 'Path',
        'core-backup-shell.size': 'Size',
        'core-backup-shell.files': 'Files',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-backup/locales/es/shell.json',
      hi: 'packages/core-backup/locales/hi/shell.json',
      ar: 'packages/core-backup/locales/ar/shell.json',
      zh: 'packages/core-backup/locales/zh/shell.json',
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      'core-backup-shell.' + key,
      {},
      this.manifest.catalogs.en['core-backup-shell.' + key],
    );
  },
  render() {},
};
Zogograph.cards.register(BackupShellCard);
