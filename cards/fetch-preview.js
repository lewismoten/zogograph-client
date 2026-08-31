window.ZogographFetchPreviewCard = {
  manifest: Object.freeze({
    id: 'core-fetch-preview',
    locales: ['core-fetch-preview.'],
    helpers: ['note', 'toggleImages', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-fetch-preview.request_title': 'Ask for a page',
        'core-fetch-preview.request_help':
          'One request, made now, reported in full — what the server actually sends, which is the question while you are setting a check up or arguing with a cache. Type an address with or without {{https}}. The body is shown as text and never inserted as markup; the preview runs in a sandboxed frame of its own, which can neither read this page nor act as you. The body is kept for an hour so the preview can load it, then discarded.',
        'core-fetch-preview.placeholder': 'example.com or https://example.com/',
        'core-fetch-preview.follow_redirects': 'Follow redirects',
        'core-fetch-preview.ask': 'Ask',
        'core-fetch-preview.title': 'Preview of the response',
        'core-fetch-preview.too_large':
          'That body is too large to preview. The raw view still has all of it.',
        'core-fetch-preview.strip_assets': 'Strip it back to placeholders',
        'core-fetch-preview.load_assets':
          "Load the site's own pictures and styling",
        'core-fetch-preview.strip_assets_title':
          'Show it with no pictures and no stylesheets, the way a reader without them meets it',
        'core-fetch-preview.load_assets_title':
          'Fetch the images and stylesheets from the site — which tells it you are looking',
        'core-fetch-preview.assets_external_notice':
          'Pictures and stylesheets are being fetched from the site itself, so it can see that somebody is looking. Scripts stay blocked.',
        'core-fetch-preview.assets_local_notice':
          'Nothing is being fetched from the site: pictures are replaced with placeholders carrying their alt text and the stylesheets are not loaded, so this is roughly what a reader without either would get. It also means the site is not told that you are looking at it.',
        'core-fetch-preview.sandbox_notice':
          'Rendered in a sandbox with no scripts, no forms and no access to this page, so it will not look exactly as it does in a browser — anything the page draws with JavaScript will be missing.',
      }),
      es: Object.freeze({
        'core-fetch-preview.request_title': 'Pedir una página',
        'core-fetch-preview.request_help':
          'Una solicitud realizada ahora e informada completa: lo que el servidor envía realmente, que es la pregunta al configurar una comprobación o discutir con una caché. Escriba una dirección con o sin {{https}}. El cuerpo se muestra como texto y nunca se inserta como marcado; la vista previa se ejecuta en su propio marco aislado, que no puede leer esta página ni actuar como usted. El cuerpo se conserva una hora para que la vista previa pueda cargarlo y luego se descarta.',
        'core-fetch-preview.placeholder': 'ejemplo.com o https://ejemplo.com/',
        'core-fetch-preview.follow_redirects': 'Seguir redirecciones',
        'core-fetch-preview.ask': 'Consultar',
        'core-fetch-preview.title': 'Vista previa de la respuesta',
        'core-fetch-preview.too_large':
          'Ese cuerpo es demasiado grande para obtener una vista previa. La vista sin procesar aún lo contiene completo.',
        'core-fetch-preview.strip_assets': 'Volver a marcadores de posición',
        'core-fetch-preview.load_assets':
          'Cargar las imágenes y estilos del propio sitio',
        'core-fetch-preview.strip_assets_title':
          'Mostrarlo sin imágenes ni hojas de estilo, como lo encontraría un lector sin ellas',
        'core-fetch-preview.load_assets_title':
          'Obtener las imágenes y hojas de estilo del sitio, lo que le indica que estás mirando',
        'core-fetch-preview.assets_external_notice':
          'Las imágenes y hojas de estilo se obtienen del propio sitio, por lo que puede ver que alguien está mirando. Los scripts permanecen bloqueados.',
        'core-fetch-preview.assets_local_notice':
          'No se obtiene nada del sitio: las imágenes se sustituyen por marcadores con su texto alternativo y las hojas de estilo no se cargan, por lo que esto es aproximadamente lo que recibiría un lector sin ninguno de los dos. También significa que no se le informa al sitio de que estás mirando.',
        'core-fetch-preview.sandbox_notice':
          'Se muestra en una zona aislada sin scripts, formularios ni acceso a esta página, por lo que no se verá exactamente como en un navegador: faltará todo lo que la página dibuje con JavaScript.',
      }),
      hi: Object.freeze({
        'core-fetch-preview.request_title': 'एक पेज माँगें',
        'core-fetch-preview.request_help':
          'एक अनुरोध, अभी किया गया और पूरा बताया गया—सर्वर वास्तव में क्या भेजता है, जो जाँच सेट करते समय या कैश पर बहस करते समय महत्वपूर्ण है। {{https}} के साथ या बिना पता लिखें। बॉडी को टेक्स्ट के रूप में दिखाया जाता है और कभी मार्कअप के रूप में नहीं डाला जाता; प्रीव्यू अपने सैंडबॉक्स फ्रेम में चलता है, जो न इस पेज को पढ़ सकता है न आपके रूप में कार्य कर सकता है। प्रीव्यू लोड करने के लिए बॉडी एक घंटे रखी जाती है, फिर हटा दी जाती है।',
        'core-fetch-preview.placeholder': 'example.com या https://example.com/',
        'core-fetch-preview.follow_redirects': 'रीडायरेक्ट का अनुसरण करें',
        'core-fetch-preview.ask': 'माँगें',
        'core-fetch-preview.title': 'प्रतिक्रिया का पूर्वावलोकन',
        'core-fetch-preview.too_large':
          'यह मुख्य भाग पूर्वावलोकन के लिए बहुत बड़ा है। कच्चे दृश्य में अभी भी यह पूरा है।',
        'core-fetch-preview.strip_assets': 'इसे फिर से प्लेसहोल्डर पर लाएँ',
        'core-fetch-preview.load_assets':
          'साइट के अपने चित्र और स्टाइल लोड करें',
        'core-fetch-preview.strip_assets_title':
          'इसे चित्रों और स्टाइलशीट के बिना दिखाएँ, जैसे कोई पाठक उन्हें बिना देखता है',
        'core-fetch-preview.load_assets_title':
          'साइट से चित्र और स्टाइलशीट फ़ेच करें — जिससे उसे पता चलता है कि आप देख रहे हैं',
        'core-fetch-preview.assets_external_notice':
          'चित्र और स्टाइलशीट साइट से ही फ़ेच की जा रही हैं, इसलिए वह देख सकती है कि कोई देख रहा है। स्क्रिप्ट अवरोधित रहती हैं।',
        'core-fetch-preview.assets_local_notice':
          'साइट से कुछ भी फ़ेच नहीं किया जा रहा: चित्रों की जगह उनके alt text वाले प्लेसहोल्डर हैं और स्टाइलशीट लोड नहीं की जातीं, इसलिए यह लगभग वही है जो इनके बिना पाठक को मिलेगा। इसका अर्थ यह भी है कि साइट को नहीं बताया जाता कि आप देख रहे हैं।',
        'core-fetch-preview.sandbox_notice':
          'यह बिना स्क्रिप्ट, फ़ॉर्म या इस पेज तक पहुँच वाले सैंडबॉक्स में रेंडर होता है, इसलिए यह ब्राउज़र जैसा बिल्कुल नहीं दिखेगा — पेज JavaScript से जो भी बनाता है वह गायब होगा।',
      }),
      ar: Object.freeze({
        'core-fetch-preview.request_title': 'اطلب صفحة',
        'core-fetch-preview.request_help':
          'طلب واحد يُجرى الآن ويُبلَّغ كاملًا — ما يرسله الخادم فعلًا، وهو السؤال عند إعداد فحص أو مناقشة ذاكرة مؤقتة. اكتب عنوانًا مع {{https}} أو بدونه. يُعرض النص كنص ولا يُدرج قط كترميز؛ تعمل المعاينة في إطار معزول خاص بها فلا تقرأ هذه الصفحة ولا تتصرف بصفتك. يُحتفظ بالنص ساعة كي تحمّله المعاينة ثم يُتلف.',
        'core-fetch-preview.placeholder': 'example.com أو https://example.com/',
        'core-fetch-preview.follow_redirects': 'اتبع عمليات إعادة التوجيه',
        'core-fetch-preview.ask': 'اطلب',
        'core-fetch-preview.title': 'معاينة الاستجابة',
        'core-fetch-preview.too_large':
          'هذا المحتوى كبير جدًا للمعاينة. ما زالت العرض الخام يحتوي عليه كله.',
        'core-fetch-preview.strip_assets': 'أعِده إلى عناصر نائبة',
        'core-fetch-preview.load_assets': 'حمّل صور الموقع وأنماطه الخاصة',
        'core-fetch-preview.strip_assets_title':
          'اعرضه بلا صور أو أوراق أنماط، كما يراه قارئ لا يملكهما',
        'core-fetch-preview.load_assets_title':
          'اجلب صور الموقع وأوراق أنماطه، وهذا يخبره بأنك تنظر',
        'core-fetch-preview.assets_external_notice':
          'يجري جلب الصور وأوراق الأنماط من الموقع نفسه، لذا يمكنه رؤية أن شخصًا ما ينظر. تبقى البرامج النصية محجوبة.',
        'core-fetch-preview.assets_local_notice':
          'لا يُجلب شيء من الموقع: تُستبدل الصور بعناصر نائبة تحمل النص البديل ولا تُحمّل أوراق الأنماط، لذلك هذا تقريبًا ما يحصل عليه قارئ بلا كليهما. كما يعني ذلك أن الموقع لا يُخبَر بأنك تنظر إليه.',
        'core-fetch-preview.sandbox_notice':
          'يُعرض في بيئة معزولة بلا برامج نصية أو نماذج أو وصول إلى هذه الصفحة، لذلك لن يبدو تمامًا كما في المتصفح — أي شيء يرسمه الموقع بـ JavaScript سيكون مفقودًا.',
      }),
      zh: Object.freeze({
        'core-fetch-preview.request_title': '请求一个页面',
        'core-fetch-preview.request_help':
          '一项现在发出的请求，并完整报告服务器实际发送的内容；这正是在配置检查或与缓存争论时需要知道的。输入带或不带 {{https}} 的地址。正文会以文本显示，绝不会作为标记插入；预览在独立的沙盒框架中运行，既不能读取本页，也不能以您的身份操作。正文会保留一小时以供预览加载，然后被丢弃。',
        'core-fetch-preview.placeholder': 'example.com 或 https://example.com/',
        'core-fetch-preview.follow_redirects': '跟随重定向',
        'core-fetch-preview.ask': '请求',
        'core-fetch-preview.title': '响应预览',
        'core-fetch-preview.too_large':
          '该正文过大，无法预览。原始视图仍包含全部内容。',
        'core-fetch-preview.strip_assets': '还原为占位符',
        'core-fetch-preview.load_assets': '加载网站自己的图片和样式',
        'core-fetch-preview.strip_assets_title':
          '不显示图片和样式表，就像没有它们的读者所看到的一样',
        'core-fetch-preview.load_assets_title':
          '获取网站自己的图片和样式表——这会告诉网站你正在查看',
        'core-fetch-preview.assets_external_notice':
          '图片和样式表正从网站本身获取，因此它能看到有人正在查看。脚本仍被阻止。',
        'core-fetch-preview.assets_local_notice':
          '不会从网站获取任何内容：图片会替换为带有替代文本的占位符，且不加载样式表，因此这大致是没有二者的读者会得到的内容。这也意味着网站不会知道你正在查看它。',
        'core-fetch-preview.sandbox_notice':
          '内容在没有脚本、表单或访问此页面权限的沙箱中渲染，因此不会完全像在浏览器中那样显示——页面用 JavaScript 绘制的任何内容都会缺失。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-fetch-preview.${key}`,
      {},
      this.manifest.catalogs.en[`core-fetch-preview.${key}`],
    );
  },
  render(data, helpers) {
    const wrap = document.createDocumentFragment();
    if (!data.preview_token) {
      wrap.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-fetch-preview.too_large',
            {},
            'That body is too large to preview. The raw view still has all of it.',
          ),
        ),
      );
      return wrap;
    }

    const tools = document.createElement('div');
    tools.className = 'panel-tools';
    const toggle = document.createElement('button');
    toggle.className = 'link';
    toggle.textContent = data.images
      ? helpers.i18n.t(
          'core-fetch-preview.strip_assets',
          {},
          'Strip it back to placeholders',
        )
      : helpers.i18n.t(
          'core-fetch-preview.load_assets',
          {},
          "Load the site's own pictures and styling",
        );
    toggle.title = data.images
      ? helpers.i18n.t(
          'core-fetch-preview.strip_assets_title',
          {},
          'Show it with no pictures and no stylesheets, the way a reader without them meets it',
        )
      : helpers.i18n.t(
          'core-fetch-preview.load_assets_title',
          {},
          'Fetch the images and stylesheets from the site — which tells it you are looking',
        );
    toggle.addEventListener('click', helpers.toggleImages);
    tools.appendChild(toggle);
    wrap.appendChild(tools);

    const frame = document.createElement('iframe');
    frame.className = 'preview-frame';
    frame.setAttribute('sandbox', '');
    frame.title = helpers.i18n.t(
      'core-fetch-preview.title',
      {},
      'Preview of the response',
    );
    frame.src =
      `preview.php?token=${encodeURIComponent(data.preview_token)}` +
      `&images=${data.images ? '1' : '0'}`;
    wrap.appendChild(frame);
    wrap.appendChild(
      helpers.note(
        data.images
          ? helpers.i18n.t(
              'core-fetch-preview.assets_external_notice',
              {},
              'Pictures and stylesheets are being fetched from the site itself, so it can see that somebody is looking. Scripts stay blocked.',
            )
          : helpers.i18n.t(
              'core-fetch-preview.assets_local_notice',
              {},
              'Nothing is being fetched from the site: pictures are replaced with placeholders carrying their alt text and the stylesheets are not loaded, so this is roughly what a reader without either would get. It also means the site is not told that you are looking at it.',
            ),
      ),
    );
    wrap.appendChild(
      helpers.note(
        helpers.i18n.t(
          'core-fetch-preview.sandbox_notice',
          {},
          'Rendered in a sandbox with no scripts, no forms and no access to this page, so it will not look exactly as it does in a browser — anything the page draws with JavaScript will be missing.',
        ),
      ),
    );
    return wrap;
  },
};
