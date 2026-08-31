window.ZogographRegexFetchCard = {
  manifest: Object.freeze({
    id: 'core-regex-fetch',
    locales: ['core-regex-fetch.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-regex-fetch.use_page': 'Fetch the page and use that',
        'core-regex-fetch.use_page_help':
          'Ask for the URL in the form above and paste its body in here',
        'core-regex-fetch.enter_address': 'Put an address in the form first.',
        'core-regex-fetch.fetched': 'Fetched {{bytes}} from {{url}}.',
      }),
      es: Object.freeze({
        'core-regex-fetch.use_page': 'Obtener la página y usarla',
        'core-regex-fetch.use_page_help':
          'Indique la URL en el formulario de arriba y pegue aquí su contenido',
        'core-regex-fetch.enter_address':
          'Primero indique una dirección en el formulario.',
        'core-regex-fetch.fetched': 'Se obtuvieron {{bytes}} de {{url}}.',
      }),
      hi: Object.freeze({
        'core-regex-fetch.use_page': 'पेज लाएँ और उसका उपयोग करें',
        'core-regex-fetch.use_page_help':
          'ऊपर दिए गए फ़ॉर्म में URL लिखें और उसका मुख्य भाग यहाँ चिपकाएँ',
        'core-regex-fetch.enter_address': 'पहले फ़ॉर्म में एक पता डालें।',
        'core-regex-fetch.fetched': '{{url}} से {{bytes}} लाए गए।',
      }),
      ar: Object.freeze({
        'core-regex-fetch.use_page': 'اجلب الصفحة واستخدمها',
        'core-regex-fetch.use_page_help':
          'أدخل عنوان URL في النموذج أعلاه والصق متن الصفحة هنا',
        'core-regex-fetch.enter_address': 'أدخل عنوانًا في النموذج أولًا.',
        'core-regex-fetch.fetched': 'جُلب {{bytes}} من {{url}}.',
      }),
      zh: Object.freeze({
        'core-regex-fetch.use_page': '获取并使用该页面',
        'core-regex-fetch.use_page_help':
          '在上方表单中填写 URL，然后将其正文粘贴到这里',
        'core-regex-fetch.enter_address': '请先在表单中填写地址。',
        'core-regex-fetch.fetched': '已从 {{url}} 获取 {{bytes}}。',
      }),
    }),
  }),
  render(data, helpers) {
    const id = `core-regex-fetch.${data.key}`;
    return helpers.i18n.t(id, data.values, this.manifest.catalogs.en[id]);
  },
};
