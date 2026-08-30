window.ZogographEmailDeliveryHistoryCard = {
  manifest: Object.freeze({
    id: 'core-email-delivery-history',
    locales: ['core-email-delivery-history.'],
    helpers: ['section', 'simpleTable', 'humanTime', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-email-delivery-history.title': 'Recently sent',
        'core-email-delivery-history.when': 'When',
        'core-email-delivery-history.to': 'To',
        'core-email-delivery-history.result': 'Result',
        'core-email-delivery-history.about': 'About',
      }),
      es: Object.freeze({
        'core-email-delivery-history.title': 'Enviados recientemente',
        'core-email-delivery-history.when': 'Cuándo',
        'core-email-delivery-history.to': 'Para',
        'core-email-delivery-history.result': 'Resultado',
        'core-email-delivery-history.about': 'Acerca de',
      }),
      hi: Object.freeze({
        'core-email-delivery-history.title': 'हाल में भेजे गए',
        'core-email-delivery-history.when': 'कब',
        'core-email-delivery-history.to': 'को',
        'core-email-delivery-history.result': 'परिणाम',
        'core-email-delivery-history.about': 'बारे में',
      }),
      ar: Object.freeze({
        'core-email-delivery-history.title': 'أُرسل مؤخرًا',
        'core-email-delivery-history.when': 'متى',
        'core-email-delivery-history.to': 'إلى',
        'core-email-delivery-history.result': 'النتيجة',
        'core-email-delivery-history.about': 'حول',
      }),
      zh: Object.freeze({
        'core-email-delivery-history.title': '最近发送',
        'core-email-delivery-history.when': '时间',
        'core-email-delivery-history.to': '收件人',
        'core-email-delivery-history.result': '结果',
        'core-email-delivery-history.about': '关于',
      }),
    }),
  }),
  render(data, helpers) {
    if (!data.recent?.length) return document.createDocumentFragment();
    return helpers.section(
      helpers.i18n.t('core-email-delivery-history.title', {}, 'Recently sent'),
      helpers.simpleTable(
        [
          helpers.i18n.t('core-email-delivery-history.when', {}, 'When'),
          helpers.i18n.t('core-email-delivery-history.to', {}, 'To'),
          helpers.i18n.t('core-email-delivery-history.result', {}, 'Result'),
          helpers.i18n.t('core-email-delivery-history.about', {}, 'About'),
        ],
        data.recent.map((delivery) => [
          helpers.humanTime(delivery.at),
          delivery.target || '—',
          delivery.outcome,
          delivery.detail || '—',
        ]),
        [null, 'name', null, null],
      ),
    );
  },
};
