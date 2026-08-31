/* Core card package: bounded symlink-sweep list notice. */
window.ZogographSymlinkSweepShowingFirstCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sweep-showing-first',
    locales: ['core-symlink-sweep-showing-first.'],
    helpers: ['i18n', 'note'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sweep-showing-first.message':
          'Showing the first 200 of {{count}}.',
      }),
      es: Object.freeze({
        'core-symlink-sweep-showing-first.message':
          'Se muestran los primeros 200 de {{count}}.',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-showing-first.message':
          '{{count}} में से पहले 200 दिखाए जा रहे हैं।',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-showing-first.message':
          'يُعرض أول 200 من {{count}}.',
      }),
      zh: Object.freeze({
        'core-symlink-sweep-showing-first.message':
          '显示 {{count}} 个中的前 200 个。',
      }),
    }),
  }),
  render(input, helpers) {
    const { count } = input;
    const { i18n, note } = helpers;
    return note(
      i18n.t(
        'core-symlink-sweep-showing-first.message',
        { count },
        `Showing the first 200 of ${count}.`,
      ),
    );
  },
};
