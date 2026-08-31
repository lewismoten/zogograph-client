/* Core card package: feedback and confirmation copy for saved conversations. */
window.ZogographAiPastConversationActionsCard = {
  manifest: Object.freeze({
    id: 'core-ai-past-conversation-actions',
    locales: ['core-ai-past-conversation-actions.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-past-conversation-actions.pinned':
          'Pinned. Retention will leave this one alone.',
        'core-ai-past-conversation-actions.unpinned':
          'Unpinned. It is subject to the retention rule again.',
        'core-ai-past-conversation-actions.resume_title':
          'Pick this conversation up?',
        'core-ai-past-conversation-actions.resume_background':
          'A new conversation starts, carrying what was said in this one as background.',
        'core-ai-past-conversation-actions.resume_history':
          'It is carried as history rather than replayed, and the assistant is told so: anything about the state of the machine has to be measured again before it is relied on. A tool result from last week read as a reading from now is the one thing it is told never to do.',
        'core-ai-past-conversation-actions.resume_limits':
          'The limits that ended the old one - turns, spend, time - do not come with it. A conversation still open will be stopped.',
        'core-ai-past-conversation-actions.resumed':
          'Picked up. It is the open conversation now.',
      }),
      es: Object.freeze({
        'core-ai-past-conversation-actions.pinned':
          'Fijada. La retención dejará esta intacta.',
        'core-ai-past-conversation-actions.unpinned':
          'Desfijada. Vuelve a estar sujeta a la regla de retención.',
        'core-ai-past-conversation-actions.resume_title':
          '¿Retomar esta conversación?',
        'core-ai-past-conversation-actions.resume_background':
          'Comienza una conversación nueva que lleva como contexto lo dicho en esta.',
        'core-ai-past-conversation-actions.resume_history':
          'Se lleva como historial, no se reproduce, y se le indica al asistente: cualquier cosa sobre el estado de la máquina debe volver a medirse antes de confiar en ella. Un resultado de herramienta de la semana pasada leído como una medición actual es lo único que se le dice que nunca haga.',
        'core-ai-past-conversation-actions.resume_limits':
          'Los límites que terminaron la anterior — turnos, gasto y tiempo — no la acompañan. Se detendrá cualquier conversación que siga abierta.',
        'core-ai-past-conversation-actions.resumed':
          'Retomada. Ahora es la conversación abierta.',
      }),
      hi: Object.freeze({
        'core-ai-past-conversation-actions.pinned':
          'पिन किया गया। प्रतिधारण इसे अकेला छोड़ देगा।',
        'core-ai-past-conversation-actions.unpinned':
          'पिन हटाया गया। यह फिर से प्रतिधारण नियम के अधीन है।',
        'core-ai-past-conversation-actions.resume_title':
          'इस बातचीत को फिर से शुरू करें?',
        'core-ai-past-conversation-actions.resume_background':
          'एक नई बातचीत शुरू होती है, जो इसमें कही गई बातों को पृष्ठभूमि के रूप में साथ ले जाती है।',
        'core-ai-past-conversation-actions.resume_history':
          'इसे दोहराने के बजाय इतिहास के रूप में साथ ले जाया जाता है, और सहायक को बताया जाता है: मशीन की स्थिति के बारे में किसी भी बात पर भरोसा करने से पहले उसे फिर मापना होगा। पिछले सप्ताह के टूल परिणाम को वर्तमान रीडिंग मानना वह एक काम है जो उसे कभी नहीं करना चाहिए।',
        'core-ai-past-conversation-actions.resume_limits':
          'जिसने पुरानी बातचीत समाप्त की वे सीमाएँ — टर्न, खर्च, समय — साथ नहीं आतीं। जो बातचीत अभी खुली है उसे रोका जाएगा।',
        'core-ai-past-conversation-actions.resumed':
          'फिर से शुरू की गई। अब यही खुली बातचीत है।',
      }),
      ar: Object.freeze({
        'core-ai-past-conversation-actions.pinned':
          'تم التثبيت. لن تحذف قاعدة الاحتفاظ هذه المحادثة.',
        'core-ai-past-conversation-actions.unpinned':
          'أُلغي التثبيت. تخضع الآن لقاعدة الاحتفاظ مجددًا.',
        'core-ai-past-conversation-actions.resume_title':
          'متابعة هذه المحادثة؟',
        'core-ai-past-conversation-actions.resume_background':
          'تبدأ محادثة جديدة حاملةً ما قيل في هذه كمعلومات خلفية.',
        'core-ai-past-conversation-actions.resume_history':
          'تُنقل كسجل لا يعاد تشغيله، ويُخبر المساعد بذلك: يجب قياس أي شيء عن حالة الجهاز مرة أخرى قبل الاعتماد عليه. نتيجة أداة من الأسبوع الماضي تُقرأ كأنها قياس الآن هي الشيء الوحيد الذي يُطلب منه ألا يفعله أبدًا.',
        'core-ai-past-conversation-actions.resume_limits':
          'الحدود التي أنهت السابقة — الأدوار والإنفاق والوقت — لا تنتقل معها. ستُوقف أي محادثة لا تزال مفتوحة.',
        'core-ai-past-conversation-actions.resumed':
          'تمت المتابعة. هذه هي المحادثة المفتوحة الآن.',
      }),
      zh: Object.freeze({
        'core-ai-past-conversation-actions.pinned':
          '已置顶。保留规则不会删除这条对话。',
        'core-ai-past-conversation-actions.unpinned':
          '已取消置顶。它再次受保留规则约束。',
        'core-ai-past-conversation-actions.resume_title': '继续这段对话？',
        'core-ai-past-conversation-actions.resume_background':
          '会启动一段新对话，并将这段对话的内容作为背景携带。',
        'core-ai-past-conversation-actions.resume_history':
          '它会作为历史携带，而不是重新播放；助手会被告知：任何关于机器状态的内容在依赖前都必须重新测量。把上周的工具结果当作当前读数，是明确禁止它做的唯一一件事。',
        'core-ai-past-conversation-actions.resume_limits':
          '结束旧对话的限制——轮次、花费和时间——不会随之带来。仍处于打开状态的对话将被停止。',
        'core-ai-past-conversation-actions.resumed':
          '已继续。它现在是当前对话。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-past-conversation-actions.${input.key}`,
      {},
      input.fallback,
    );
  },
};
