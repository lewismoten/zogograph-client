window.ZogographNotesEmptyCard = {
  manifest: Object.freeze({
    id: 'core-notes-empty',
    locales: ['core-notes-empty.'],
    helpers: ['target', 'note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-notes-empty.title': 'What things are',
        'core-notes-empty.message':
          'Nothing written down yet. The button in the bottom corner writes one about whatever page you are on.',
      }),
      es: Object.freeze({
        'core-notes-empty.title': 'Qué son las cosas',
        'core-notes-empty.message':
          'Aún no hay nada escrito. El botón de la esquina inferior escribe una nota sobre la página en la que está.',
      }),
      hi: Object.freeze({
        'core-notes-empty.title': 'चीज़ें क्या हैं',
        'core-notes-empty.message':
          'अभी कुछ लिखा नहीं गया है। निचले कोने का बटन जिस पृष्ठ पर आप हैं उसके बारे में एक नोट लिखता है।',
      }),
      ar: Object.freeze({
        'core-notes-empty.title': 'ما الأشياء',
        'core-notes-empty.message':
          'لا شيء مكتوب بعد. يكتب الزر في الزاوية السفلية ملاحظة عن الصفحة التي أنت فيها.',
      }),
      zh: Object.freeze({
        'core-notes-empty.title': '这些东西是什么',
        'core-notes-empty.message':
          '还没有写下任何内容。右下角的按钮会写一条关于当前页面的注释。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-notes-empty.${key}`,
      {},
      this.manifest.catalogs.en[`core-notes-empty.${key}`],
    );
  },
  render(data, helpers) {
    if (data.rows.length) return;
    helpers.target.replaceChildren(
      helpers.note(
        helpers.i18n.t(
          'core-notes-empty.message',
          {},
          'Nothing written down yet.',
        ),
      ),
    );
  },
};
