/* Core card package: static shells for assistant thresholds and images. */
window.ZogographAiLimitsImagesShellCard = {
  manifest: Object.freeze({
    id: 'core-ai-limits-images-shell',
    locales: ['core-ai-limits-images-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-limits-images-shell.limits_title': 'Thresholds',
        'core-ai-limits-images-shell.limits_help':
          'What stops a conversation, and how much of one the model can hold at a time. Every limit here is checked before a request rather than after, so it stops the next call rather than being noticed once the money is gone.',
        'core-ai-limits-images-shell.images_title': 'Images',
        'core-ai-limits-images-shell.images_help':
          'Entirely optional, and off until keyed. Generating a picture costs money; searching for an animation is a free read against somebody else’s quota. They are limited separately for that reason.',
      }),
      es: Object.freeze({
        'core-ai-limits-images-shell.limits_title': 'Umbrales',
        'core-ai-limits-images-shell.limits_help':
          'Qué detiene una conversación y cuánto puede mantener el modelo a la vez. Cada límite se comprueba antes de una solicitud, no después, por lo que detiene la siguiente llamada en vez de descubrirse cuando ya no queda dinero.',
        'core-ai-limits-images-shell.images_title': 'Imágenes',
        'core-ai-limits-images-shell.images_help':
          'Es totalmente opcional y permanece apagado hasta configurarlo. Generar una imagen cuesta dinero; buscar una animación es una lectura gratuita contra la cuota de otra persona. Por eso se limitan por separado.',
      }),
      hi: Object.freeze({
        'core-ai-limits-images-shell.limits_title': 'सीमाएँ',
        'core-ai-limits-images-shell.limits_help':
          'बातचीत को क्या रोकता है और मॉडल एक बार में उसका कितना भाग रख सकता है। यहाँ हर सीमा अनुरोध के बाद नहीं, पहले जाँची जाती है; इसलिए पैसा खत्म हो जाने के बाद पता चलने की बजाय अगली कॉल रुकती है।',
        'core-ai-limits-images-shell.images_title': 'छवियाँ',
        'core-ai-limits-images-shell.images_help':
          'पूरी तरह वैकल्पिक और कुंजी देने तक बंद। चित्र बनाना पैसे लेता है; एनीमेशन खोजना किसी और की कोटा के विरुद्ध मुफ्त रीड है। इसी कारण उन्हें अलग-अलग सीमित किया जाता है।',
      }),
      ar: Object.freeze({
        'core-ai-limits-images-shell.limits_title': 'الحدود',
        'core-ai-limits-images-shell.limits_help':
          'ما يوقف محادثة ومقدار ما يستطيع النموذج الاحتفاظ به منها في المرة الواحدة. يُفحص كل حد هنا قبل الطلب لا بعده، فيوقف المكالمة التالية بدل ملاحظته بعد نفاد المال.',
        'core-ai-limits-images-shell.images_title': 'الصور',
        'core-ai-limits-images-shell.images_help':
          'اختياري تمامًا ومعطل إلى أن يُدخل المفتاح. إنشاء صورة يكلف مالًا؛ والبحث عن رسم متحرك قراءة مجانية مقابل حصة شخص آخر. لذلك يُحدّ كل منهما منفصلًا.',
      }),
      zh: Object.freeze({
        'core-ai-limits-images-shell.limits_title': '阈值',
        'core-ai-limits-images-shell.limits_help':
          '什么会停止对话，以及模型一次能保留多少对话内容。这里的每项限制都在请求之前检查，而不是之后，因此会阻止下一次调用，而不会在钱已花完后才发现。',
        'core-ai-limits-images-shell.images_title': '图像',
        'core-ai-limits-images-shell.images_help':
          '完全可选，在配置密钥前处于关闭状态。生成图片需要花钱；搜索动画则是对他人配额的一次免费读取。因此两者分别受到限制。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-limits-images-shell.${input.key}`,
      {},
      input.fallback,
    );
  },
};
