/* Core card package: sign-in fallback messages. */
window.ZogographAuthSignInMessageCard = {
  manifest: Object.freeze({
    id: 'core-auth-sign-in-message',
    locales: ['core-auth-sign-in-message.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-auth-sign-in-message.incorrect_password': 'Incorrect password.',
      }),
      es: Object.freeze({
        'core-auth-sign-in-message.incorrect_password':
          'Contraseña incorrecta.',
      }),
      hi: Object.freeze({
        'core-auth-sign-in-message.incorrect_password': 'गलत पासवर्ड।',
      }),
      ar: Object.freeze({
        'core-auth-sign-in-message.incorrect_password':
          'كلمة المرور غير صحيحة.',
      }),
      zh: Object.freeze({
        'core-auth-sign-in-message.incorrect_password': '密码不正确。',
      }),
    }),
  }),

  render(input, helpers) {
    return helpers.i18n.t(
      'core-auth-sign-in-message.incorrect_password',
      {},
      'Incorrect password.',
    );
  },
};
