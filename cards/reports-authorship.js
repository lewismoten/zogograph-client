window.ZogographReportsAuthorshipCard = {
  manifest: Object.freeze({
    id: 'core-reports-authorship',
    locales: ['core-reports-authorship.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-reports-authorship.text':
          'Written by {{assistant}}, which is a language model reading the figures. The figures themselves are on the dashboard, and are what to check if a summary says something surprising.',
      }),
      es: Object.freeze({
        'core-reports-authorship.text':
          'Escrito por {{assistant}}, un modelo de lenguaje que lee las cifras. Las cifras están en el panel y son lo que debe comprobar si un resumen dice algo sorprendente.',
      }),
      hi: Object.freeze({
        'core-reports-authorship.text':
          '{{assistant}} द्वारा लिखा गया, जो आंकड़ों को पढ़ने वाला भाषा मॉडल है। आंकड़े डैशबोर्ड पर हैं और यदि सारांश कुछ आश्चर्यजनक कहे तो उन्हीं की जाँच करनी है।',
      }),
      ar: Object.freeze({
        'core-reports-authorship.text':
          'كتبه {{assistant}}، وهو نموذج لغوي يقرأ الأرقام. الأرقام نفسها موجودة في لوحة المعلومات، وهي ما ينبغي التحقق منه إن قال الملخص شيئًا مفاجئًا.',
      }),
      zh: Object.freeze({
        'core-reports-authorship.text':
          '由 {{assistant}} 撰写，它是读取数据的语言模型。数据本身在仪表板中；若摘要出现意外内容，应检查这些数据。',
      }),
    }),
  }),
  render(data, helpers) {
    return helpers.note(
      helpers.i18n.t('core-reports-authorship.text', {
        assistant: data.who.name,
      }),
    );
  },
};
