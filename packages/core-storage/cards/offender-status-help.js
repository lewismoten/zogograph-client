/* Built-in storage package: offender-list new and gone explanations. */
const StorageOffenderStatusHelpCard = {
  manifest: Object.freeze({
    id: 'core-storage-offender-status-help',
    locales: ['core-storage-offender-status-help.'],
    owner: 'core-storage',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-offender-status-help.gone':
          'Not on disk any more, and not deleted through this dashboard — so something else removed it: log rotation, a cron job, or a person.',
        'core-storage-offender-status-help.new':
          'This path has never been recorded before.',
        'core-storage-offender-status-help.back_in_list': 'Back in the list',
        'core-storage-offender-status-help.back_in_list_detail':
          'Recorded before, absent from the previous scan, and back now — it grew past the size that gets a path recorded rather than being created.',
        'core-storage-offender-status-help.still':
          'Still on disk — no longer among the largest',
        'core-storage-offender-status-help.still_detail':
          'The file has not gone anywhere. Something else grew past it, so it dropped out of the paths this scan records.',
        'core-storage-offender-status-help.deleted_here':
          'Deleted from here {{at}}',
        'core-storage-offender-status-help.deleted_here_detail':
          'Deleted through this dashboard, from the action log.',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/offender-status-help.json',
      hi: 'packages/core-storage/locales/hi/offender-status-help.json',
      ar: 'packages/core-storage/locales/ar/offender-status-help.json',
      zh: 'packages/core-storage/locales/zh/offender-status-help.json',
    }),
  }),
  render({ key }, { i18n }) {
    return i18n.t(
      `core-storage-offender-status-help.${key}`,
      {},
      this.manifest.catalogs.en[`core-storage-offender-status-help.${key}`],
    );
  },
};
Zogograph.cards.register(StorageOffenderStatusHelpCard);
