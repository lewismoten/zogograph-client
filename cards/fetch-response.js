window.ZogographFetchResponseCard = {
  manifest: Object.freeze({
    id: 'core-fetch-response',
    locales: ['core-fetch-response.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-fetch-response.sign_in_request': 'Sign in to make a request.',
        'core-fetch-response.type_address': 'Type an address above.',
        'core-fetch-response.asking': 'Asking…',
        'core-fetch-response.no_response':
          'Nothing came back after {{ms, number}} ms: {{error}}',
        'core-fetch-response.status': 'Status',
        'core-fetch-response.took': 'Took',
        'core-fetch-response.took_ms': '{{ms, number}} ms',
        'core-fetch-response.method': 'Method',
        'core-fetch-response.body': 'Body',
        'core-fetch-response.redirects': 'Redirects',
        'core-fetch-response.redirected_to':
          '{{count, number}} redirects — ended at {{landed}}',
        'core-fetch-response.response': 'The response',
      }),
      es: Object.freeze({
        'core-fetch-response.sign_in_request':
          'Inicie sesión para hacer una solicitud.',
        'core-fetch-response.type_address': 'Escriba una dirección arriba.',
        'core-fetch-response.asking': 'Consultando…',
        'core-fetch-response.no_response':
          'No llegó nada después de {{ms, number}} ms: {{error}}',
        'core-fetch-response.status': 'Estado',
        'core-fetch-response.took': 'Tardó',
        'core-fetch-response.took_ms': '{{ms, number}} ms',
        'core-fetch-response.method': 'Método',
        'core-fetch-response.body': 'Cuerpo',
        'core-fetch-response.redirects': 'Redirecciones',
        'core-fetch-response.redirected_to':
          '{{count, number}} redirecciones — terminó en {{landed}}',
        'core-fetch-response.response': 'La respuesta',
      }),
      hi: Object.freeze({
        'core-fetch-response.sign_in_request':
          'अनुरोध करने के लिए साइन इन करें।',
        'core-fetch-response.type_address': 'ऊपर एक पता लिखें।',
        'core-fetch-response.asking': 'पूछ रहे हैं…',
        'core-fetch-response.no_response':
          '{{ms, number}} ms के बाद कुछ नहीं आया: {{error}}',
        'core-fetch-response.status': 'स्थिति',
        'core-fetch-response.took': 'लगा समय',
        'core-fetch-response.took_ms': '{{ms, number}} ms',
        'core-fetch-response.method': 'तरीका',
        'core-fetch-response.body': 'मुख्य भाग',
        'core-fetch-response.redirects': 'पुनर्निर्देशन',
        'core-fetch-response.redirected_to':
          '{{count, number}} पुनर्निर्देशन — {{landed}} पर समाप्त',
        'core-fetch-response.response': 'जवाब',
      }),
      ar: Object.freeze({
        'core-fetch-response.sign_in_request': 'سجّل الدخول لتقديم طلب.',
        'core-fetch-response.type_address': 'اكتب عنوانًا أعلاه.',
        'core-fetch-response.asking': 'جارٍ الطلب…',
        'core-fetch-response.no_response':
          'لم يصل شيء بعد {{ms, number}} مللي ثانية: {{error}}',
        'core-fetch-response.status': 'الحالة',
        'core-fetch-response.took': 'استغرق',
        'core-fetch-response.took_ms': '{{ms, number}} مللي ثانية',
        'core-fetch-response.method': 'الطريقة',
        'core-fetch-response.body': 'المتن',
        'core-fetch-response.redirects': 'إعادات التوجيه',
        'core-fetch-response.redirected_to':
          '{{count, number}} من إعادات التوجيه — انتهى عند {{landed}}',
        'core-fetch-response.response': 'الاستجابة',
      }),
      zh: Object.freeze({
        'core-fetch-response.sign_in_request': '请登录以发出请求。',
        'core-fetch-response.type_address': '请在上方输入地址。',
        'core-fetch-response.asking': '正在请求…',
        'core-fetch-response.no_response':
          '{{ms, number}} 毫秒后没有收到响应：{{error}}',
        'core-fetch-response.status': '状态',
        'core-fetch-response.took': '耗时',
        'core-fetch-response.took_ms': '{{ms, number}} 毫秒',
        'core-fetch-response.method': '方法',
        'core-fetch-response.body': '正文',
        'core-fetch-response.redirects': '重定向',
        'core-fetch-response.redirected_to':
          '{{count, number}} 次重定向 — 最终到达 {{landed}}',
        'core-fetch-response.response': '响应',
      }),
    }),
  }),
  text(key, values, helpers) {
    const id = `core-fetch-response.${key}`;
    return helpers.i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
  render(data, helpers) {
    return this.text(data.key, data.values, helpers);
  },
};
