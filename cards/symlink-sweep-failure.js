/* Core card package: grouped symlink-sweep failure explanation. */
window.ZogographSymlinkSweepFailureCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sweep-failure',
    locales: ['core-symlink-sweep-failure.'],
    helpers: ['i18n', 'note'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sweep-failure.message':
          '{{count}} links could not be removed: {{reason}}.',
      }),
      es: Object.freeze({
        'core-symlink-sweep-failure.message':
          'No se pudieron eliminar {{count}} enlaces: {{reason}}.',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-failure.message':
          '{{count}} लिंक नहीं हटाए जा सके: {{reason}}।',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-failure.message':
          'تعذر إزالة {{count}} روابط: {{reason}}.',
      }),
      zh: Object.freeze({
        'core-symlink-sweep-failure.message':
          '无法移除 {{count}} 个链接：{{reason}}。',
      }),
    }),
  }),
  render(input, helpers) {
    const { count, reason } = input;
    const { i18n, note } = helpers;
    return note(
      i18n.t(
        'core-symlink-sweep-failure.message',
        { count, reason },
        `${count} links could not be removed: ${reason}.`,
      ),
    );
  },
};
