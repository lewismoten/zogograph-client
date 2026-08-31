/* Core card package: sign-in state for the assistant settings pages. */
window.ZogographAiSettingsSignInCard = {
  manifest: Object.freeze({
    id: 'core-ai-settings-sign-in',
    locales: ['core-ai-settings-sign-in.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-settings-sign-in.message': 'Sign in to see these settings.',
      }),
      es: Object.freeze({
        'core-ai-settings-sign-in.message':
          'Inicie sesión para ver estos ajustes.',
      }),
      hi: Object.freeze({
        'core-ai-settings-sign-in.message':
          'इन सेटिंग्स को देखने के लिए साइन इन करें।',
      }),
      ar: Object.freeze({
        'core-ai-settings-sign-in.message': 'سجّل الدخول لرؤية هذه الإعدادات.',
      }),
      zh: Object.freeze({
        'core-ai-settings-sign-in.message': '登录后可查看这些设置。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.note(
      helpers.i18n.t(
        'core-ai-settings-sign-in.message',
        {},
        'Sign in to see these settings.',
      ),
    );
  },
};
