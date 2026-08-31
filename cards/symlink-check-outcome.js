/* Core card package: symlink check outcomes and empty states. */
window.ZogographSymlinkCheckOutcomeCard = {
  manifest: Object.freeze({
    id: 'core-symlink-check-outcome',
    locales: ['core-symlink-check-outcome.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-check-outcome.walking_tree': 'Walking the tree…',
        'core-symlink-check-outcome.not_checked':
          'Nothing has been checked yet. This walks the whole watched directory, which takes a few seconds, so it runs when you ask rather than on every poll.',
        'core-symlink-check-outcome.first_walk':
          'This is the first complete check, so nothing counts as new. From now on anything that appears will be called out here and will raise an alert.',
        'core-symlink-check-outcome.new_problems':
          '{{count}} problems here for the first time. An alert has been raised.',
        'core-symlink-check-outcome.gone':
          'Nothing new, and {{count}} problems gone since last time.',
        'core-symlink-check-outcome.unchanged':
          'Nothing new since the last check.',
        'core-symlink-check-outcome.healthy':
          'No symlink loops, dangling links or escapes found.',
        'core-symlink-check-outcome.healthy_partial':
          'Nothing wrong in the part that was checked.',
      }),
      es: Object.freeze({
        'core-symlink-check-outcome.walking_tree': 'Recorriendo el árbol…',
        'core-symlink-check-outcome.not_checked':
          'Aún no se ha comprobado nada. Esto recorre todo el directorio vigilado, tarda unos segundos, por lo que se ejecuta cuando lo solicita y no en cada sondeo.',
        'core-symlink-check-outcome.first_walk':
          'Esta es la primera comprobación completa, así que nada cuenta como nuevo. A partir de ahora, todo lo que aparezca se indicará aquí y generará una alerta.',
        'core-symlink-check-outcome.new_problems':
          '{{count}} problemas aparecen aquí por primera vez. Se ha generado una alerta.',
        'core-symlink-check-outcome.gone':
          'Nada nuevo y {{count}} problemas desaparecieron desde la última vez.',
        'core-symlink-check-outcome.unchanged':
          'Nada nuevo desde la última comprobación.',
        'core-symlink-check-outcome.healthy':
          'No se encontraron bucles, enlaces colgantes ni escapes de enlaces simbólicos.',
        'core-symlink-check-outcome.healthy_partial':
          'No hay nada incorrecto en la parte que se comprobó.',
      }),
      hi: Object.freeze({
        'core-symlink-check-outcome.walking_tree': 'ट्री में चल रहा है…',
        'core-symlink-check-outcome.not_checked':
          'अभी तक कुछ जांचा नहीं गया है। यह पूरे देखे जा रहे डायरेक्टरी को चलकर जांचता है, जिसमें कुछ सेकंड लगते हैं, इसलिए यह हर पोल पर नहीं बल्कि आपके कहने पर चलता है।',
        'core-symlink-check-outcome.first_walk':
          'यह पहली पूरी जांच है, इसलिए कुछ भी नया नहीं गिना जाता। अब से जो भी दिखाई देगा वह यहाँ बताया जाएगा और अलर्ट उठाएगा।',
        'core-symlink-check-outcome.new_problems':
          '{{count}} समस्याएँ यहाँ पहली बार हैं। एक अलर्ट उठाया गया है।',
        'core-symlink-check-outcome.gone':
          'कुछ नया नहीं, और पिछली बार से {{count}} समस्याएँ चली गईं।',
        'core-symlink-check-outcome.unchanged':
          'पिछली जांच के बाद से कुछ नया नहीं है।',
        'core-symlink-check-outcome.healthy':
          'कोई सिमलिंक लूप, डैंगलिंग लिंक या एस्केप नहीं मिला।',
        'core-symlink-check-outcome.healthy_partial':
          'जिस हिस्से की जांच हुई उसमें कुछ गलत नहीं है।',
      }),
      ar: Object.freeze({
        'core-symlink-check-outcome.walking_tree': 'جارٍ اجتياز الشجرة…',
        'core-symlink-check-outcome.not_checked':
          'لم يُفحص شيء بعد. يفحص هذا الدليل المراقَب كله، ويستغرق بضع ثوانٍ، لذا يعمل عند طلبك لا في كل استطلاع.',
        'core-symlink-check-outcome.first_walk':
          'هذا أول فحص كامل، لذلك لا يُحسب شيء كجديد. من الآن فصاعدًا سيُذكر هنا كل ما يظهر وسيُطلق تنبيهًا.',
        'core-symlink-check-outcome.new_problems':
          'توجد {{count}} مشكلات هنا لأول مرة. أُطلق تنبيه.',
        'core-symlink-check-outcome.gone':
          'لا جديد، واختفت {{count}} مشكلات منذ المرة الماضية.',
        'core-symlink-check-outcome.unchanged': 'لا جديد منذ الفحص الأخير.',
        'core-symlink-check-outcome.healthy':
          'لم يُعثر على حلقات أو روابط معلقة أو هروب روابط رمزية.',
        'core-symlink-check-outcome.healthy_partial':
          'لا شيء خاطئ في الجزء الذي فُحص.',
      }),
      zh: Object.freeze({
        'core-symlink-check-outcome.walking_tree': '正在遍历目录树…',
        'core-symlink-check-outcome.not_checked':
          '尚未执行检查。这会遍历整个受监视目录，需几秒钟，因此只会在您请求时运行，而不是每次轮询都运行。',
        'core-symlink-check-outcome.first_walk':
          '这是第一次完整检查，因此没有任何内容算作新增。从现在起，任何出现的问题都会在此处标出并触发警报。',
        'core-symlink-check-outcome.new_problems':
          '{{count}} 个问题首次出现在这里。已触发警报。',
        'core-symlink-check-outcome.gone':
          '没有新问题，且自上次以来已有 {{count}} 个问题消失。',
        'core-symlink-check-outcome.unchanged': '自上次检查以来没有新内容。',
        'core-symlink-check-outcome.healthy':
          '未发现符号链接循环、悬挂链接或逃逸链接。',
        'core-symlink-check-outcome.healthy_partial':
          '在已检查的部分中未发现问题。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-symlink-check-outcome.${key}`,
      values,
      this.manifest.catalogs.en[`core-symlink-check-outcome.${key}`],
    );
  },
};
