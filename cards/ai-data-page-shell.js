/* Core card package: title and explanation for assistant data storage. */
window.ZogographAiDataPageShellCard = {
  manifest: Object.freeze({
    id: 'core-ai-data-page-shell',
    locales: ['core-ai-data-page-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-data-page-shell.title': '{{assistant}}’s database',
        'core-ai-data-page-shell.help':
          'Kept apart from the account’s own database on purpose: everything here is a record of what was sent to somebody else’s service and what came back, and that is worth being able to read, export or delete without touching the readings. It is also the one database here that grows by conversation rather than by schedule — a long session with a few screenshots in it can outweigh a week of measurements — so what it is holding is worth being able to see.',
      }),
      es: Object.freeze({
        'core-ai-data-page-shell.title': 'Base de datos de {{assistant}}',
        'core-ai-data-page-shell.help':
          'Se mantiene separada de la base de datos de la cuenta a propósito: todo aquí es un registro de lo enviado al servicio de otra persona y de lo que volvió, y poder leerlo, exportarlo o borrarlo sin tocar las lecturas vale la pena. También es la única base de datos que crece por conversación en vez de por calendario: una sesión larga con unas capturas puede pesar más que una semana de mediciones, así que conviene poder ver qué contiene.',
      }),
      hi: Object.freeze({
        'core-ai-data-page-shell.title': '{{assistant}} का डेटाबेस',
        'core-ai-data-page-shell.help':
          'इसे जानबूझकर खाते के डेटाबेस से अलग रखा गया है: यहाँ सब कुछ किसी और की सेवा को भेजी गई चीज़ और वापस आई चीज़ का रिकॉर्ड है, और रीडिंग को छुए बिना उसे पढ़, निर्यात या हटा पाना मूल्यवान है। यह यहाँ एकमात्र डेटाबेस भी है जो शेड्यूल की जगह बातचीत से बढ़ता है—कुछ स्क्रीनशॉट वाली लंबी सत्र एक सप्ताह के माप से भारी हो सकता है—इसलिए यह क्या रख रहा है देख पाना जरूरी है।',
      }),
      ar: Object.freeze({
        'core-ai-data-page-shell.title': 'قاعدة بيانات {{assistant}}',
        'core-ai-data-page-shell.help':
          'تُحفظ منفصلة عن قاعدة بيانات الحساب عمدًا: كل شيء هنا سجل لما أُرسل إلى خدمة شخص آخر وما عاد منها، والقدرة على قراءته أو تصديره أو حذفه من دون لمس القراءات جديرة بذلك. وهي أيضًا قاعدة البيانات الوحيدة هنا التي تنمو بالمحادثة بدل الجدول — فجلسة طويلة بها لقطات شاشة قليلة قد تفوق أسبوعًا من القياسات — لذا فإن رؤية ما تحتفظ به جديرة بذلك.',
      }),
      zh: Object.freeze({
        'core-ai-data-page-shell.title': '{{assistant}} 的数据库',
        'core-ai-data-page-shell.help':
          '它有意与账户自身的数据库分开：这里的一切都是发送到他人服务的内容及其返回结果的记录，能够在不触碰读数的情况下阅读、导出或删除这些内容很有价值。它也是这里唯一按对话而非按计划增长的数据库——一次含有几张截图的长会话可能超过一周的测量数据——因此值得能够查看它保存了什么。',
      }),
    }),
  }),
  render(input, helpers) {
    return helpers.i18n.t(
      `core-ai-data-page-shell.${input.key}`,
      { assistant: input.assistant },
      input.fallback,
    );
  },
};
