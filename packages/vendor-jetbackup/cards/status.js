/* Built-in JetBackup integration package. */
const JetBackupCard = {
  manifest: Object.freeze({
    id: 'core-jetbackup',
    locales: ['core-jetbackup.'],
    owner: 'core-jetbackup',
    helpers: [
      'note',
      'section',
      'simpleTable',
      'humanTime',
      'humanBytes',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-jetbackup.title': 'JetBackup',
        'core-jetbackup.help':
          'What the backup system says about its own backups is the only place the account and date behind a directory called files0000000005 can be found. It needs the JetBackup command line tool and usually needs running as root.',
        'core-jetbackup.account': 'Account',
        'core-jetbackup.when': 'When',
        'core-jetbackup.type': 'Type',
        'core-jetbackup.destination': 'Destination',
        'core-jetbackup.destinations': 'Destinations',
        'core-jetbackup.name': 'Name',
        'core-jetbackup.size': 'Size',
        'core-jetbackup.path': 'Path',
        'core-jetbackup.not_reported': 'not reported',
        'core-jetbackup.known': '{{count}} backups known to JetBackup',
        'core-jetbackup.empty':
          'JetBackup answered, but listed no backups and no destinations.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/vendor-jetbackup/locales/es/status.json',
      hi: 'packages/vendor-jetbackup/locales/hi/status.json',
      ar: 'packages/vendor-jetbackup/locales/ar/status.json',
      zh: 'packages/vendor-jetbackup/locales/zh/status.json',
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      'core-jetbackup.' + key,
      {},
      this.manifest.catalogs.en['core-jetbackup.' + key],
    );
  },
  render(jet, helpers) {
    const out = document.createDocumentFragment();
    if (jet.backups?.length) {
      out.appendChild(
        helpers.section(
          helpers.i18n.t(
            'core-jetbackup.known',
            { count: jet.backups.length },
            '{{count}} backups known to JetBackup',
          ),
          helpers.simpleTable(
            [
              helpers.i18n.t('core-jetbackup.account', {}, 'Account'),
              helpers.i18n.t('core-jetbackup.when', {}, 'When'),
              helpers.i18n.t('core-jetbackup.type', {}, 'Type'),
              helpers.i18n.t('core-jetbackup.size', {}, 'Size'),
              helpers.i18n.t('core-jetbackup.destination', {}, 'Destination'),
            ],
            jet.backups.map((backup) => [
              backup.account ?? '—',
              backup.created_at ? helpers.humanTime(backup.created_at) : '—',
              backup.type ?? '—',
              backup.bytes
                ? helpers.humanBytes(backup.bytes)
                : helpers.i18n.t(
                    'core-jetbackup.not_reported',
                    {},
                    'not reported',
                  ),
              backup.destination ?? '—',
            ]),
            ['name', null, null, 'size', null],
          ),
        ),
      );
    }
    if (jet.destinations?.length) {
      out.appendChild(
        helpers.section(
          helpers.i18n.t('core-jetbackup.destinations', {}, 'Destinations'),
          helpers.simpleTable(
            [
              helpers.i18n.t('core-jetbackup.name', {}, 'Name'),
              helpers.i18n.t('core-jetbackup.type', {}, 'Type'),
              helpers.i18n.t('core-jetbackup.path', {}, 'Path'),
            ],
            jet.destinations.map((destination) => [
              destination.name ?? '—',
              destination.type ?? '—',
              destination.path ?? '—',
            ]),
            ['name', null, 'name'],
          ),
        ),
      );
    }
    if (!out.childElementCount) {
      out.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-jetbackup.empty',
            {},
            'JetBackup answered, but listed no backups and no destinations.',
          ),
        ),
      );
    }
    return out;
  },
};
Zogograph.cards.register(JetBackupCard);
