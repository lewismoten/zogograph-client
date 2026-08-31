/* Core card package: symlink-sweep safety note. */
window.ZogographSymlinkSweepSafetyCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sweep-safety',
    locales: ['core-symlink-sweep-safety.'],
    helpers: ['i18n', 'note'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sweep-safety.message':
          'Working links are not considered at all, whatever the pattern says. Only links this check found dangling can be removed here.',
      }),
      es: Object.freeze({
        'core-symlink-sweep-safety.message':
          'Los enlaces funcionales no se consideran en absoluto, diga lo que diga el patrón. Aquí solo se pueden eliminar enlaces que esta comprobación encontró colgando.',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-safety.message':
          'पैटर्न कुछ भी कहे, काम कर रहे लिंक बिल्कुल नहीं देखे जाते। यहाँ केवल वे लिंक हटाए जा सकते हैं जिन्हें इस जांच ने डैंगलिंग पाया।',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-safety.message':
          'لا تُؤخذ الروابط العاملة في الاعتبار إطلاقًا، مهما قال النمط. لا يمكن حذف هنا إلا الروابط التي وجدها هذا الفحص معلقة.',
      }),
      zh: Object.freeze({
        'core-symlink-sweep-safety.message':
          '无论模式怎么说，正常链接都完全不予考虑。这里只能移除该检查发现为悬挂的链接。',
      }),
    }),
  }),
  render(_input, helpers) {
    const { i18n, note } = helpers;
    return note(
      i18n.t(
        'core-symlink-sweep-safety.message',
        {},
        'Working links are not considered at all, whatever the pattern says. Only links this check found dangling can be removed here.',
      ),
    );
  },
};
