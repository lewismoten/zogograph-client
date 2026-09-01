/* Core card package: page shell for removing broken symlinks below one path. */
window.ZogographSymlinkSweepShellCard = {
  manifest: Object.freeze({
    id: 'core-symlink-sweep-shell',
    locales: ['core-symlink-sweep-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-sweep-shell.title': 'Clear out a whole path',
        'core-symlink-sweep-shell.help':
          'When a directory of dangling links is the problem, ticking four hundred boxes is not a workflow. Name a directory and everything {{broken}} under it goes, however deep — you do not need to know how many levels there are, and you should not have to add a {{star}} until the count comes out right. Working links under the same path are left alone and counted: the destructive half is bounded by a fact about each link, not by how carefully the path was typed. You are always shown exactly what would go before anything does.',
        'core-symlink-sweep-shell.placeholder':
          '/home/you/public_html/previews',
        'core-symlink-sweep-shell.show_matches': 'Show me what matches',
      }),
      es: Object.freeze({
        'core-symlink-sweep-shell.title': 'Vaciar una ruta completa',
        'core-symlink-sweep-shell.help':
          'Cuando el problema es un directorio de enlaces colgantes, marcar cuatrocientas casillas no es un flujo de trabajo. Indique un directorio y se elimina todo lo {{broken}} bajo él, por profundo que esté: no necesita saber cuántos niveles hay ni añadir {{star}} hasta que el conteo salga correcto. Los enlaces válidos bajo la misma ruta se dejan intactos y se cuentan: la mitad destructiva está limitada por un hecho de cada enlace, no por lo cuidadosamente que se escribió la ruta. Siempre se muestra exactamente qué se eliminaría antes de hacer nada.',
        'core-symlink-sweep-shell.placeholder':
          '/home/usted/public_html/previews',
        'core-symlink-sweep-shell.show_matches': 'Mostrar coincidencias',
      }),
      hi: Object.freeze({
        'core-symlink-sweep-shell.title': 'पूरा पथ साफ़ करें',
        'core-symlink-sweep-shell.help':
          'जब लटके लिंक की डायरेक्टरी समस्या हो, चार सौ बॉक्स टिक करना वर्कफ़्लो नहीं है। डायरेक्टरी का नाम दें और उसके नीचे का हर {{broken}} लिंक चला जाता है, चाहे कितना गहरा हो—आपको स्तरों की संख्या जानने की जरूरत नहीं और गिनती सही होने तक {{star}} जोड़ना नहीं चाहिए। उसी पथ के काम करने वाले लिंक अकेले रहते और गिने जाते हैं: विनाशकारी आधा हर लिंक के तथ्य से बंधा है, पथ कितनी सावधानी से टाइप हुआ इससे नहीं। कुछ होने से पहले हमेशा ठीक वही दिखाया जाता है जो जाएगा।',
        'core-symlink-sweep-shell.placeholder':
          '/home/user/public_html/previews',
        'core-symlink-sweep-shell.show_matches': 'दिखाएँ क्या मेल खाता है',
      }),
      ar: Object.freeze({
        'core-symlink-sweep-shell.title': 'أفرغ مسارًا كاملًا',
        'core-symlink-sweep-shell.help':
          'حين تكون مشكلة دليل من الروابط المعلقة، ليس وضع علامة في أربعمائة مربع سير عمل. سمِّ دليلًا ويذهب كل ما هو {{broken}} تحته مهما كان عميقًا — لا تحتاج إلى معرفة عدد المستويات ولا ينبغي أن تضيف {{star}} حتى يخرج العدد صحيحًا. تُترك الروابط العاملة تحت المسار نفسه وحدها وتُعد: النصف التدميري مقيد بحقيقة عن كل رابط لا بمدى دقة كتابة المسار. يُعرض لك دائمًا بالضبط ما سيذهب قبل أن يحدث شيء.',
        'core-symlink-sweep-shell.placeholder':
          '/home/your-user/public_html/previews',
        'core-symlink-sweep-shell.show_matches': 'أرني ما يطابق',
      }),
      zh: Object.freeze({
        'core-symlink-sweep-shell.title': '清除整条路径',
        'core-symlink-sweep-shell.help':
          '当一整个目录的悬空链接是问题时，勾选四百个框不是工作流程。指定一个目录，其下所有{{broken}}链接都会被清除，无论有多深——您不需要知道有多少层，也不应反复加 {{star}} 直到计数正确。同一路径下正常的链接会保持不动并被计数：破坏性部分受每个链接的事实限制，而非路径输入得有多仔细。执行前始终会准确显示将要清除的内容。',
        'core-symlink-sweep-shell.placeholder':
          '/home/your-user/public_html/previews',
        'core-symlink-sweep-shell.show_matches': '显示匹配项',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-symlink-sweep-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-symlink-sweep-shell.${key}`],
    );
  },
  render() {},
};
