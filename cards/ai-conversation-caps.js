/* Core card package: spend and turn caps for one assistant conversation. */
window.ZogographAiConversationCapsCard = {
  manifest: Object.freeze({
    id: 'core-ai-conversation-caps',
    locales: ['core-ai-conversation-caps.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-conversation-caps.spend_label': 'Or after spending (cents)',
        'core-ai-conversation-caps.spend_help':
          'Checked before each request rather than after, so the limit stops the next call rather than being noticed once the money is gone. Zero means no limit — which, with prices set, means no limit on the bill either.',
        'core-ai-conversation-caps.turns_label': 'Or after this many turns',
        'core-ai-conversation-caps.turns_help':
          'One turn is one request to the model. A conversation that needs forty is usually one that has gone in a circle, so this is the limit most likely to catch a problem rather than a long investigation.',
      }),
      es: Object.freeze({
        'core-ai-conversation-caps.spend_label':
          'O después de gastar (centavos)',
        'core-ai-conversation-caps.spend_help':
          'Se comprueba antes de cada solicitud, por lo que detiene la siguiente llamada antes de que se agote el dinero. Cero significa sin límite.',
        'core-ai-conversation-caps.turns_label':
          'O después de este número de turnos',
        'core-ai-conversation-caps.turns_help':
          'Un turno es una solicitud al modelo. Una conversación que necesita cuarenta suele haber dado vueltas en círculo, por lo que este límite probablemente detectará un problema.',
      }),
      hi: Object.freeze({
        'core-ai-conversation-caps.spend_label': 'या खर्च के बाद (सेंट)',
        'core-ai-conversation-caps.spend_help':
          'हर अनुरोध से पहले जाँचा जाता है, इसलिए पैसा खत्म होने पर पता चलने के बजाय अगली कॉल रुकती है। शून्य का अर्थ कोई सीमा नहीं है।',
        'core-ai-conversation-caps.turns_label': 'या इतने टर्न के बाद',
        'core-ai-conversation-caps.turns_help':
          'एक टर्न मॉडल को एक अनुरोध है। चालीस टर्न वाली बातचीत अक्सर चक्कर में फँसी होती है, इसलिए यह सीमा समस्या पकड़ने की संभावना रखती है।',
      }),
      ar: Object.freeze({
        'core-ai-conversation-caps.spend_label': 'أو بعد إنفاق (بالسنتات)',
        'core-ai-conversation-caps.spend_help':
          'يُفحص قبل كل طلب، فيوقف المكالمة التالية بدل ملاحظته بعد نفاد المال. الصفر يعني بلا حد.',
        'core-ai-conversation-caps.turns_label': 'أو بعد هذا العدد من الأدوار',
        'core-ai-conversation-caps.turns_help':
          'الدور طلب واحد إلى النموذج. المحادثة التي تحتاج أربعين دورًا غالبًا ما تكون تدور في حلقة، ولذلك يرجح أن يلتقط هذا الحد مشكلة.',
      }),
      zh: Object.freeze({
        'core-ai-conversation-caps.spend_label': '或在花费后停止（美分）',
        'core-ai-conversation-caps.spend_help':
          '在每次请求前检查，因此会停止下一次调用，而不是在钱花完后才发现。零表示不限。',
        'core-ai-conversation-caps.turns_label': '或在达到此轮数后停止',
        'core-ai-conversation-caps.turns_help':
          '一轮是对模型的一次请求。需要四十轮的对话通常陷入了循环，因此这个限制最可能发现问题。',
      }),
    }),
  }),
  render(data, helpers) {
    const t = (key, fallback) =>
      helpers.i18n.t(`core-ai-conversation-caps.${key}`, {}, fallback);
    const spend = document.createElement('input');
    spend.type = 'number';
    spend.id = 'aiMaxCents';
    spend.min = '0';
    spend.step = '1';
    spend.className = 'days';
    spend.value = String(data.max_cents || 0);
    const turns = document.createElement('input');
    turns.type = 'number';
    turns.id = 'aiMaxTurns';
    turns.min = '0';
    turns.max = '500';
    turns.className = 'days';
    turns.value = String(data.max_turns || 0);
    return [
      helpers.fieldRow(
        t('spend_label', 'Or after spending (cents)'),
        spend,
        t(
          'spend_help',
          'Checked before each request rather than after, so the limit stops the next call rather than being noticed once the money is gone. Zero means no limit — which, with prices set, means no limit on the bill either.',
        ),
      ),
      helpers.fieldRow(
        t('turns_label', 'Or after this many turns'),
        turns,
        t(
          'turns_help',
          'One turn is one request to the model. A conversation that needs forty is usually one that has gone in a circle, so this is the limit most likely to catch a problem rather than a long investigation.',
        ),
      ),
    ];
  },
};
