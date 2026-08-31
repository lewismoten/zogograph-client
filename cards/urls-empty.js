window.ZogographUrlsEmptyCard = {
  manifest: Object.freeze({
    id: 'core-urls-empty',
    locales: ['core-urls-empty.'],
    helpers: ['target', 'hideHistory', 'note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-urls-empty.title': 'Is the site answering?',
        'core-urls-empty.add_check': 'Add a check',
        'core-urls-empty.message':
          'Nothing is being watched. Add the address somebody would actually type — the home page, or a health endpoint that touches the database.',
      }),
      es: Object.freeze({
        'core-urls-empty.title': '¿Responde el sitio?',
        'core-urls-empty.add_check': 'Añadir una comprobación',
        'core-urls-empty.message':
          'No se vigila nada. Añada la dirección que alguien escribiría realmente: la página de inicio o un punto de comprobación de estado que consulte la base de datos.',
      }),
      hi: Object.freeze({
        'core-urls-empty.title': 'क्या साइट जवाब दे रही है?',
        'core-urls-empty.add_check': 'जाँच जोड़ें',
        'core-urls-empty.message':
          'कुछ भी नहीं देखा जा रहा है। वह पता जोड़ें जिसे कोई वास्तव में लिखेगा — मुखपृष्ठ, या डेटाबेस को छूने वाला स्वास्थ्य एंडपॉइंट।',
      }),
      ar: Object.freeze({
        'core-urls-empty.title': 'هل يجيب الموقع؟',
        'core-urls-empty.add_check': 'أضف فحصًا',
        'core-urls-empty.message':
          'لا يجري مراقبة شيء. أضف العنوان الذي سيكتبه شخص فعليًا — الصفحة الرئيسية أو نقطة فحص حالة تصل إلى قاعدة البيانات.',
      }),
      zh: Object.freeze({
        'core-urls-empty.title': '网站有响应吗？',
        'core-urls-empty.add_check': '添加检查',
        'core-urls-empty.message':
          '当前没有监视任何内容。请添加用户实际会输入的地址——主页，或会访问数据库的健康检查端点。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-urls-empty.${key}`,
      {},
      this.manifest.catalogs.en[`core-urls-empty.${key}`],
    );
  },
  render(data, helpers) {
    if (data.rows.length) return;
    helpers.target.replaceChildren(
      helpers.note(
        helpers.i18n.t(
          'core-urls-empty.message',
          {},
          'Nothing is being watched.',
        ),
      ),
    );
    helpers.hideHistory();
  },
};
