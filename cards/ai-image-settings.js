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
        'core-ai-image-settings.styles': 'Styles it may use',
        'core-ai-image-settings.styles_help':
          'Tick the ones it may choose from. There are over a hundred, and a model offered twelve will use twelve — most of them are wrong for a monitoring dashboard, so two or three is plenty. Type to narrow the list; the ones you have ticked stay at the top whatever you search for.',
      }),
      es: Object.freeze({
        'core-ai-image-settings.styles': 'Estilos que puede usar',
        'core-ai-image-settings.styles_help':
          'Marque los estilos que puede elegir. Escriba para filtrar; los marcados permanecen arriba.',
      }),
      hi: Object.freeze({
        'core-ai-image-settings.styles': 'शैलियाँ जो यह उपयोग कर सकता है',
        'core-ai-image-settings.styles_help':
          'चुनने योग्य शैलियों को टिक करें। खोजने के लिए लिखें; चुनी हुई शैलियाँ ऊपर रहती हैं।',
      }),
      ar: Object.freeze({
        'core-ai-image-settings.styles': 'الأنماط التي يمكنها استخدامها',
        'core-ai-image-settings.styles_help':
          'حدد الأنماط المسموح بها. اكتب للتصفية وتبقى المحددة في الأعلى.',
      }),
      zh: Object.freeze({
        'core-ai-image-settings.styles': '可使用的风格',
        'core-ai-image-settings.styles_help':
          '勾选它可选择的风格。输入可筛选；已勾选的风格始终位于顶部。',
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
