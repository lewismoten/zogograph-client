window.ZogographReportsNotReadyCard = {
  manifest: Object.freeze({
    id: 'core-reports-not-ready',
    locales: ['core-reports-not-ready.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-reports-not-ready.text':
          '{{assistant}} is not set up, so nothing can be written: {{why}}',
      }),
      es: Object.freeze({
        'core-reports-not-ready.text':
          '{{assistant}} no está configurado, por lo que no se puede escribir nada: {{why}}',
      }),
      hi: Object.freeze({
        'core-reports-not-ready.text':
          '{{assistant}} सेट अप नहीं है, इसलिए कुछ भी नहीं लिखा जा सकता: {{why}}',
      }),
      ar: Object.freeze({
        'core-reports-not-ready.text':
          'لم يُعَد {{assistant}}، لذا لا يمكن كتابة شيء: {{why}}',
      }),
      zh: Object.freeze({
        'core-reports-not-ready.text':
          '{{assistant}} 尚未设置，因此无法写出摘要：{{why}}',
      }),
    }),
  }),
  render(data, helpers) {
    return helpers.note(
      helpers.i18n.t('core-reports-not-ready.text', {
        assistant: data.who.name,
        why: data.why,
      }),
    );
  },
};
