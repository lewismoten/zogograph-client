/* Core card package: sign-in fallback messages. */
window.ZogographAuthSignInMessageCard = {
  manifest: Object.freeze({
    id: 'core-auth-sign-in-message',
    locales: ['core-auth-sign-in-message.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-auth-sign-in-message.incorrect_password': 'Incorrect password.',
        'core-auth-sign-in-message.ask': 'Sign in to ask {{assistant}}.',
        'core-auth-sign-in-message.note_or_ask':
          'Sign in to write a note or ask {{assistant}}.',
      }),
      es: Object.freeze({
        'core-auth-sign-in-message.incorrect_password':
          'Contraseña incorrecta.',
        'core-auth-sign-in-message.ask':
          'Inicie sesión para preguntarle a {{assistant}}.',
        'core-auth-sign-in-message.note_or_ask':
          'Inicie sesión para escribir una nota o preguntarle a {{assistant}}.',
      }),
      hi: Object.freeze({
        'core-auth-sign-in-message.incorrect_password': 'गलत पासवर्ड।',
        'core-auth-sign-in-message.ask':
          '{{assistant}} से पूछने के लिए साइन इन करें।',
        'core-auth-sign-in-message.note_or_ask':
          'नोट लिखने या {{assistant}} से पूछने के लिए साइन इन करें।',
      }),
      ar: Object.freeze({
        'core-auth-sign-in-message.incorrect_password':
          'كلمة المرور غير صحيحة.',
        'core-auth-sign-in-message.ask': 'سجّل الدخول لسؤال {{assistant}}.',
        'core-auth-sign-in-message.note_or_ask':
          'سجّل الدخول لكتابة ملاحظة أو سؤال {{assistant}}.',
      }),
      zh: Object.freeze({
        'core-auth-sign-in-message.incorrect_password': '密码不正确。',
        'core-auth-sign-in-message.ask': '请登录以询问 {{assistant}}。',
        'core-auth-sign-in-message.note_or_ask':
          '请登录以写下备注或询问 {{assistant}}。',
      }),
    }),
  }),

  text(key, variables, helpers) {
    return helpers.i18n.t(
      `core-auth-sign-in-message.${key}`,
      variables,
      this.manifest.catalogs.en[`core-auth-sign-in-message.${key}`],
    );
  },
  render(input, helpers) {
    return helpers.i18n.t(
      'core-auth-sign-in-message.incorrect_password',
      {},
      'Incorrect password.',
    );
  },
};
