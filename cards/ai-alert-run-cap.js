/* Core card package: maximum unattended assistant investigations per cron run. */
window.ZogographAiAlertRunCapCard = {
  manifest: Object.freeze({
    id: 'core-ai-alert-run-cap',
    locales: ['core-ai-alert-run-cap.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-alert-run-cap.label': 'At most, per run',
        'core-ai-alert-run-cap.help':
          'A disk filling up trips several rules at once. This is what stops a storm becoming a bill.',
      }),
      es: Object.freeze({
        'core-ai-alert-run-cap.label': 'Como máximo, por ejecución',
        'core-ai-alert-run-cap.help':
          'Un disco que se llena activa varias reglas a la vez. Esto impide que una tormenta se convierta en una factura.',
      }),
      hi: Object.freeze({
        'core-ai-alert-run-cap.label': 'प्रति रन अधिकतम',
        'core-ai-alert-run-cap.help':
          'डिस्क भरने पर एक साथ कई नियम सक्रिय होते हैं। यही स्थिति को खर्चों के तूफान में बदलने से रोकता है।',
      }),
      ar: Object.freeze({
        'core-ai-alert-run-cap.label': 'الحد الأقصى لكل تشغيل',
        'core-ai-alert-run-cap.help':
          'امتلاء القرص يطلق عدة قواعد معًا. هذا ما يمنع العاصفة من أن تصبح فاتورة.',
      }),
      zh: Object.freeze({
        'core-ai-alert-run-cap.label': '每次运行最多',
        'core-ai-alert-run-cap.help':
          '磁盘写满会同时触发多个规则。此项可防止告警风暴变成账单。',
      }),
    }),
  }),
  render(data, helpers) {
    const input = document.createElement('input');
    input.type = 'number';
    input.id = 'aiAlertPerRun';
    input.min = '1';
    input.max = '20';
    input.className = 'days';
    input.value = String(data.alerts?.per_run ?? 5);
    return helpers.fieldRow(
      helpers.i18n.t('core-ai-alert-run-cap.label', {}, 'At most, per run'),
      input,
      helpers.i18n.t(
        'core-ai-alert-run-cap.help',
        {},
        'A disk filling up trips several rules at once. This is what stops a storm becoming a bill.',
      ),
    );
  },
};
