/* Core card package: composition of the complete AI image-settings surface. */
window.ZogographAiImageSettingsCard = {
  manifest: Object.freeze({
    id: 'core-ai-image-settings',
    locales: ['core-ai-image-settings.'],
    helpers: [
      'fieldRow',
      'section',
      'note',
      'styleFinder',
      'renderCard',
      'providerCard',
      'stylesCard',
      'renderOptionsCard',
      'limitsCard',
      'searchCard',
      'ratingCard',
      'costsCard',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-image-settings.title': 'Images',
        'core-ai-image-settings.help':
          'Entirely optional, and off until keyed. Generating a picture costs money; searching for an animation is a free read against somebody else’s quota. They are limited separately for that reason.',
      }),
      es: Object.freeze({
        'core-ai-image-settings.title': 'Imágenes',
        'core-ai-image-settings.help':
          'Es totalmente opcional y permanece apagado hasta configurarlo. Generar una imagen cuesta dinero; buscar una animación es una lectura gratuita contra la cuota de otra persona. Por eso se limitan por separado.',
      }),
      hi: Object.freeze({
        'core-ai-image-settings.title': 'छवियाँ',
        'core-ai-image-settings.help':
          'पूरी तरह वैकल्पिक और कुंजी देने तक बंद। चित्र बनाना पैसे लेता है; एनीमेशन खोजना किसी और की कोटा के विरुद्ध मुफ्त रीड है। इसी कारण उन्हें अलग-अलग सीमित किया जाता है।',
      }),
      ar: Object.freeze({
        'core-ai-image-settings.title': 'الصور',
        'core-ai-image-settings.help':
          'اختياري تمامًا ومعطل إلى أن يُدخل المفتاح. إنشاء صورة يكلف مالًا؛ والبحث عن رسم متحرك قراءة مجانية مقابل حصة شخص آخر. لذلك يُحدّ كل منهما منفصلًا.',
      }),
      zh: Object.freeze({
        'core-ai-image-settings.title': '图像',
        'core-ai-image-settings.help':
          '完全可选，在配置密钥前处于关闭状态。生成图片需要花钱；搜索动画则是对他人配额的一次免费读取。因此两者分别受到限制。',
      }),
    }),
  }),
  render(input, helpers) {
    const { pictures } = input;
    const {
      fieldRow,
      section,
      note,
      styleFinder,
      renderCard,
      providerCard,
      stylesCard,
      renderOptionsCard,
      limitsCard,
      searchCard,
      ratingCard,
      costsCard,
      i18n,
    } = helpers;
    const call = (card, id, data, extra = {}) =>
      renderCard(card, id, data, { fieldRow, section, note, i18n, ...extra });
    const form = document.createElement('div');
    form.className = 'settings';
    const panel = document.querySelector('#aiImagesPanel');
    const title = panel?.querySelector('.panel-head h2');
    if (title)
      title.textContent = i18n.t('core-ai-image-settings.title', {}, 'Images');
    const help = panel?.querySelector('.sub-note');
    if (help)
      help.textContent = i18n.t(
        'core-ai-image-settings.help',
        {},
        'Entirely optional, and off until keyed. Generating a picture costs money; searching for an animation is a free read against somebody else’s quota. They are limited separately for that reason.',
      );
    form.append(...call(providerCard, 'core-ai-image-provider', { pictures }));
    form.appendChild(
      call(stylesCard, 'core-ai-image-styles', { pictures }, { styleFinder }),
    );
    form.append(
      ...call(renderOptionsCard, 'core-ai-image-render-options', { pictures }),
    );
    form.append(...call(limitsCard, 'core-ai-image-limits', { pictures }));
    form.append(...call(searchCard, 'core-ai-image-search', { pictures }));
    form.appendChild(
      call(ratingCard, 'core-ai-image-rating', { rating: pictures.rating }),
    );
    form.append(...call(costsCard, 'core-ai-image-costs', { pictures }));
    const wrap = document.createDocumentFragment();
    wrap.appendChild(form);
    return wrap;
  },
};
