/* Core card package: static shell copy for scheduled AI summaries. */
window.ZogographReportsShellCard = {
  manifest: Object.freeze({
    id: 'core-reports-shell',
    locales: ['core-reports-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-reports-shell.title': 'Summaries',
        'core-reports-shell.write_now': 'Write one now',
        'core-reports-shell.help':
          'Written on a schedule by {{assistant}} from a fixed set of figures for the period — the same questions every time, so two summaries can be compared. What varies is what it makes of them. Each one is emailed when it is written; this is where they are kept.',
      }),
      es: Object.freeze({
        'core-reports-shell.title': 'Resúmenes',
        'core-reports-shell.write_now': 'Escribir uno ahora',
        'core-reports-shell.help':
          '{{assistant}} los escribe según un calendario a partir de un conjunto fijo de cifras del periodo: las mismas preguntas cada vez, para poder comparar dos resúmenes. Lo que varía es la interpretación que hace de ellas. Cada uno se envía por correo al escribirse; aquí es donde se conservan.',
      }),
      hi: Object.freeze({
        'core-reports-shell.title': 'सारांश',
        'core-reports-shell.write_now': 'अभी एक लिखें',
        'core-reports-shell.help':
          'अवधि के तय आंकड़ों से {{assistant}} इन्हें शेड्यूल पर लिखता है—हर बार वही सवाल, ताकि दो सारांशों की तुलना हो सके। बदलता है कि वह उनका क्या अर्थ निकालता है। हर एक लिखे जाने पर ईमेल होता है; इन्हें यहाँ रखा जाता है।',
      }),
      ar: Object.freeze({
        'core-reports-shell.title': 'الملخصات',
        'core-reports-shell.write_now': 'اكتب واحدًا الآن',
        'core-reports-shell.help':
          'يكتب {{assistant}} هذه وفق جدول من مجموعة ثابتة من أرقام الفترة — الأسئلة نفسها كل مرة، لذا يمكن مقارنة ملخصين. ما يختلف هو ما يستنتجه منها. يُرسل كل واحد بالبريد عند كتابته؛ وهنا تُحفظ.',
      }),
      zh: Object.freeze({
        'core-reports-shell.title': '摘要',
        'core-reports-shell.write_now': '立即写一份',
        'core-reports-shell.help':
          '{{assistant}} 按计划从该时段的一组固定数据中写出摘要——每次提出相同的问题，因此两个摘要可以比较。不同之处在于它如何理解这些数据。每份摘要写成时都会通过电子邮件发送；它们保存在这里。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-reports-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-reports-shell.${key}`],
    );
  },
  render() {},
};
