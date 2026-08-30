window.ZogographFetchJsonCard = {
  manifest: Object.freeze({
    id: 'core-fetch-json',
    locales: ['core-fetch-json.'],
    helpers: ['note', 'jsonBlock', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-fetch-json.invalid':
          'The server called this JSON, but it does not parse: {{error}}. The raw view has exactly what arrived.',
        'core-fetch-json.truncated':
          'Showing the first {{shown, number}} of {{total, number}} lines. The raw view has all of it.',
      }),
      es: Object.freeze({
        'core-fetch-json.invalid':
          'El servidor llamó JSON a esto, pero no se puede analizar: {{error}}. La vista sin procesar contiene exactamente lo que llegó.',
        'core-fetch-json.truncated':
          'Se muestran las primeras {{shown, number}} de {{total, number}} líneas. La vista sin procesar las contiene todas.',
      }),
      hi: Object.freeze({
        'core-fetch-json.invalid':
          'सर्वर ने इसे JSON कहा है, लेकिन यह पार्स नहीं होता: {{error}}। रॉ दृश्य में ठीक वही है जो प्राप्त हुआ था।',
        'core-fetch-json.truncated':
          'पहली {{shown, number}} में से {{total, number}} पंक्तियाँ दिखाई जा रही हैं। रॉ दृश्य में वे सभी हैं।',
      }),
      ar: Object.freeze({
        'core-fetch-json.invalid':
          'سمّى الخادم هذا JSON، لكنه لا يُحلَّل: {{error}}. تحتوي طريقة العرض الخام على ما وصل بالضبط.',
        'core-fetch-json.truncated':
          'يُعرض أول {{shown, number}} من أصل {{total, number}} سطرًا. تحتوي طريقة العرض الخام عليها كلها.',
      }),
      zh: Object.freeze({
        'core-fetch-json.invalid':
          '服务器将其称为 JSON，但它无法解析：{{error}}。原始视图中包含完全相同的收到内容。',
        'core-fetch-json.truncated':
          '显示前 {{shown, number}} 行，共 {{total, number}} 行。原始视图中包含全部内容。',
      }),
    }),
  }),
  render(data, helpers) {
    const wrap = document.createDocumentFragment();
    const parsed = data.json || {};

    if (!parsed.ok) {
      wrap.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-fetch-json.invalid',
            { error: parsed.error },
            'The server called this JSON, but it does not parse: {{error}}. The raw view has exactly what arrived.',
          ),
        ),
      );
      return wrap;
    }

    if (parsed.truncated) {
      wrap.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-fetch-json.truncated',
            { shown: parsed.lines.length, total: parsed.total },
            'Showing the first {{shown, number}} of {{total, number}} lines. The raw view has all of it.',
          ),
        ),
      );
    }
    wrap.appendChild(helpers.jsonBlock(parsed.lines));
    return wrap;
  },
};
