/* Core card package: style-picker field and its explanatory copy. */
window.ZogographAiImageStylesCard = {
  manifest: Object.freeze({
    id: 'core-ai-image-styles',
    locales: ['core-ai-image-styles.'],
    helpers: ['fieldRow', 'styleFinder', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-image-styles.label': 'Styles it may use',
        'core-ai-image-styles.help':
          'Tick the ones it may choose from. There are over a hundred, and a model offered twelve will use twelve — most of them are wrong for a monitoring dashboard, so two or three is plenty. Type to narrow the list; the ones you have ticked stay at the top whatever you search for.',
      }),
      es: Object.freeze({
        'core-ai-image-styles.label': 'Estilos que puede usar',
        'core-ai-image-styles.help':
          'Marque los estilos permitidos. Escriba para filtrar; los marcados permanecen arriba.',
      }),
      hi: Object.freeze({
        'core-ai-image-styles.label': 'शैलियाँ जो यह उपयोग कर सकता है',
        'core-ai-image-styles.help':
          'अनुमत शैलियों को टिक करें। खोजने के लिए लिखें; चुनी हुई ऊपर रहती हैं।',
      }),
      ar: Object.freeze({
        'core-ai-image-styles.label': 'الأنماط التي يمكنها استخدامها',
        'core-ai-image-styles.help':
          'حدد الأنماط المسموح بها. اكتب للتصفية وتبقى المحددة في الأعلى.',
      }),
      zh: Object.freeze({
        'core-ai-image-styles.label': '可使用的风格',
        'core-ai-image-styles.help':
          '勾选允许选择的风格。输入可筛选；已勾选的始终位于顶部。',
      }),
    }),
  }),
  render(input, helpers) {
    const { fieldRow, styleFinder, i18n } = helpers;
    return fieldRow(
      i18n.t('core-ai-image-styles.label', {}, 'Styles it may use'),
      styleFinder(input.pictures),
      i18n.t(
        'core-ai-image-styles.help',
        {},
        'Tick the ones it may choose from. There are over a hundred, and a model offered twelve will use twelve — most of them are wrong for a monitoring dashboard, so two or three is plenty. Type to narrow the list; the ones you have ticked stay at the top whatever you search for.',
      ),
    );
  },
};
