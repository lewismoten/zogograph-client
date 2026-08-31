/* Core card package: shells for provider, model, and model-price pages. */
window.ZogographAiModelPagesShellCard = {
  manifest: Object.freeze({
    id: 'core-ai-model-pages-shell',
    locales: ['core-ai-model-pages-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-model-pages-shell.model_title': 'Selected model',
        'core-ai-model-pages-shell.model_help':
          'Choose a platform and an available model. Prices are per million tokens, in cents, and optional — without them everything still works and the running total reports token counts rather than money, because a wrong price is worse than no price.',
        'core-ai-model-pages-shell.providers_title': 'Providers',
        'core-ai-model-pages-shell.providers_help':
          'Choose the platform, its endpoint, and any credentials it needs. Ollama-compatible servers may use no key; remote ones must use HTTPS.',
        'core-ai-model-pages-shell.prices_title': 'Available models',
        'core-ai-model-pages-shell.add': 'Add a model',
        'core-ai-model-pages-shell.prices_help':
          'Models and prices this install has been told about, per provider. Changing the selected model fills its price boxes from here rather than making you look them up again — and a model you have used before keeps whatever you set for it. Nothing here is fetched from anywhere: these are the numbers you were quoted, which is the only honest source for them.',
      }),
      es: Object.freeze({
        'core-ai-model-pages-shell.model_title': 'Modelo seleccionado',
        'core-ai-model-pages-shell.model_help':
          'Qué servicio usar, qué modelo y cuánto cuesta. Los precios son por millón de tokens, en céntimos, y opcionales: sin ellos todo sigue funcionando y el total acumulado informa conteos de tokens en vez de dinero, porque un precio incorrecto es peor que no tener precio.',
        'core-ai-model-pages-shell.providers_title': 'Proveedores',
        'core-ai-model-pages-shell.providers_help':
          'Elija la plataforma, su dirección y las credenciales que necesita. Los servidores compatibles con Ollama pueden no usar clave; los remotos deben usar HTTPS.',
        'core-ai-model-pages-shell.prices_title': 'Modelos disponibles',
        'core-ai-model-pages-shell.add': 'Añadir un modelo',
        'core-ai-model-pages-shell.prices_help':
          'Los precios que se han indicado a esta instalación, por proveedor y modelo. Cambiar el modelo en la página Modelo rellena los campos de precio desde aquí, en vez de obligarle a buscarlos de nuevo; un modelo usado antes conserva lo que usted estableció. No se obtiene nada desde ningún lugar: son los números que le cotizaron, la única fuente honesta para ellos.',
      }),
      hi: Object.freeze({
        'core-ai-model-pages-shell.model_title': 'चुना गया मॉडल',
        'core-ai-model-pages-shell.model_help':
          'किस सेवा से बात करनी है, कौन सा मॉडल और उसकी कीमत। कीमतें प्रति दस लाख टोकन सेंट में हैं और वैकल्पिक हैं—इनके बिना भी सब चलता है और कुल राशि की जगह टोकन गिनती बताता है, क्योंकि गलत कीमत न होने से भी बुरी है।',
        'core-ai-model-pages-shell.providers_title': 'प्रदाता',
        'core-ai-model-pages-shell.providers_help':
          'प्लैटफ़ॉर्म, उसका पता और आवश्यक क्रेडेंशियल चुनें। Ollama-संगत सर्वर को कुंजी की आवश्यकता नहीं हो सकती; दूरस्थ सर्वर को HTTPS उपयोग करना होगा।',
        'core-ai-model-pages-shell.prices_title': 'उपलब्ध मॉडल',
        'core-ai-model-pages-shell.add': 'मॉडल जोड़ें',
        'core-ai-model-pages-shell.prices_help':
          'वे कीमतें जो इस इंस्टॉल को प्रत्येक प्रदाता और मॉडल के लिए बताई गई हैं। मॉडल पृष्ठ पर मॉडल बदलने से यहाँ से मूल्य बॉक्स भरते हैं, फिर से खोजने की जरूरत नहीं—और पहले इस्तेमाल किया मॉडल आपके तय किए मूल्य रखता है। यहाँ कुछ भी कहीं से लाया नहीं जाता: ये वे संख्या हैं जो आपको कोट की गई थीं और उनके लिए वही ईमानदार स्रोत है।',
      }),
      ar: Object.freeze({
        'core-ai-model-pages-shell.model_title': 'النموذج المختار',
        'core-ai-model-pages-shell.model_help':
          'الخدمة التي يُتحدث إليها والنموذج وتكلفته. الأسعار لكل مليون رمز مميز وبالسنتات، وهي اختيارية — من دونها يظل كل شيء يعمل ويعرض الإجمالي الجاري عدد الرموز لا المال، لأن السعر الخاطئ أسوأ من غياب السعر.',
        'core-ai-model-pages-shell.providers_title': 'المزوّدون',
        'core-ai-model-pages-shell.providers_help':
          'اختر المنصة وعنوانها وبيانات الاعتماد التي تحتاجها. قد لا يحتاج خادم متوافق مع Ollama إلى مفتاح؛ ويجب أن يستخدم الخادم البعيد HTTPS.',
        'core-ai-model-pages-shell.prices_title': 'النماذج المتاحة',
        'core-ai-model-pages-shell.add': 'أضف نموذجًا',
        'core-ai-model-pages-shell.prices_help':
          'الأسعار التي أُبلغ بها هذا التثبيت، لكل مزود ونموذج. تغيير النموذج في صفحة النموذج يملأ خانات السعر من هنا بدل أن تبحث عنها ثانية — والنموذج الذي استخدمته من قبل يحتفظ بما ضبطته له. لا يُجلب شيء هنا من أي مكان: هذه هي الأرقام التي عُرضت عليك، وهي المصدر الصادق الوحيد لها.',
      }),
      zh: Object.freeze({
        'core-ai-model-pages-shell.model_title': '已选模型',
        'core-ai-model-pages-shell.model_help':
          '选择要使用的服务、模型以及费用。价格以每百万个令牌的美分计，可选填；不填写时一切仍可正常运行，累计值会报告令牌数量而非金额，因为错误的价格比没有价格更糟。',
        'core-ai-model-pages-shell.providers_title': '提供商',
        'core-ai-model-pages-shell.providers_help':
          '选择平台、其地址以及所需凭据。兼容 Ollama 的服务器可不使用密钥；远程服务器必须使用 HTTPS。',
        'core-ai-model-pages-shell.prices_title': '可用模型',
        'core-ai-model-pages-shell.add': '添加模型',
        'core-ai-model-pages-shell.prices_help':
          '此安装已获知的价格，按提供商和模型区分。在“模型”页面更改模型会从这里填充价格框，不必再次查找；之前用过的模型会保留您为它设定的值。这里不从任何地方抓取数据：这些是您收到的报价数字，也是唯一诚实的来源。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-model-pages-shell.${input.key}`,
      {},
      input.fallback,
    );
  },
};
