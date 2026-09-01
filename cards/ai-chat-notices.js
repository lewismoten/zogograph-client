/* Core card package: status feedback for chat actions and tool choices. */
window.ZogographAiChatNoticesCard = {
  manifest: Object.freeze({
    id: 'core-ai-chat-notices',
    locales: ['core-ai-chat-notices.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-chat-notices.tool_group_requested':
          '{{assistant}} requested {{group}} tools — review and save the choice.',
        'core-ai-chat-notices.limit_raised':
          'Raised, for this conversation. Carry on.',
        'core-ai-chat-notices.nothing_chosen': 'Nothing chosen yet.',
        'core-ai-chat-notices.skip_question_help':
          'Carry on without answering — the same as letting the clock run out',
        'core-ai-chat-notices.context_added':
          'Added. It will read that on its next turn.',
        'core-ai-chat-notices.interrupted_with_runs':
          'Stopped. {{runs}} it had started was dropped.',
        'core-ai-chat-notices.interrupted':
          'Stopped. Tell it what you want it to do instead.',
      }),
      es: Object.freeze({
        'core-ai-chat-notices.tool_group_requested':
          '{{assistant}} solicitó herramientas de {{group}}; revise y guarde la elección.',
        'core-ai-chat-notices.limit_raised':
          'Aumentado para esta conversación. Continúe.',
        'core-ai-chat-notices.nothing_chosen': 'Aún no se ha elegido nada.',
        'core-ai-chat-notices.skip_question_help':
          'Continúa sin responder; es igual que dejar que el tiempo se agote',
        'core-ai-chat-notices.context_added':
          'Añadido. Lo leerá en su siguiente turno.',
        'core-ai-chat-notices.interrupted_with_runs':
          'Detenido. Se descartó {{runs}} que había iniciado.',
        'core-ai-chat-notices.interrupted':
          'Detenido. Dígale qué quiere que haga en su lugar.',
      }),
      hi: Object.freeze({
        'core-ai-chat-notices.tool_group_requested':
          '{{assistant}} ने {{group}} टूल का अनुरोध किया — विकल्प की समीक्षा कर सहेजें।',
        'core-ai-chat-notices.limit_raised':
          'इस बातचीत के लिए बढ़ाया गया। जारी रखें।',
        'core-ai-chat-notices.nothing_chosen': 'अभी कुछ नहीं चुना गया है।',
        'core-ai-chat-notices.skip_question_help':
          'जवाब दिए बिना आगे बढ़ें — यह समय समाप्त होने देने जैसा ही है',
        'core-ai-chat-notices.context_added':
          'जोड़ा गया। यह अपने अगले टर्न में इसे पढ़ेगा।',
        'core-ai-chat-notices.interrupted_with_runs':
          'रोक दिया गया। इसके शुरू किए हुए {{runs}} हटा दिए गए।',
        'core-ai-chat-notices.interrupted':
          'रोक दिया गया। इसके बजाय इसे बताएं कि आप क्या करवाना चाहते हैं।',
      }),
      ar: Object.freeze({
        'core-ai-chat-notices.tool_group_requested':
          'طلب {{assistant}} أدوات {{group}} — راجع الاختيار واحفظه.',
        'core-ai-chat-notices.limit_raised': 'رُفع لهذه المحادثة. تابع.',
        'core-ai-chat-notices.nothing_chosen': 'لم يُختر شيء بعد.',
        'core-ai-chat-notices.skip_question_help':
          'تابع دون إجابة — وهو نفس ترك الوقت ينقضي',
        'core-ai-chat-notices.context_added':
          'أُضيف. سيقرأ ذلك في دوره التالي.',
        'core-ai-chat-notices.interrupted_with_runs':
          'تم الإيقاف. أُسقط {{runs}} مما كان قد بدأه.',
        'core-ai-chat-notices.interrupted':
          'تم الإيقاف. أخبره بما تريد منه أن يفعله بدلاً من ذلك.',
      }),
      zh: Object.freeze({
        'core-ai-chat-notices.tool_group_requested':
          '{{assistant}} 请求使用 {{group}} 工具；请检查并保存此选择。',
        'core-ai-chat-notices.limit_raised': '已为此对话提高限制。请继续。',
        'core-ai-chat-notices.nothing_chosen': '尚未选择任何内容。',
        'core-ai-chat-notices.skip_question_help':
          '不回答而继续——这与让计时结束相同',
        'core-ai-chat-notices.context_added': '已添加。它会在下一轮阅读它。',
        'core-ai-chat-notices.interrupted_with_runs':
          '已停止。它已启动的 {{runs}} 已被丢弃。',
        'core-ai-chat-notices.interrupted':
          '已停止。请告诉它您希望它改做什么。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-ai-chat-notices.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
