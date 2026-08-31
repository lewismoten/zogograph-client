/* Core card package: destructive confirmation copy for one saved memory. */
window.ZogographAiMemoryForgetDialogCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-forget-dialog',
    locales: ['core-ai-memory-forget-dialog.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-forget-dialog.confirm': 'Forget “{{subject}}”?',
        'core-ai-memory-forget-dialog.title': 'Forget memory',
      }),
      es: Object.freeze({
        'core-ai-memory-forget-dialog.confirm': '¿Olvidar «{{subject}}»?',
        'core-ai-memory-forget-dialog.title': 'Olvidar memoria',
      }),
      hi: Object.freeze({
        'core-ai-memory-forget-dialog.confirm': '“{{subject}}” को भूलें?',
        'core-ai-memory-forget-dialog.title': 'मेमोरी भूलें',
      }),
      ar: Object.freeze({
        'core-ai-memory-forget-dialog.confirm': 'هل تريد نسيان «{{subject}}»؟',
        'core-ai-memory-forget-dialog.title': 'نسيان ذاكرة',
      }),
      zh: Object.freeze({
        'core-ai-memory-forget-dialog.confirm': '要忘记“{{subject}}”吗？',
        'core-ai-memory-forget-dialog.title': '忘记记忆',
      }),
    }),
  }),
  render(input, helpers) {
    return {
      confirm: helpers.i18n.t(
        'core-ai-memory-forget-dialog.confirm',
        { subject: input.subject },
        'Forget “{{subject}}”?',
      ),
      title: helpers.i18n.t(
        'core-ai-memory-forget-dialog.title',
        {},
        'Forget memory',
      ),
    };
  },
};
