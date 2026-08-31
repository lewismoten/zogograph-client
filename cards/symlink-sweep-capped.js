/* Core card package: capped symlink-sweep result. */
window.ZogographSymlinkSweepCappedCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sweep-capped',
    locales: ['core-symlink-sweep-capped.'],
    helpers: ['i18n', 'note'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sweep-capped.message':
          'Capped at {{deleted}} links in one go. {{remaining}} more would be left — run it again.',
      }),
      es: Object.freeze({
        'core-symlink-sweep-capped.message':
          'El límite es {{deleted}} enlaces por operación. Quedarían {{remaining}} más; ejecútelo de nuevo.',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-capped.message':
          'एक बार में {{deleted}} लिंक की सीमा है। {{remaining}} और रह जाएंगे — इसे फिर चलाएँ।',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-capped.message':
          'الحد هو {{deleted}} روابط في المرة الواحدة. ستبقى {{remaining}} أخرى — شغّله مجددًا.',
      }),
      zh: Object.freeze({
        'core-symlink-sweep-capped.message':
          '单次最多 {{deleted}} 个链接。还会剩下 {{remaining}} 个——请再次运行。',
      }),
    }),
  }),
  render(input, helpers) {
    const { deleted, remaining } = input;
    const { i18n, note } = helpers;
    return note(
      i18n.t(
        'core-symlink-sweep-capped.message',
        { deleted, remaining },
        `Capped at ${deleted} links in one go. ${remaining} more would be left — run it again.`,
      ),
    );
  },
};
