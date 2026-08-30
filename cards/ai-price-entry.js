/* Core card package: optional per-million token prices for the selected model. */
window.ZogographAiPriceEntryCard = {
  manifest: Object.freeze({
    id: 'core-ai-price-entry',
    locales: ['core-ai-price-entry.'],
    helpers: ['fieldRow', 'section', 'note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-price-entry.input_label': 'Input, per million',
        'core-ai-price-entry.input_help':
          "In cents. From the provider's pricing page.",
        'core-ai-price-entry.output_label': 'Output, per million',
        'core-ai-price-entry.output_help':
          'In cents. Usually several times the input price.',
        'core-ai-price-entry.cached_label': 'Cached input, per million',
        'core-ai-price-entry.cached_help':
          'In cents. Usually about a tenth of the input price. On a conversation that re-sends a long prompt every turn this is most of the bill, so leaving it at the input price overstates the cost considerably.',
        'core-ai-price-entry.title': 'Prices, if you want a cost',
        'core-ai-price-entry.help':
          'Leave these at zero and sessions report token counts and no money. That is the honest default — a wrong price is worse than no price, so nothing is guessed from the model name. Prices you set here are remembered against this provider and model, so changing the model and changing back does not lose them.',
      }),
      es: Object.freeze({
        'core-ai-price-entry.input_label': 'Entrada, por millón',
        'core-ai-price-entry.input_help':
          'En céntimos. Consulte la página de precios del proveedor.',
        'core-ai-price-entry.output_label': 'Salida, por millón',
        'core-ai-price-entry.output_help':
          'En céntimos. Suele ser varias veces el precio de entrada.',
        'core-ai-price-entry.cached_label': 'Entrada en caché, por millón',
        'core-ai-price-entry.cached_help':
          'En céntimos. Suele ser aproximadamente una décima parte del precio de entrada. En una conversación que reenvía un mensaje largo en cada turno, esto es la mayor parte de la factura, por lo que dejarlo al precio de entrada sobrestima mucho el coste.',
        'core-ai-price-entry.title': 'Precios, si desea calcular un coste',
        'core-ai-price-entry.help':
          'Déjelos a cero y las sesiones mostrarán recuentos de tokens, sin dinero. Es el valor predeterminado honesto: un precio incorrecto es peor que ninguno, así que no se adivina nada por el nombre del modelo. Los precios que indique aquí se recuerdan para este proveedor y modelo; cambiar de modelo y volver no los pierde.',
      }),
      hi: Object.freeze({
        'core-ai-price-entry.input_label': 'इनपुट, प्रति मिलियन',
        'core-ai-price-entry.input_help':
          'सेंट में। प्रदाता के मूल्य पृष्ठ से।',
        'core-ai-price-entry.output_label': 'आउटपुट, प्रति मिलियन',
        'core-ai-price-entry.output_help':
          'सेंट में। आमतौर पर इनपुट मूल्य से कई गुना।',
        'core-ai-price-entry.cached_label': 'कैश किया गया इनपुट, प्रति मिलियन',
        'core-ai-price-entry.cached_help':
          'सेंट में। आमतौर पर इनपुट मूल्य का लगभग दसवां हिस्सा। हर टर्न पर लंबा प्रॉम्प्ट फिर से भेजने वाली बातचीत में यह बिल का अधिकांश भाग होता है, इसलिए इसे इनपुट मूल्य पर छोड़ने से लागत काफी अधिक दिखाई देती है।',
        'core-ai-price-entry.title': 'मूल्य, यदि लागत जाननी हो',
        'core-ai-price-entry.help':
          'इन्हें शून्य पर छोड़ें तो सत्र टोकन गिनती दिखाते हैं और कोई पैसा नहीं। यह ईमानदार डिफ़ॉल्ट है — गलत मूल्य, मूल्य न होने से बुरा है, इसलिए मॉडल के नाम से कुछ नहीं अनुमानित किया जाता। यहाँ सेट किए मूल्य इस प्रदाता और मॉडल के साथ याद रखे जाते हैं, इसलिए मॉडल बदलकर वापस आने पर वे नहीं खोते।',
      }),
      ar: Object.freeze({
        'core-ai-price-entry.input_label': 'الإدخال، لكل مليون',
        'core-ai-price-entry.input_help': 'بالسنتات. من صفحة أسعار الموفّر.',
        'core-ai-price-entry.output_label': 'الإخراج، لكل مليون',
        'core-ai-price-entry.output_help':
          'بالسنتات. يكون عادةً عدة أضعاف سعر الإدخال.',
        'core-ai-price-entry.cached_label': 'إدخال مخزّن مؤقتًا، لكل مليون',
        'core-ai-price-entry.cached_help':
          'بالسنتات. يكون عادةً نحو عُشر سعر الإدخال. في محادثة تعيد إرسال مطالبة طويلة في كل دور، يشكّل هذا معظم الفاتورة، لذا فإن تركه بسعر الإدخال يبالغ في التكلفة كثيرًا.',
        'core-ai-price-entry.title': 'الأسعار، إن أردت معرفة التكلفة',
        'core-ai-price-entry.help':
          'اترك هذه القيم صفرًا فتعرض الجلسات أعداد الرموز فقط ولا تعرض مبلغًا ماليًا. هذا هو الإعداد الافتراضي الصادق — السعر الخاطئ أسوأ من عدم وجود سعر، لذلك لا يُخمّن شيء من اسم النموذج. تُحفظ الأسعار التي تضبطها هنا لهذا الموفّر والنموذج، لذلك لا تفقدها عند تغيير النموذج ثم العودة إليه.',
      }),
      zh: Object.freeze({
        'core-ai-price-entry.input_label': '输入，每百万',
        'core-ai-price-entry.input_help': '以美分计。请参阅提供商的价格页面。',
        'core-ai-price-entry.output_label': '输出，每百万',
        'core-ai-price-entry.output_help': '以美分计。通常是输入价格的数倍。',
        'core-ai-price-entry.cached_label': '缓存输入，每百万',
        'core-ai-price-entry.cached_help':
          '以美分计。通常约为输入价格的十分之一。在每轮都重新发送长提示的对话中，这会占据大部分费用，因此将其保持为输入价格会明显高估成本。',
        'core-ai-price-entry.title': '价格（如需计算成本）',
        'core-ai-price-entry.help':
          '将这些值保留为零，会话便只报告令牌数量而不报告金额。这是诚实的默认值——错误的价格比没有价格更糟，因此不会根据模型名称猜测。您在这里设置的价格会随此提供商和模型保存，切换模型后再切回也不会丢失。',
      }),
    }),
  }),
  render(data, helpers) {
    const t = (key, values, fallback) =>
      helpers.i18n.t(`core-ai-price-entry.${key}`, values, fallback);
    const money = document.createElement('div');
    money.className = 'settings';
    for (const [id, key, fallback, value, helpKey, helpFallback] of [
      [
        'aiPriceIn',
        'input_label',
        'Input, per million',
        data.price_in,
        'input_help',
        "In cents. From the provider's pricing page.",
      ],
      [
        'aiPriceOut',
        'output_label',
        'Output, per million',
        data.price_out,
        'output_help',
        'In cents. Usually several times the input price.',
      ],
      [
        'aiPriceCached',
        'cached_label',
        'Cached input, per million',
        data.price_cached,
        'cached_help',
        'In cents. Usually about a tenth of the input price. On a conversation that re-sends a long prompt every turn this is most of the bill, so leaving it at the input price overstates the cost considerably.',
      ],
    ]) {
      const input = document.createElement('input');
      input.type = 'number';
      input.id = id;
      input.min = '0';
      input.step = '0.01';
      input.className = 'days';
      input.value = String(value || 0);
      money.appendChild(
        helpers.fieldRow(
          t(key, {}, fallback),
          input,
          t(helpKey, {}, helpFallback),
        ),
      );
    }
    return [
      helpers.section(t('title', {}, 'Prices, if you want a cost'), money),
      helpers.note(
        t(
          'help',
          {},
          'Leave these at zero and sessions report token counts and no money. That is the honest default — a wrong price is worse than no price, so nothing is guessed from the model name. Prices you set here are remembered against this provider and model, so changing the model and changing back does not lose them.',
        ),
      ),
    ];
  },
};
