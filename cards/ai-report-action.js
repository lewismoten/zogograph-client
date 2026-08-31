/* Core card package: manual assistant-report prompt and status messages. */
window.ZogographAiReportActionCard = {
  manifest: Object.freeze({
    id: 'core-ai-report-action',
    locales: ['core-ai-report-action.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-report-action.title': 'Write a summary',
        'core-ai-report-action.period': 'Period',
        'core-ai-report-action.help': 'Choose daily, weekly, or monthly.',
        'core-ai-report-action.writing':
          'Writing it — this asks the model, so it takes a few seconds.',
        'core-ai-report-action.written': 'Written.',
      }),
      es: Object.freeze({
        'core-ai-report-action.title': 'Escribir un resumen',
        'core-ai-report-action.period': 'Periodo',
        'core-ai-report-action.help': 'Elija diario, semanal o mensual.',
        'core-ai-report-action.writing':
          'Se está escribiendo; esto consulta al modelo, así que tarda unos segundos.',
        'core-ai-report-action.written': 'Escrito.',
      }),
      hi: Object.freeze({
        'core-ai-report-action.title': 'सारांश लिखें',
        'core-ai-report-action.period': 'अवधि',
        'core-ai-report-action.help': 'दैनिक, साप्ताहिक या मासिक चुनें।',
        'core-ai-report-action.writing':
          'लिखा जा रहा है — यह मॉडल से पूछता है, इसलिए कुछ सेकंड लगते हैं।',
        'core-ai-report-action.written': 'लिख दिया गया।',
      }),
      ar: Object.freeze({
        'core-ai-report-action.title': 'اكتب ملخصًا',
        'core-ai-report-action.period': 'الفترة',
        'core-ai-report-action.help': 'اختر يوميًا أو أسبوعيًا أو شهريًا.',
        'core-ai-report-action.writing':
          'جارٍ كتابته — هذا يطلب من النموذج، لذا يستغرق بضع ثوانٍ.',
        'core-ai-report-action.written': 'تمت الكتابة.',
      }),
      zh: Object.freeze({
        'core-ai-report-action.title': '撰写摘要',
        'core-ai-report-action.period': '周期',
        'core-ai-report-action.help': '选择每日、每周或每月。',
        'core-ai-report-action.writing':
          '正在撰写——这会询问模型，因此需要几秒钟。',
        'core-ai-report-action.written': '已写好。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-report-action.${input.key}`,
      {},
      input.fallback,
    );
  },
};
