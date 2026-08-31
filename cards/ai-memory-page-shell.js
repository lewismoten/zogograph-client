/* Core card package: static shell for the assistant memory page. */
window.ZogographAiMemoryPageShellCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-page-shell',
    locales: ['core-ai-memory-page-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-page-shell.title': 'What {{assistant}} remembers',
        'core-ai-memory-page-shell.add': 'Add one',
        'core-ai-memory-page-shell.forget': 'Forget everything',
        'core-ai-memory-page-shell.help':
          'A conversation ends and everything worked out in it goes with it. These are the things kept on purpose — which of the installed PHP versions the site actually runs, what the 03:10 spike turns out to be, how you have said you want to be told things. Everything here is editable and deletable by you, and every one records the conversation it came from, because a remembered claim whose origin cannot be found is a rumour.',
      }),
      es: Object.freeze({
        'core-ai-memory-page-shell.title': 'Lo que recuerda {{assistant}}',
        'core-ai-memory-page-shell.add': 'Añadir una',
        'core-ai-memory-page-shell.forget': 'Olvidarlo todo',
        'core-ai-memory-page-shell.help':
          'Una conversación termina y todo lo que se resolvió en ella se va con ella. Estas son las cosas que se conservan a propósito: qué versión de PHP instalada ejecuta realmente el sitio, qué resulta ser el pico de las 03:10, cómo ha dicho que quiere que se le informen las cosas. Todo aquí se puede editar y eliminar, y cada elemento registra la conversación de la que procede, porque una afirmación recordada cuyo origen no se puede encontrar es un rumor.',
      }),
      hi: Object.freeze({
        'core-ai-memory-page-shell.title': '{{assistant}} क्या याद रखता है',
        'core-ai-memory-page-shell.add': 'एक जोड़ें',
        'core-ai-memory-page-shell.forget': 'सब भूलें',
        'core-ai-memory-page-shell.help':
          'बातचीत समाप्त होती है और उसमें निकली हर बात साथ चली जाती है। ये वे चीज़ें हैं जिन्हें उद्देश्य से रखा गया है—साइट वास्तव में इंस्टॉल PHP संस्करणों में से कौन सा चलाती है, 03:10 का स्पाइक क्या निकलता है, आपने कैसे बताया जाना चाहा है। यहाँ सब कुछ आपके द्वारा संपादित और हटाया जा सकता है और हर एक उस बातचीत को दर्ज करता है जिससे वह आया था, क्योंकि ऐसा याद दावा जिसका स्रोत न मिले अफ़वाह है।',
      }),
      ar: Object.freeze({
        'core-ai-memory-page-shell.title': 'ما يتذكره {{assistant}}',
        'core-ai-memory-page-shell.add': 'أضف واحدة',
        'core-ai-memory-page-shell.forget': 'انس كل شيء',
        'core-ai-memory-page-shell.help':
          'تنتهي محادثة ويذهب معها كل ما حُل فيها. هذه هي الأشياء المحفوظة عمدًا — أي إصدارات PHP المثبتة يشغّلها الموقع فعلًا، وما الذي يتبين أن ذروة 03:10 هي، وكيف قلت إنك تريد أن تُخبر بالأمور. كل شيء هنا قابل للتعديل والحذف بواسطتك، وكل واحد يسجل المحادثة التي أتى منها، لأن ادعاء متذكّرًا لا يمكن العثور على أصله إشاعة.',
      }),
      zh: Object.freeze({
        'core-ai-memory-page-shell.title': '{{assistant}} 记住什么',
        'core-ai-memory-page-shell.add': '添加一条',
        'core-ai-memory-page-shell.forget': '忘记全部',
        'core-ai-memory-page-shell.help':
          '一段对话结束后，在其中解决的所有事情都会随之消失。这些内容是有意保留的：网站实际运行哪个已安装的 PHP 版本、03:10 的峰值究竟是什么、您说过希望如何接收信息。这里的一切都可由您编辑和删除，每项都记录其来源对话，因为找不到来源的已记住说法只是传言。',
      }),
    }),
  }),
  render(input, helpers) {
    const assistant = '\uE000assistant\uE001';
    return {
      title: helpers.i18n.t(
        'core-ai-memory-page-shell.title',
        { assistant },
        'What {{assistant}} remembers',
      ),
      assistant: input.assistant,
      add: helpers.i18n.t('core-ai-memory-page-shell.add', {}, 'Add one'),
      forget: helpers.i18n.t(
        'core-ai-memory-page-shell.forget',
        {},
        'Forget everything',
      ),
      help: helpers.i18n.t(
        'core-ai-memory-page-shell.help',
        {},
        'A conversation ends and everything worked out in it goes with it. These are the things kept on purpose — which of the installed PHP versions the site actually runs, what the 03:10 spike turns out to be, how you have said you want to be told things. Everything here is editable and deletable by you, and every one records the conversation it came from, because a remembered claim whose origin cannot be found is a rumour.',
      ),
    };
  },
};
