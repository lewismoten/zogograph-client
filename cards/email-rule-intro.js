window.ZogographEmailRuleIntroCard = {
  manifest: Object.freeze({
    id: 'core-email-rule-intro',
    locales: ['core-email-rule-intro.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-email-rule-intro.text':
          'A cooldown of “every time” is a real answer for a site that has stopped answering. The recommendation is beside each one, so a deliberate choice is distinguishable from a default nobody looked at.',
        'core-email-rule-intro.set_by_hand': '{{duration}} (set by hand)',
        'core-email-rule-intro.recommended': 'recommended {{value}}',
        'core-email-rule-intro.every_time': 'every time',
        'core-email-rule-intro.as_recommended': 'as recommended',
        'core-email-rule-intro.show_me': 'Show me',
        'core-email-rule-intro.open_view': 'Open {{view}}',
      }),
      es: Object.freeze({
        'core-email-rule-intro.text':
          'Un periodo de espera de «cada vez» es una respuesta válida para un sitio que ha dejado de responder. La recomendación está junto a cada regla, de modo que una elección deliberada se distingue de un valor predeterminado que nadie revisó.',
        'core-email-rule-intro.set_by_hand':
          '{{duration}} (establecido manualmente)',
        'core-email-rule-intro.recommended': 'recomendado: {{value}}',
        'core-email-rule-intro.every_time': 'cada vez',
        'core-email-rule-intro.as_recommended': 'como se recomienda',
        'core-email-rule-intro.show_me': 'Muéstramelo',
        'core-email-rule-intro.open_view': 'Abrir {{view}}',
      }),
      hi: Object.freeze({
        'core-email-rule-intro.text':
          'जवाब देना बंद कर चुकी साइट के लिए “हर बार” का कूलडाउन वास्तविक उत्तर है। हर नियम के साथ अनुशंसा है, इसलिए जानबूझकर किया गया चुनाव उस डिफ़ॉल्ट से अलग दिखता है जिसे किसी ने नहीं देखा।',
        'core-email-rule-intro.set_by_hand': '{{duration}} (हाथ से सेट)',
        'core-email-rule-intro.recommended': 'अनुशंसित: {{value}}',
        'core-email-rule-intro.every_time': 'हर बार',
        'core-email-rule-intro.as_recommended': 'अनुशंसित के रूप में',
        'core-email-rule-intro.show_me': 'मुझे दिखाएँ',
        'core-email-rule-intro.open_view': '{{view}} खोलें',
      }),
      ar: Object.freeze({
        'core-email-rule-intro.text':
          'فترة التهدئة «في كل مرة» إجابة حقيقية لموقع توقف عن الاستجابة. توجد التوصية بجانب كل قاعدة، لذا يمكن تمييز الاختيار المتعمد عن قيمة افتراضية لم يراجعها أحد.',
        'core-email-rule-intro.set_by_hand': '{{duration}} (مُعيّن يدويًا)',
        'core-email-rule-intro.recommended': 'الموصى به: {{value}}',
        'core-email-rule-intro.every_time': 'كل مرة',
        'core-email-rule-intro.as_recommended': 'كما هو موصى به',
        'core-email-rule-intro.show_me': 'أرني',
        'core-email-rule-intro.open_view': 'افتح {{view}}',
      }),
      zh: Object.freeze({
        'core-email-rule-intro.text':
          '对于已停止响应的网站，“每次”冷却时间是合理的选择。每条规则旁都有建议，因此可以区分有意的选择和无人查看过的默认值。',
        'core-email-rule-intro.set_by_hand': '{{duration}}（手动设置）',
        'core-email-rule-intro.recommended': '建议：{{value}}',
        'core-email-rule-intro.every_time': '每次',
        'core-email-rule-intro.as_recommended': '按建议设置',
        'core-email-rule-intro.show_me': '带我查看',
        'core-email-rule-intro.open_view': '打开 {{view}}',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-email-rule-intro.${key}`,
      values,
      this.manifest.catalogs.en[`core-email-rule-intro.${key}`],
    );
  },
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
