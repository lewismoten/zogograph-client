window.ZogographEmailRuleIntroCard = {
  manifest: Object.freeze({
    id: 'core-email-rule-intro',
    locales: ['core-email-rule-intro.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-email-rule-intro.text':
          'A cooldown of “every time” is a real answer for a site that has stopped answering. The recommendation is beside each one, so a deliberate choice is distinguishable from a default nobody looked at.',
      }),
      es: Object.freeze({
        'core-email-rule-intro.text':
          'Un periodo de espera de «cada vez» es una respuesta válida para un sitio que ha dejado de responder. La recomendación está junto a cada regla, de modo que una elección deliberada se distingue de un valor predeterminado que nadie revisó.',
      }),
      hi: Object.freeze({
        'core-email-rule-intro.text':
          'जवाब देना बंद कर चुकी साइट के लिए “हर बार” का कूलडाउन वास्तविक उत्तर है। हर नियम के साथ अनुशंसा है, इसलिए जानबूझकर किया गया चुनाव उस डिफ़ॉल्ट से अलग दिखता है जिसे किसी ने नहीं देखा।',
      }),
      ar: Object.freeze({
        'core-email-rule-intro.text':
          'فترة التهدئة «في كل مرة» إجابة حقيقية لموقع توقف عن الاستجابة. توجد التوصية بجانب كل قاعدة، لذا يمكن تمييز الاختيار المتعمد عن قيمة افتراضية لم يراجعها أحد.',
      }),
      zh: Object.freeze({
        'core-email-rule-intro.text':
          '对于已停止响应的网站，“每次”冷却时间是合理的选择。每条规则旁都有建议，因此可以区分有意的选择和无人查看过的默认值。',
      }),
    }),
  }),
  render(_data, helpers) {
    return helpers.note(
      helpers.i18n.t(
        'core-email-rule-intro.text',
        {},
        'A cooldown of “every time” is a real answer for a site that has stopped answering. The recommendation is beside each one, so a deliberate choice is distinguishable from a default nobody looked at.',
      ),
    );
  },
};
