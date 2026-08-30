window.ZogographEmailRecipientStatusCard = {
  manifest: Object.freeze({
    id: 'core-email-recipient-status',
    locales: ['core-email-recipient-status.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-email-recipient-status.invalid':
          '{{count, number}} in that list will never receive anything: {{addresses}}.',
        'core-email-recipient-status.count': '{{count, number}} will be told.',
      }),
      es: Object.freeze({
        'core-email-recipient-status.invalid':
          '{{count, number}} de esa lista nunca recibirán nada: {{addresses}}.',
        'core-email-recipient-status.count': 'Se avisará a {{count, number}}.',
      }),
      hi: Object.freeze({
        'core-email-recipient-status.invalid':
          'उस सूची के {{count, number}} को कभी कुछ नहीं मिलेगा: {{addresses}}।',
        'core-email-recipient-status.count':
          '{{count, number}} को बताया जाएगा।',
      }),
      ar: Object.freeze({
        'core-email-recipient-status.invalid':
          'لن يتلقى {{count, number}} من تلك القائمة شيئًا أبدًا: {{addresses}}.',
        'core-email-recipient-status.count': 'سيُبلّغ {{count, number}}.',
      }),
      zh: Object.freeze({
        'core-email-recipient-status.invalid':
          '该列表中的 {{count, number}} 个永远收不到任何内容：{{addresses}}。',
        'core-email-recipient-status.count':
          '将通知 {{count, number}} 个地址。',
      }),
    }),
  }),
  render(data, helpers) {
    if (data.bad_recipients?.length) {
      return helpers.note(
        helpers.i18n.t(
          'core-email-recipient-status.invalid',
          {
            count: data.bad_recipients.length,
            addresses: data.bad_recipients.join(', '),
          },
          '{{count, number}} in that list will never receive anything: {{addresses}}.',
        ),
      );
    }
    if (data.recipients?.length > 1) {
      return helpers.note(
        helpers.i18n.t(
          'core-email-recipient-status.count',
          { count: data.recipients.length },
          '{{count, number}} will be told.',
        ),
      );
    }
    return document.createDocumentFragment();
  },
};
