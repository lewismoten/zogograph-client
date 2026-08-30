window.ZogographEmailMailRuntimeCard = {
  manifest: Object.freeze({
    id: 'core-email-mail-runtime',
    locales: ['core-email-mail-runtime.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-email-mail-runtime.unavailable':
          'This PHP has no mail() function, so nothing can be sent from here whatever is set below. That is a host setting, not one of these.',
      }),
      es: Object.freeze({
        'core-email-mail-runtime.unavailable':
          'Este PHP no tiene función mail(), por lo que no se puede enviar nada desde aquí sin importar lo que se configure abajo. Es una configuración del host, no una de estas.',
      }),
      hi: Object.freeze({
        'core-email-mail-runtime.unavailable':
          'इस PHP में mail() फ़ंक्शन नहीं है, इसलिए नीचे कुछ भी सेट हो तब भी यहाँ से कुछ नहीं भेजा जा सकता। यह होस्ट सेटिंग है, इन सेटिंग में से नहीं।',
      }),
      ar: Object.freeze({
        'core-email-mail-runtime.unavailable':
          'لا تملك PHP هذه دالة mail()، لذلك لا يمكن إرسال شيء من هنا مهما ضُبط أدناه. هذا إعداد للمضيف وليس من هذه الإعدادات.',
      }),
      zh: Object.freeze({
        'core-email-mail-runtime.unavailable':
          '此 PHP 没有 mail() 函数，因此无论在下面设置什么，都无法从这里发送内容。这是主机设置，不是这里的设置。',
      }),
    }),
  }),
  render(_data, helpers) {
    return helpers.note(
      helpers.i18n.t(
        'core-email-mail-runtime.unavailable',
        {},
        'This PHP has no mail() function, so nothing can be sent from here whatever is set below. That is a host setting, not one of these.',
      ),
    );
  },
};
