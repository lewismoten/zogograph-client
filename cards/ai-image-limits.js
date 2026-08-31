/* Core card package: image-generation rate and behavior limits. */
window.ZogographAiImageLimitsCard = {
  manifest: Object.freeze({
    id: 'core-ai-image-limits',
    locales: ['core-ai-image-limits.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-image-limits.per_hour': 'At most, per hour',
        'core-ai-image-limits.self_portrait': 'Let it redraw itself',
        'core-ai-image-limits.reactions': 'Let it react',
        'core-ai-image-limits.per_hour_help':
          'Across everything on this panel. A model that finds something amusing will keep doing it, and each one is billed — this is what stops a loop running up a bill before anybody notices. Zero switches picture-making off without losing the rest of these settings.',
        'core-ai-image-limits.per_hour_used':
          '{{count, number}} made in the last hour.',
        'core-ai-image-limits.self_portrait_help':
          'A novelty: it can generate its own portrait, squared off and shrunk to a thumbnail. Shares the hourly limit and asks permission each time.',
        'core-ai-image-limits.reactions_help':
          'Short animated images, searched rather than generated, for the occasional aside. It chooses a mood, not an image — what comes back is whatever the catalogue returns.',
      }),
      es: Object.freeze({
        'core-ai-image-limits.per_hour': 'Como máximo por hora',
        'core-ai-image-limits.self_portrait': 'Permitir que se redibuje',
        'core-ai-image-limits.reactions': 'Permitir reacciones',
        'core-ai-image-limits.per_hour_help':
          'Para todo este panel. Un modelo que encuentra algo divertido seguirá haciéndolo y cada uno se factura; esto evita que un bucle genere una factura antes de que alguien lo note. Cero desactiva la creación de imágenes sin perder el resto de esta configuración.',
        'core-ai-image-limits.per_hour_used':
          '{{count, number}} creadas en la última hora.',
        'core-ai-image-limits.self_portrait_help':
          'Una novedad: puede generar su propio retrato, cuadrado y reducido a una miniatura. Comparte el límite por hora y pide permiso cada vez.',
        'core-ai-image-limits.reactions_help':
          'Imágenes animadas cortas, buscadas en vez de generadas, para algún aparte ocasional. Elige un estado de ánimo, no una imagen; lo que vuelve es lo que devuelva el catálogo.',
      }),
      hi: Object.freeze({
        'core-ai-image-limits.per_hour': 'अधिकतम प्रति घंटा',
        'core-ai-image-limits.self_portrait': 'इसे स्वयं को फिर से बनाने दें',
        'core-ai-image-limits.reactions': 'इसे प्रतिक्रिया देने दें',
        'core-ai-image-limits.per_hour_help':
          'इस पैनल की हर चीज़ में। जिस चीज़ को मॉडल मज़ेदार पाता है उसे करता रहेगा और हर एक का बिल होगा — यही किसी के ध्यान देने से पहले लूप को बिल बढ़ाने से रोकता है। शून्य बाकी सेटिंग खोए बिना चित्र बनाना बंद कर देता है।',
        'core-ai-image-limits.per_hour_used':
          'पिछले घंटे में {{count, number}} बनाए गए।',
        'core-ai-image-limits.self_portrait_help':
          'एक नवीनता: यह अपना चित्र बना सकता है, चौकोर और थंबनेल तक छोटा। यह प्रति घंटा सीमा साझा करता है और हर बार अनुमति मांगता है।',
        'core-ai-image-limits.reactions_help':
          'कभी-कभार टिप्पणी के लिए छोटे एनिमेटेड चित्र, बनाए नहीं बल्कि खोजे जाते हैं। यह मूड चुनता है, चित्र नहीं — वापस वही आता है जो कैटलॉग लौटाता है।',
      }),
      ar: Object.freeze({
        'core-ai-image-limits.per_hour': 'الحد الأقصى في الساعة',
        'core-ai-image-limits.self_portrait': 'اسمح لها بإعادة رسم نفسها',
        'core-ai-image-limits.reactions': 'اسمح لها بالتفاعل',
        'core-ai-image-limits.per_hour_help':
          'عبر كل ما في هذه اللوحة. النموذج الذي يجد شيئًا مسليًا سيواصل فعله، وكل واحدة تُفوتر — وهذا ما يمنع حلقة من رفع فاتورة قبل أن يلاحظ أحد. الصفر يعطّل صنع الصور دون فقد بقية هذه الإعدادات.',
        'core-ai-image-limits.per_hour_used':
          'تم إنشاء {{count, number}} في الساعة الماضية.',
        'core-ai-image-limits.self_portrait_help':
          'أمر طريف: يمكنه إنشاء صورته الشخصية، مربعة ومصغرة إلى صورة مصغرة. يشارك الحد بالساعة ويطلب الإذن في كل مرة.',
        'core-ai-image-limits.reactions_help':
          'صور متحركة قصيرة، تُبحث ولا تُنشأ، لتعليق عرضي. يختار مزاجًا لا صورة — وما يعود هو ما يعيده الفهرس.',
      }),
      zh: Object.freeze({
        'core-ai-image-limits.per_hour': '每小时最多',
        'core-ai-image-limits.self_portrait': '允许它重绘自己',
        'core-ai-image-limits.reactions': '允许它作出反应',
        'core-ai-image-limits.per_hour_help':
          '适用于此面板上的所有内容。模型发现有趣的内容会不断重复，每次都会计费——此项可防止循环在有人注意前累积账单。设为零会关闭图片生成，同时保留其余设置。',
        'core-ai-image-limits.per_hour_used':
          '过去一小时内已生成 {{count, number}} 张。',
        'core-ai-image-limits.self_portrait_help':
          '一个新奇功能：它可以生成自己的肖像，裁成正方形并缩小为缩略图。它与其他功能共用每小时限制，并且每次都会请求许可。',
        'core-ai-image-limits.reactions_help':
          '用于偶尔插话的短动画图片，通过搜索而非生成获得。它选择心情而不是图片——返回的内容取决于目录返回什么。',
      }),
    }),
  }),
  render(input, helpers) {
    const { pictures } = input;
    const { fieldRow, i18n } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const perHour = document.createElement('input');
    perHour.type = 'number';
    perHour.id = 'aiPerHour';
    perHour.min = '0';
    perHour.max = '200';
    perHour.className = 'days';
    perHour.value = String(pictures.per_hour);
    const perHourHelp =
      i18n.t(
        'core-ai-image-limits.per_hour_help',
        {},
        'Across everything on this panel. A model that finds something amusing will keep doing it, and each one is billed — this is what stops a loop running up a bill before anybody notices. Zero switches picture-making off without losing the rest of these settings.',
      ) +
      (pictures.budget
        ? ` ${i18n.t('core-ai-image-limits.per_hour_used', { count: pictures.budget.used }, '{{count, number}} made in the last hour.')}`
        : '');
    const self = document.createElement('input');
    self.type = 'checkbox';
    self.id = 'aiSelfPortrait';
    self.checked = pictures.self_portrait;
    const reactions = document.createElement('input');
    reactions.type = 'checkbox';
    reactions.id = 'aiReactions';
    reactions.checked = pictures.reactions;
    return [
      fieldRow(
        t('core-ai-image-limits.per_hour', {}, 'At most, per hour'),
        perHour,
        perHourHelp,
      ),
      fieldRow(
        t('core-ai-image-limits.self_portrait', {}, 'Let it redraw itself'),
        self,
        i18n.t(
          'core-ai-image-limits.self_portrait_help',
          {},
          'A novelty: it can generate its own portrait, squared off and shrunk to a thumbnail. Shares the hourly limit and asks permission each time.',
        ),
      ),
      fieldRow(
        t('core-ai-image-limits.reactions', {}, 'Let it react'),
        reactions,
        i18n.t(
          'core-ai-image-limits.reactions_help',
          {},
          'Short animated images, searched rather than generated, for the occasional aside. It chooses a mood, not an image — what comes back is whatever the catalogue returns.',
        ),
      ),
    ];
  },
};
