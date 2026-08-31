/* Core card package: subject field copy for saved assistant memories. */
window.ZogographAiMemorySubjectFieldCard = {
  manifest: Object.freeze({
    id: 'core-ai-memory-subject-field',
    locales: ['core-ai-memory-subject-field.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-memory-subject-field.placeholder': 'the nightly I/O spike',
        'core-ai-memory-subject-field.edit_help':
          'The subject is the key, so it cannot be changed — forget this one and write a new one if it is wrong.',
        'core-ai-memory-subject-field.help':
          'A short phrase. Writing the same subject again replaces what is stored under it.',
      }),
      es: Object.freeze({
        'core-ai-memory-subject-field.placeholder': 'el pico nocturno de E/S',
        'core-ai-memory-subject-field.edit_help':
          'El asunto es la clave, por lo que no se puede cambiar: olvide esta y escriba una nueva si es incorrecta.',
        'core-ai-memory-subject-field.help':
          'Una frase corta. Escribir de nuevo el mismo asunto reemplaza lo almacenado bajo él.',
      }),
      hi: Object.freeze({
        'core-ai-memory-subject-field.placeholder': 'रात का I/O स्पाइक',
        'core-ai-memory-subject-field.edit_help':
          'विषय कुंजी है, इसलिए इसे बदला नहीं जा सकता — गलत होने पर इसे भूलें और नया लिखें।',
        'core-ai-memory-subject-field.help':
          'एक छोटा वाक्यांश। वही विषय फिर लिखने से उसके अंतर्गत संग्रहीत सामग्री बदल जाती है।',
      }),
      ar: Object.freeze({
        'core-ai-memory-subject-field.placeholder':
          'ارتفاع الإدخال/الإخراج الليلي',
        'core-ai-memory-subject-field.edit_help':
          'الموضوع هو المفتاح، لذلك لا يمكن تغييره — انسَ هذه واكتب واحدة جديدة إذا كانت خاطئة.',
        'core-ai-memory-subject-field.help':
          'عبارة قصيرة. تؤدي كتابة الموضوع نفسه مرة أخرى إلى استبدال ما حُفظ تحته.',
      }),
      zh: Object.freeze({
        'core-ai-memory-subject-field.placeholder': '夜间 I/O 峰值',
        'core-ai-memory-subject-field.edit_help':
          '主题是键，因此无法更改——如果有误，请忘记此条并写一条新的。',
        'core-ai-memory-subject-field.help':
          '一个简短短语。再次写入相同主题会替换该主题下已存储的内容。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-memory-subject-field.${input.key}`,
      {},
      input.fallback,
    );
  },
};
