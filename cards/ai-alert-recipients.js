/* Core card package: recipients for unattended assistant summaries. */
window.ZogographAiAlertRecipientsCard = {
  manifest: Object.freeze({
    id: 'core-ai-alert-recipients',
    locales: ['core-ai-alert-recipients.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-alert-recipients.label': 'Send summaries to',
        'core-ai-alert-recipients.placeholder':
          'leave blank to use the alert addresses',
        'core-ai-alert-recipients.help':
          'Semicolon-separated. A summary and an alert are different kinds of message and may reasonably go to different places — but requiring a second address before the feature works at all would be a needless step, so blank means the alert addresses.',
      }),
      es: Object.freeze({
        'core-ai-alert-recipients.label': 'Enviar resúmenes a',
        'core-ai-alert-recipients.placeholder':
          'deje vacío para usar las direcciones de alerta',
        'core-ai-alert-recipients.help':
          'Separe las direcciones con punto y coma. Los resúmenes y las alertas pueden ir a lugares distintos; dejarlo vacío usa las direcciones de alerta.',
      }),
      hi: Object.freeze({
        'core-ai-alert-recipients.label': 'सारांश इनको भेजें',
        'core-ai-alert-recipients.placeholder':
          'चेतावनी पते उपयोग करने के लिए खाली छोड़ें',
        'core-ai-alert-recipients.help':
          'पतों को सेमीकोलन से अलग करें। सारांश और चेतावनी अलग जगह जा सकते हैं; खाली रखने पर चेतावनी पते उपयोग होंगे।',
      }),
      ar: Object.freeze({
        'core-ai-alert-recipients.label': 'أرسل الملخصات إلى',
        'core-ai-alert-recipients.placeholder':
          'اتركه فارغًا لاستخدام عناوين التنبيه',
        'core-ai-alert-recipients.help':
          'افصل العناوين بفاصلة منقوطة. قد تذهب الملخصات والتنبيهات إلى أماكن مختلفة؛ الفراغ يستخدم عناوين التنبيه.',
      }),
      zh: Object.freeze({
        'core-ai-alert-recipients.label': '将摘要发送到',
        'core-ai-alert-recipients.placeholder': '留空以使用告警地址',
        'core-ai-alert-recipients.help':
          '用分号分隔地址。摘要和告警可以发送到不同位置；留空则使用告警地址。',
      }),
    }),
  }),
  render(data, helpers) {
    const input = document.createElement('input');
    input.type = 'text';
    input.id = 'aiReportTo';
    input.className = 'wide-field';
    input.value = data.alerts?.report_to ?? '';
    input.placeholder = helpers.i18n.t(
      'core-ai-alert-recipients.placeholder',
      {},
      'leave blank to use the alert addresses',
    );
    return helpers.fieldRow(
      helpers.i18n.t('core-ai-alert-recipients.label', {}, 'Send summaries to'),
      input,
      helpers.i18n.t(
        'core-ai-alert-recipients.help',
        {},
        'Semicolon-separated. A summary and an alert are different kinds of message and may reasonably go to different places — but requiring a second address before the feature works at all would be a needless step, so blank means the alert addresses.',
      ),
    );
  },
};
