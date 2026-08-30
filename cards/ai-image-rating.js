/* Core card package: browser-safe content rating for reaction images. */
window.ZogographAiImageRatingCard = {
  manifest: Object.freeze({
    id: 'core-ai-image-rating',
    locales: ['core-ai-image-rating.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-ai-image-rating.label': 'Rating' }),
      es: Object.freeze({ 'core-ai-image-rating.label': 'Clasificación' }),
      hi: Object.freeze({ 'core-ai-image-rating.label': 'रेटिंग' }),
      ar: Object.freeze({ 'core-ai-image-rating.label': 'التصنيف' }),
      zh: Object.freeze({ 'core-ai-image-rating.label': '分级' }),
    }),
  }),
  render(input, helpers) {
    const { fieldRow, i18n } = helpers;
    const rating = document.createElement('select');
    rating.id = 'aiRating';
    for (const [value, key, fallback] of [
      ['g', 'ai.images_rating_g', 'G only'],
      ['pg', 'ai.images_rating_pg', 'PG and under'],
      ['pg-13', 'ai.images_rating_pg13', 'PG-13 and under'],
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
        'ai.images_rating_help',
        {},
        'This appears unannounced in a browser, possibly at work, chosen by a language model from a search term it invented. G is the sensible default and there is no good argument against it.',
      ),
    );
  },
};
