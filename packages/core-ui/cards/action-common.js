/* Shared action copy primitive. */
const ActionCommonCard = {
  manifest: Object.freeze({
    id: 'core-action-common',
    locales: ['core-action-common.'],
    owner: 'core-ui',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-action-common.view': 'View',
        'core-action-common.close_all': 'Close all',
        'core-action-common.open_all': 'Open all',
        'core-action-common.delete': 'Delete',
        'core-action-common.cancel': 'Cancel',
        'core-action-common.open': 'Open',
        'core-action-common.edit': 'Edit',
        'core-action-common.forget': 'Forget',
        'core-action-common.save': 'Save it',
        'core-action-common.add': 'Add it',
        'core-action-common.close': 'Close',
        'core-action-common.running': 'Running…',
        'core-action-common.looking': 'Looking…',
        'core-action-common.checking': 'Checking…',
        'core-action-common.run': 'Run it',
        'core-action-common.stop': 'Stop it',
        'core-action-common.show': 'Show it',
        'core-action-common.copy': 'Copy',
        'core-action-common.copied': 'Copied',
        'core-action-common.clear': 'Clear',
        'core-action-common.continue': 'Continue',
        'core-action-common.details': 'Details',
        'core-action-common.copy_empty': 'There is nothing to copy.',
        'core-action-common.copy_done': 'Copied {{amount}}.',
        'core-action-common.copy_refused':
          'This browser would not let the page write to the clipboard.',
        'core-action-common.check_now': 'Check now',
        'core-action-common.rewriting': 'Rewriting…',
        'core-action-common.rolling_up': 'Rolling up…',
        'core-action-common.skip': 'Skip',
        'core-action-common.rename': 'Rename',
        'core-action-common.remove': 'Remove',
        'core-action-common.go_there': 'Go there',
        'core-action-common.reclaim_empty_space': 'Reclaim empty space',
        'core-action-common.run_full_integrity_check':
          'Run full integrity check',
        'core-action-common.roll_up_old_readings': 'Roll up old readings now',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-ui/locales/es/action-common.json',
      hi: 'packages/core-ui/locales/hi/action-common.json',
      ar: 'packages/core-ui/locales/ar/action-common.json',
      zh: 'packages/core-ui/locales/zh/action-common.json',
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-action-common.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
Zogograph.cards.register(ActionCommonCard);
