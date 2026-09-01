/* Core card package: regex tester pattern and sample input fields. */
window.ZogographRegexPatternInputCard = {
  manifest: Object.freeze({
    id: 'core-regex-pattern-input',
    locales: ['core-regex-pattern-input.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-regex-pattern-input.pattern': 'Pattern',
        'core-regex-pattern-input.pattern_help':
          'Written without slashes around it — they are added for you, so a / in the pattern needs no escaping.',
        'core-regex-pattern-input.sample_placeholder':
          'Paste some of the page here — view source and take the part you are matching against.',
        'core-regex-pattern-input.against': 'Against',
      }),
      es: Object.freeze({
        'core-regex-pattern-input.pattern': 'Patrón',
        'core-regex-pattern-input.pattern_help':
          'Escríbalo sin barras: se añaden automáticamente, por lo que una / dentro del patrón no necesita escape.',
        'core-regex-pattern-input.sample_placeholder':
          'Pegue aquí parte de la página; vea el código fuente y tome la parte con la que desea comparar.',
        'core-regex-pattern-input.against': 'Comparar con',
      }),
      hi: Object.freeze({
        'core-regex-pattern-input.pattern': 'पैटर्न',
        'core-regex-pattern-input.pattern_help':
          'इसे आसपास की स्लैश के बिना लिखें — वे आपके लिए जोड़ दी जाती हैं, इसलिए पैटर्न में / को एस्केप करने की जरूरत नहीं है।',
        'core-regex-pattern-input.sample_placeholder':
          'पेज का कुछ भाग यहाँ चिपकाएँ — स्रोत देखें और वह भाग लें जिसके विरुद्ध मिलान करना है।',
        'core-regex-pattern-input.against': 'इसके विरुद्ध',
      }),
      ar: Object.freeze({
        'core-regex-pattern-input.pattern': 'النمط',
        'core-regex-pattern-input.pattern_help':
          'اكتبه دون شرطتين مائلتين حوله — تُضافان لك، لذا لا تحتاج إلى تهريب / داخل النمط.',
        'core-regex-pattern-input.sample_placeholder':
          'الصق جزءًا من الصفحة هنا — اعرض المصدر وخذ الجزء الذي تطابقه.',
        'core-regex-pattern-input.against': 'مقابل',
      }),
      zh: Object.freeze({
        'core-regex-pattern-input.pattern': '模式',
        'core-regex-pattern-input.pattern_help':
          '请勿在两侧加斜杠；系统会自动添加，因此模式中的 / 无需转义。',
        'core-regex-pattern-input.sample_placeholder':
          '请在此粘贴页面的一部分——查看源代码并取出要匹配的部分。',
        'core-regex-pattern-input.against': '匹配对象',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-regex-pattern-input.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
