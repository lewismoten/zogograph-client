/* Core card package: notices and confirmation copy for memory-page actions. */
window.ZogographAiMemoryActionsCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-actions',
    locales: ['core-ai-memory-actions.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-actions.open_first': 'Open this page first.',
        'core-ai-memory-actions.clear_confirm':
          'Forget everything it has remembered?\n\nEvery conversation starts from nothing again. This cannot be undone from here.',
        'core-ai-memory-actions.clear_result': '{{memories}} forgotten.',
      }),
      es: Object.freeze({
        'core-ai-memory-actions.open_first': 'Abra primero esta página.',
        'core-ai-memory-actions.clear_confirm':
          '¿Olvidar todo lo que ha recordado?\n\nCada conversación volverá a empezar desde nada. No se puede deshacer desde aquí.',
        'core-ai-memory-actions.clear_result': 'Se olvidaron {{memories}}.',
      }),
      hi: Object.freeze({
        'core-ai-memory-actions.open_first': 'पहले यह पृष्ठ खोलें।',
        'core-ai-memory-actions.clear_confirm':
          'इसने जो कुछ भी याद रखा है उसे भूलें?\n\nहर बातचीत फिर से शून्य से शुरू होगी। इसे यहाँ से पूर्ववत नहीं किया जा सकता।',
        'core-ai-memory-actions.clear_result': '{{memories}} भुला दी गईं।',
      }),
      ar: Object.freeze({
        'core-ai-memory-actions.open_first': 'افتح هذه الصفحة أولاً.',
        'core-ai-memory-actions.clear_confirm':
          'نسيان كل ما تذكّره؟\n\nستبدأ كل محادثة من لا شيء مجددًا. لا يمكن التراجع عن ذلك من هنا.',
        'core-ai-memory-actions.clear_result': 'تم نسيان {{memories}}.',
      }),
      zh: Object.freeze({
        'core-ai-memory-actions.open_first': '请先打开此页面。',
        'core-ai-memory-actions.clear_confirm':
          '忘记它记住的所有内容？\n\n每段对话都会从头开始。无法从这里撤销。',
        'core-ai-memory-actions.clear_result': '已忘记 {{memories}}。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-memory-actions.${input.key}`,
      input.values || {},
      input.fallback,
    );
  },
};
