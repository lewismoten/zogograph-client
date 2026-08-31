/* Core card package: scheduled unattended assistant summaries. */
window.ZogographAiAlertSummariesCard = {
  manifest: Object.freeze({
    id: 'core-ai-alert-summaries',
    locales: ['core-ai-alert-summaries.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-alert-summaries.daily': 'Write a daily summary',
        'core-ai-alert-summaries.weekly': '…a weekly one',
        'core-ai-alert-summaries.monthly': '…a monthly one',
      }),
      es: Object.freeze({
        'core-ai-alert-summaries.daily': 'Escribir un resumen diario',
        'core-ai-alert-summaries.weekly': '…uno semanal',
        'core-ai-alert-summaries.monthly': '…uno mensual',
      }),
      hi: Object.freeze({
        'core-ai-alert-summaries.daily': 'दैनिक सारांश लिखें',
        'core-ai-alert-summaries.weekly': '…साप्ताहिक सारांश',
        'core-ai-alert-summaries.monthly': '…मासिक सारांश',
      }),
      ar: Object.freeze({
        'core-ai-alert-summaries.daily': 'اكتب ملخصًا يوميًا',
        'core-ai-alert-summaries.weekly': '…ملخصًا أسبوعيًا',
        'core-ai-alert-summaries.monthly': '…ملخصًا شهريًا',
      }),
      zh: Object.freeze({
        'core-ai-alert-summaries.daily': '编写每日摘要',
        'core-ai-alert-summaries.weekly': '……每周摘要',
        'core-ai-alert-summaries.monthly': '……每月摘要',
      }),
    }),
  }),
  render(data, helpers) {
    return [
      ['aiDaily', 'daily'],
      ['aiWeekly', 'weekly'],
      ['aiMonthly', 'monthly'],
    ].map(([id, key]) => {
      const input = document.createElement('input');
      input.type = 'checkbox';
      input.id = id;
      input.checked = data.alerts?.[key] === true;
      return helpers.fieldRow(
        helpers.i18n.t(`core-ai-alert-summaries.${key}`, {}, key),
        input,
        '',
      );
    });
  },
};
