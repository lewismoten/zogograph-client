/* Core card package: model context capacity setting. */
window.ZogographAiContextWindowCard = {
  manifest: Object.freeze({
    id: 'core-ai-context-window',
    locales: ['core-ai-context-window.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-context-window.label': 'Context window (tokens)',
        'core-ai-context-window.help':
          "How much the chosen model can hold at once. It is a fact about the model, not something the API will tell us, so it is typed in — check the model's own documentation. Guessing high is the worse mistake: the provider rejects the request outright, which ends the conversation.",
      }),
      es: Object.freeze({
        'core-ai-context-window.label': 'Ventana de contexto (tokens)',
        'core-ai-context-window.help':
          'Cuánto puede mantener el modelo elegido a la vez. Es un dato del modelo, no algo que la API indique; consulte su documentación. Adivinar demasiado alto hace que el proveedor rechace la solicitud.',
      }),
      hi: Object.freeze({
        'core-ai-context-window.label': 'संदर्भ विंडो (टोकन)',
        'core-ai-context-window.help':
          'चुना गया मॉडल एक साथ कितना रख सकता है। यह मॉडल का तथ्य है, API बताने वाली चीज़ नहीं; उसके दस्तावेज़ देखें। बहुत अधिक अनुमान लगाने पर प्रदाता अनुरोध अस्वीकार कर देता है।',
      }),
      ar: Object.freeze({
        'core-ai-context-window.label': 'نافذة السياق (الرموز)',
        'core-ai-context-window.help':
          'مقدار ما يستطيع النموذج المختار الاحتفاظ به دفعة واحدة. هذه حقيقة عن النموذج لا تخبر بها الواجهة البرمجية؛ راجع توثيقه. التخمين المرتفع يجعل المزوّد يرفض الطلب.',
      }),
      zh: Object.freeze({
        'core-ai-context-window.label': '上下文窗口（令牌）',
        'core-ai-context-window.help':
          '所选模型一次可保留的内容量。这是模型本身的事实，API 不会告诉我们；请查阅模型文档。猜得太高会让提供商直接拒绝请求。',
      }),
    }),
  }),
  render(data, helpers) {
    const input = document.createElement('input');
    input.type = 'number';
    input.id = 'aiContextTokens';
    input.min = '4000';
    input.step = '1000';
    input.className = 'days';
    input.value = String(data.context_tokens ?? 128000);
    return helpers.fieldRow(
      helpers.i18n.t(
        'core-ai-context-window.label',
        {},
        'Context window (tokens)',
      ),
      input,
      helpers.i18n.t(
        'core-ai-context-window.help',
        {},
        "How much the chosen model can hold at once. It is a fact about the model, not something the API will tell us, so it is typed in — check the model's own documentation. Guessing high is the worse mistake: the provider rejects the request outright, which ends the conversation.",
      ),
    );
  },
};
