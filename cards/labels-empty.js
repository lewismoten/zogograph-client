window.ZogographLabelsEmptyCard = {
  manifest: Object.freeze({
    id: 'core-labels-empty',
    locales: ['core-labels-empty.'],
    helpers: ['target', 'note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-labels-empty.title': 'Your own names',
        'core-labels-empty.message':
          "Nothing named yet. Start with this machine's own address and anything you recognise in a capture — those are the two that save the most reading.",
      }),
      es: Object.freeze({
        'core-labels-empty.title': 'Sus propios nombres',
        'core-labels-empty.message':
          'Aún no hay nada nombrado. Empiece por la propia dirección de esta máquina y cualquier cosa que reconozca en una captura: son las dos que más lectura ahorran.',
      }),
      hi: Object.freeze({
        'core-labels-empty.title': 'आपके अपने नाम',
        'core-labels-empty.message':
          'अभी कुछ भी नामित नहीं है। इस मशीन के अपने पते और कैप्चर में पहचानी गई किसी भी चीज़ से शुरुआत करें — ये दो सबसे अधिक पढ़ना बचाते हैं।',
      }),
      ar: Object.freeze({
        'core-labels-empty.title': 'أسماءك الخاصة',
        'core-labels-empty.message':
          'لا يوجد شيء مُسمّى بعد. ابدأ بعنوان هذه الآلة نفسه وبأي شيء تتعرّف عليه في لقطة — فهذان هما الأكثر توفيرًا للقراءة.',
      }),
      zh: Object.freeze({
        'core-labels-empty.title': '您自己的名称',
        'core-labels-empty.message':
          '尚未命名任何内容。请从此机器自己的地址和您在捕获记录中识别的内容开始——这两者最能减少阅读量。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-labels-empty.${key}`,
      {},
      this.manifest.catalogs.en[`core-labels-empty.${key}`],
    );
  },
  render(data, helpers) {
    if (data.rows.length) return;
    helpers.target.replaceChildren(
      helpers.note(
        helpers.i18n.t('core-labels-empty.message', {}, 'Nothing named yet.'),
      ),
    );
  },
};
