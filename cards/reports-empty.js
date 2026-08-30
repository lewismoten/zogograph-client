window.ZogographReportsEmptyCard = {
  manifest: Object.freeze({
    id: 'core-reports-empty',
    locales: ['core-reports-empty.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-reports-empty.text': 'Nothing written yet.' }),
      es: Object.freeze({
        'core-reports-empty.text': 'Aún no se ha escrito nada.',
      }),
      hi: Object.freeze({
        'core-reports-empty.text': 'अभी तक कुछ नहीं लिखा गया है।',
      }),
      ar: Object.freeze({ 'core-reports-empty.text': 'لم يُكتب شيء بعد.' }),
      zh: Object.freeze({ 'core-reports-empty.text': '尚未写出内容。' }),
    }),
  }),
  render(_data, helpers) {
    return helpers.note(
      helpers.i18n.t('core-reports-empty.text', {}, 'Nothing written yet.'),
    );
  },
};
