/* Core card package: incomplete symlink-sweep warning. */
window.ZogographSymlinkSweepIncompleteCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sweep-incomplete',
    locales: ['core-symlink-sweep-incomplete.'],
    helpers: ['i18n', 'note'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sweep-incomplete.message':
          'The walk ran out of time before reaching the end of the tree, so there may be more matching links it never saw. Run it again afterwards.',
      }),
      es: Object.freeze({
        'core-symlink-sweep-incomplete.message':
          'El recorrido se quedó sin tiempo antes de llegar al final del árbol, por lo que puede haber más enlaces coincidentes que nunca vio. Ejecútelo de nuevo después.',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-incomplete.message':
          'ट्री के अंत तक पहुँचने से पहले वॉक का समय समाप्त हो गया, इसलिए और मेल खाते लिंक हो सकते हैं जिन्हें उसने नहीं देखा। बाद में इसे फिर चलाएँ।',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-incomplete.message':
          'نفد وقت المسح قبل الوصول إلى نهاية الشجرة، لذلك قد تكون هناك روابط مطابقة أخرى لم يرها. شغّله مرة أخرى لاحقًا.',
      }),
      zh: Object.freeze({
        'core-symlink-sweep-incomplete.message':
          '遍历在到达树的末尾前已超时，因此可能还有它从未看到的匹配链接。请随后再次运行。',
      }),
    }),
  }),
  render(_input, helpers) {
    const { i18n, note } = helpers;
    return note(
      i18n.t(
        'core-symlink-sweep-incomplete.message',
        {},
        'The walk ran out of time before reaching the end of the tree, so there may be more matching links it never saw. Run it again afterwards.',
      ),
    );
  },
};
