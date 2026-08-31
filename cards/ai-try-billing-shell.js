/* Core card package: static shells for tool trials and billing history. */
window.ZogographAiTryBillingShellCard = {
  manifest: Object.freeze({
    id: 'core-ai-try-billing-shell',
    locales: ['core-ai-try-billing-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-try-billing-shell.try_title': 'Try a tool',
        'core-ai-try-billing-shell.try_help':
          'Runs one tool with parameters you choose and shows exactly what comes back — the same thing the model would receive, in the same shape. Useful for two things: seeing whether a tool actually works on this install before switching it on, and reading what the model is being told when its answer looks wrong. Nothing here involves the model and nothing leaves the machine.',
        'core-ai-try-billing-shell.billing_title': 'Billing',
        'core-ai-try-billing-shell.billing_help':
          'Every conversation, with the tokens it used and what that came to. Costs are shown only when prices are set — without them this reports token counts and no money, because a wrong price is worse than no price.',
      }),
      es: Object.freeze({
        'core-ai-try-billing-shell.try_title': 'Probar una herramienta',
        'core-ai-try-billing-shell.try_help':
          'Ejecuta una herramienta con los parámetros que elija y muestra exactamente lo que vuelve, lo mismo que recibiría el modelo y con la misma forma. Sirve para dos cosas: comprobar que una herramienta funciona en esta instalación antes de activarla y leer lo que se le comunica al modelo cuando su respuesta parece incorrecta. Nada aquí implica al modelo y nada sale de la máquina.',
        'core-ai-try-billing-shell.billing_title': 'Facturación',
        'core-ai-try-billing-shell.billing_help':
          'Cada conversación, con los tokens que usó y en cuánto se tradujeron. Los costes se muestran solo cuando se fijan precios; sin ellos se informan conteos de tokens y no dinero, porque un precio incorrecto es peor que no tener precio.',
      }),
      hi: Object.freeze({
        'core-ai-try-billing-shell.try_title': 'टूल आज़माएँ',
        'core-ai-try-billing-shell.try_help':
          'आपके चुने पैरामीटर के साथ एक टूल चलाता है और ठीक वही दिखाता है जो वापस आता है—वही चीज़ जो मॉडल को उसी रूप में मिलेगी। दो बातों के लिए उपयोगी: चालू करने से पहले देखना कि टूल इस इंस्टॉल पर सच में काम करता है, और जब जवाब गलत लगे तब पढ़ना कि मॉडल को क्या बताया जा रहा है। यहाँ मॉडल शामिल नहीं है और कुछ भी मशीन से बाहर नहीं जाता।',
        'core-ai-try-billing-shell.billing_title': 'बिलिंग',
        'core-ai-try-billing-shell.billing_help':
          'हर बातचीत, उसके उपयोग किए टोकन और उनका परिणाम। लागत केवल कीमतें सेट होने पर दिखती है—उनके बिना यह टोकन गिनती दिखाता है, पैसा नहीं, क्योंकि गलत कीमत न होने से भी बुरी है।',
      }),
      ar: Object.freeze({
        'core-ai-try-billing-shell.try_title': 'جرّب أداة',
        'core-ai-try-billing-shell.try_help':
          'يشغّل أداة واحدة بالمعلمات التي تختارها ويعرض بالضبط ما يعود — الشيء نفسه الذي سيتلقاه النموذج وبالشكل نفسه. يفيد في أمرين: رؤية ما إذا كانت الأداة تعمل فعلًا في هذا التثبيت قبل تشغيلها، وقراءة ما يُقال للنموذج حين تبدو إجابته خاطئة. لا يتضمن شيء هنا النموذج ولا يغادر شيء الآلة.',
        'core-ai-try-billing-shell.billing_title': 'الفوترة',
        'core-ai-try-billing-shell.billing_help':
          'كل محادثة والرموز المميزة التي استخدمتها وما آل إليه ذلك. لا تظهر التكاليف إلا عند تعيين الأسعار — ومن دونها يعرض هذا عدد الرموز ولا مالًا، لأن السعر الخطأ أسوأ من غياب السعر.',
      }),
      zh: Object.freeze({
        'core-ai-try-billing-shell.try_title': '试用工具',
        'core-ai-try-billing-shell.try_help':
          '使用您选择的参数运行一个工具，并准确显示返回的内容——模型会以相同形式收到同样的内容。它可用于两件事：在启用前确认工具在此安装上确实可用；以及当模型回答看似错误时，读取模型得到的信息。这里不会涉及模型，任何内容都不会离开机器。',
        'core-ai-try-billing-shell.billing_title': '费用',
        'core-ai-try-billing-shell.billing_help':
          '每段对话以及它使用的令牌数和相应费用。只有在设置价格时才会显示费用；未设置时这里报告令牌数而非金额，因为错误的价格比没有价格更糟。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-try-billing-shell.${input.key}`,
      {},
      input.fallback,
    );
  },
};
