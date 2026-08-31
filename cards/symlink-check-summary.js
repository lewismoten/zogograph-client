/* Core card package: symlink check summary and freshness details. */
window.ZogographSymlinkCheckSummaryCard = {
  manifest: Object.freeze({
    id: 'core-symlink-check-summary',
    locales: ['core-symlink-check-summary.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-check-summary.complete':
          '{{links}} links across {{dirs}} directories in {{seconds}}s. The whole tree was checked.',
        'core-symlink-check-summary.incomplete':
          '{{links}} links across {{dirs}} directories in {{seconds}}s. Stopped before the end of the tree — there may be more.',
        'core-symlink-check-summary.checked_ago': ' Checked {{time}}.',
        'core-symlink-check-summary.stored':
          ' This is the stored result of that check, not a new one.',
        'core-symlink-check-summary.stale':
          'This is the last check, not a fresh one — the walk takes seconds and is not run on a timer. Press Check again for the current state.',
      }),
      es: Object.freeze({
        'core-symlink-check-summary.complete':
          '{{links}} enlaces en {{dirs}} directorios en {{seconds}} s. Se comprobó todo el árbol.',
        'core-symlink-check-summary.incomplete':
          '{{links}} enlaces en {{dirs}} directorios en {{seconds}} s. Se detuvo antes del final del árbol; puede haber más.',
        'core-symlink-check-summary.checked_ago': ' Comprobado {{time}}.',
        'core-symlink-check-summary.stored':
          ' Este es el resultado almacenado de esa comprobación, no uno nuevo.',
        'core-symlink-check-summary.stale':
          'Esta es la última comprobación, no una reciente: el recorrido tarda segundos y no se ejecuta con un temporizador. Pulse Comprobar de nuevo para ver el estado actual.',
      }),
      hi: Object.freeze({
        'core-symlink-check-summary.complete':
          '{{dirs}} डायरेक्टरी में {{links}} लिंक, {{seconds}} सेकंड में। पूरा ट्री जांचा गया।',
        'core-symlink-check-summary.incomplete':
          '{{dirs}} डायरेक्टरी में {{links}} लिंक, {{seconds}} सेकंड में। ट्री के अंत से पहले रुक गया — और हो सकते हैं।',
        'core-symlink-check-summary.checked_ago': ' {{time}} जांचा गया।',
        'core-symlink-check-summary.stored':
          ' यह उस जांच का संग्रहीत परिणाम है, नया नहीं।',
        'core-symlink-check-summary.stale':
          'यह पिछली जांच है, ताज़ा नहीं — वॉक में सेकंड लगते हैं और यह टाइमर पर नहीं चलता। वर्तमान स्थिति के लिए फिर जांचें दबाएँ।',
      }),
      ar: Object.freeze({
        'core-symlink-check-summary.complete':
          '{{links}} روابط عبر {{dirs}} أدلة خلال {{seconds}} ث. فُحصت الشجرة كاملة.',
        'core-symlink-check-summary.incomplete':
          '{{links}} روابط عبر {{dirs}} أدلة خلال {{seconds}} ث. توقف قبل نهاية الشجرة — قد يكون هناك المزيد.',
        'core-symlink-check-summary.checked_ago': ' فُحص {{time}}.',
        'core-symlink-check-summary.stored':
          ' هذه نتيجة الفحص المخزنة، وليست نتيجة جديدة.',
        'core-symlink-check-summary.stale':
          'هذا آخر فحص، وليس فحصًا جديدًا — يستغرق الاجتياز ثوانٍ ولا يعمل بمؤقت. اضغط تحقق مرة أخرى للحالة الحالية.',
      }),
      zh: Object.freeze({
        'core-symlink-check-summary.complete':
          '{{dirs}} 个目录中有 {{links}} 个链接，用时 {{seconds}} 秒。已检查整个目录树。',
        'core-symlink-check-summary.incomplete':
          '{{dirs}} 个目录中有 {{links}} 个链接，用时 {{seconds}} 秒。在目录树末尾前停止——可能还有更多。',
        'core-symlink-check-summary.checked_ago': ' 已在 {{time}} 检查。',
        'core-symlink-check-summary.stored':
          ' 这是该检查存储的结果，不是新结果。',
        'core-symlink-check-summary.stale':
          '这是上一次检查，不是新检查——遍历需要几秒钟，且不会按计时器运行。请按“再次检查”查看当前状态。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-symlink-check-summary.${key}`,
      values,
      this.manifest.catalogs.en[`core-symlink-check-summary.${key}`],
    );
  },
};
