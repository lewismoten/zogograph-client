/* Core card package: signed-out state for past assistant conversations. */
window.ZogographAiPastConversationsSignInCard = {
  manifest: Object.freeze({
    id: 'core-ai-past-conversations-sign-in',
    locales: ['core-ai-past-conversations-sign-in.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-past-conversations-sign-in.message':
          'Sign in to see past conversations.',
      }),
      es: Object.freeze({
        'core-ai-past-conversations-sign-in.message':
          'Inicie sesión para ver conversaciones anteriores.',
      }),
      hi: Object.freeze({
        'core-ai-past-conversations-sign-in.message':
          'पिछली बातचीत देखने के लिए साइन इन करें।',
      }),
      ar: Object.freeze({
        'core-ai-past-conversations-sign-in.message':
          'سجّل الدخول لرؤية المحادثات السابقة.',
      }),
      zh: Object.freeze({
        'core-ai-past-conversations-sign-in.message':
          '登录后可查看过去的对话。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.note(
      helpers.i18n.t(
        'core-ai-past-conversations-sign-in.message',
        {},
        'Sign in to see past conversations.',
      ),
    );
  },
};
