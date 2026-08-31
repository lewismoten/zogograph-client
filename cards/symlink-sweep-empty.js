/* Core card package: empty symlink-sweep result. */
window.ZogographSymlinkSweepEmptyCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sweep-empty',
    locales: ['core-symlink-sweep-empty.'],
    helpers: ['i18n', 'note'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sweep-empty.message':
          'No link with a problem is under that path. Everything below what you type is included, however deep, so there is no need to add — or count — any stars. Healthy links are never matched, so a path over a directory of working links finds nothing here by design.',
      }),
      es: Object.freeze({
        'core-symlink-sweep-empty.message':
          'No hay ningún enlace con problemas bajo esa ruta. Se incluye todo lo que está debajo de lo que escribe, por profundo que esté, así que no hace falta añadir ni contar asteriscos. Los enlaces sanos nunca coinciden, de modo que una ruta sobre un directorio de enlaces funcionales no encuentra nada aquí por diseño.',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-empty.message':
          'उस पथ के अंतर्गत समस्या वाला कोई लिंक नहीं है। आप जो लिखते हैं उसके नीचे की हर चीज़ शामिल है, चाहे कितनी भी गहराई हो, इसलिए तारों को जोड़ने या गिनने की जरूरत नहीं है। स्वस्थ लिंक कभी मेल नहीं खाते, इसलिए काम कर रहे लिंक की डायरेक्टरी पर पथ डिज़ाइन के अनुसार यहाँ कुछ नहीं पाता।',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-empty.message':
          'لا يوجد رابط به مشكلة تحت ذلك المسار. كل ما تحت ما تكتبه مشمول، مهما كان العمق، فلا حاجة لإضافة النجوم أو عدها. الروابط السليمة لا تطابق أبدًا، لذا لا يجد المسار فوق مجلد روابط عاملة شيئًا هنا عن قصد.',
      }),
      zh: Object.freeze({
        'core-symlink-sweep-empty.message':
          '该路径下没有有问题的链接。您输入内容之下的所有内容都会包含在内，无论多深，因此无需添加或计算星号。健康链接永远不会匹配，因此针对正常链接目录的路径按设计不会在这里找到任何内容。',
      }),
    }),
  }),
  render(_input, helpers) {
    const { i18n, note } = helpers;
    return note(
      i18n.t(
        'core-symlink-sweep-empty.message',
        {},
        'No link with a problem is under that path. Everything below what you type is included, however deep, so there is no need to add — or count — any stars. Healthy links are never matched, so a path over a directory of working links finds nothing here by design.',
      ),
    );
  },
};
