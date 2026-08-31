/* Core card package: browser-safe content rating for reaction images. */
window.ZogographAiImageRatingCard = {
  manifest: Object.freeze({
    id: 'core-ai-image-rating',
    locales: ['core-ai-image-rating.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-image-rating.label': 'Rating',
        'core-ai-image-rating.g': 'G only',
        'core-ai-image-rating.pg': 'PG and under',
        'core-ai-image-rating.pg13': 'PG-13 and under',
        'core-ai-image-rating.help':
          'This appears unannounced in a browser, possibly at work, chosen by a language model from a search term it invented. G is the sensible default and there is no good argument against it.',
      }),
      es: Object.freeze({
        'core-ai-image-rating.label': 'Clasificación',
        'core-ai-image-rating.g': 'Solo G',
        'core-ai-image-rating.pg': 'PG y menores',
        'core-ai-image-rating.pg13': 'PG-13 y menores',
        'core-ai-image-rating.help':
          'Esto aparece sin aviso en un navegador, posiblemente en el trabajo, elegido por un modelo de lenguaje a partir de un término de búsqueda que inventó. G es el valor predeterminado sensato y no hay un buen argumento en contra.',
      }),
      hi: Object.freeze({
        'core-ai-image-rating.label': 'रेटिंग',
        'core-ai-image-rating.g': 'केवल G',
        'core-ai-image-rating.pg': 'PG और नीचे',
        'core-ai-image-rating.pg13': 'PG-13 और नीचे',
        'core-ai-image-rating.help':
          'यह ब्राउज़र में बिना सूचना के, संभवतः कार्यस्थल पर, भाषा मॉडल द्वारा बनाए गए खोज शब्द से दिखाई देता है। G उचित डिफ़ॉल्ट है और इसके विरुद्ध कोई अच्छा तर्क नहीं है।',
      }),
      ar: Object.freeze({
        'core-ai-image-rating.label': 'التصنيف',
        'core-ai-image-rating.g': 'G فقط',
        'core-ai-image-rating.pg': 'PG وما دون',
        'core-ai-image-rating.pg13': 'PG-13 وما دون',
        'core-ai-image-rating.help':
          'يظهر هذا دون إعلان في متصفح، ربما في العمل، وقد اختاره نموذج لغوي من مصطلح بحث اخترعه. G هو الإعداد الافتراضي المعقول ولا توجد حجة جيدة ضده.',
      }),
      zh: Object.freeze({
        'core-ai-image-rating.label': '分级',
        'core-ai-image-rating.g': '仅限 G',
        'core-ai-image-rating.pg': 'PG 及以下',
        'core-ai-image-rating.pg13': 'PG-13 及以下',
        'core-ai-image-rating.help':
          '这会在浏览器中未经提示地出现，可能是在工作场所；它由语言模型根据自行编造的搜索词选定。G 是合理的默认值，没有充分理由反对它。',
      }),
    }),
  }),
  render(input, helpers) {
    const { fieldRow, i18n } = helpers;
    const rating = document.createElement('select');
    rating.id = 'aiRating';
    for (const [value, key, fallback] of [
      ['g', 'core-ai-image-rating.g', 'G only'],
      ['pg', 'core-ai-image-rating.pg', 'PG and under'],
      ['pg-13', 'core-ai-image-rating.pg13', 'PG-13 and under'],
    ]) {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = i18n.t(key, {}, fallback);
      option.selected = input.rating === value;
      rating.appendChild(option);
    }
    return fieldRow(
      i18n.t('core-ai-image-rating.label', {}, 'Rating'),
      rating,
      i18n.t(
        'core-ai-image-rating.help',
        {},
        'This appears unannounced in a browser, possibly at work, chosen by a language model from a search term it invented. G is the sensible default and there is no good argument against it.',
      ),
    );
  },
};
