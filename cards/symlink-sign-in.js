/* Core card package: signed-out symlink inspection notice. */
window.ZogographSymlinkSignInCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sign-in',
    locales: ['core-symlink-sign-in.'],
    helpers: ['i18n', 'note'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sign-in.message': 'Sign in to check symlinks.',
      }),
      es: Object.freeze({
        'core-symlink-sign-in.message':
          'Inicie sesión para comprobar los enlaces simbólicos.',
      }),
      hi: Object.freeze({
        'core-symlink-sign-in.message': 'सिमलिंक जाँचने के लिए साइन इन करें।',
      }),
      ar: Object.freeze({
        'core-symlink-sign-in.message': 'سجّل الدخول لفحص الروابط الرمزية.',
      }),
      zh: Object.freeze({
        'core-symlink-sign-in.message': '请登录以检查符号链接。',
      }),
    }),
  }),
  render(input, helpers) {
    const { i18n, note } = helpers;
    return note(
      i18n.t('core-symlink-sign-in.message', {}, 'Sign in to check symlinks.'),
    );
  },
};
