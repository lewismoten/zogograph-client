/* Core card package: page shell for the CageFS skeleton view. */
window.ZogographCagefsShellCard = {
  manifest: Object.freeze({
    id: 'core-cagefs-shell',
    locales: ['core-cagefs-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cagefs-shell.title': 'The CageFS skeleton',
        'core-cagefs-shell.help':
          "CageFS gives each account its own {{usr}}, {{lib}} and {{proc}}, assembled from a shared skeleton at {{skeleton}}. That is why a program the server has can be missing from inside the account, and why this dashboard's view of the machine is narrower than the machine. From inside a cage the skeleton's own mounts are invisible — so an empty answer here is not evidence that CageFS is absent, and this says which of the two it is looking at.",
      }),
      es: Object.freeze({
        'core-cagefs-shell.title': 'El esqueleto de CageFS',
        'core-cagefs-shell.help':
          'CageFS proporciona a cada cuenta su propio {{usr}}, {{lib}} y {{proc}}, montados desde un esqueleto compartido en {{skeleton}}. Por eso un programa que existe en el servidor puede faltar dentro de la cuenta, y por eso la vista de esta máquina en el panel es más limitada que la propia máquina. Desde dentro de una jaula, los montajes del esqueleto son invisibles; una respuesta vacía aquí no demuestra que CageFS esté ausente, y esta vista indica cuál de las dos cosas está mirando.',
      }),
      hi: Object.freeze({
        'core-cagefs-shell.title': 'CageFS स्केलेटन',
        'core-cagefs-shell.help':
          'CageFS हर खाते को उसका अपना {{usr}}, {{lib}} और {{proc}} देता है, जो {{skeleton}} में साझा स्केलेटन से बनाया जाता है। इसलिए सर्वर पर मौजूद कोई प्रोग्राम खाते के भीतर गायब हो सकता है और इस डैशबोर्ड का मशीन दृश्य मशीन से संकरा होता है। क़ैद के अंदर स्केलेटन के अपने माउंट अदृश्य होते हैं; इसलिए यहाँ खाली उत्तर CageFS के न होने का प्रमाण नहीं है, और यह बताता है कि यह दोनों में से किसे देख रहा है।',
      }),
      ar: Object.freeze({
        'core-cagefs-shell.title': 'هيكل CageFS',
        'core-cagefs-shell.help':
          'يمنح CageFS كل حساب {{usr}} و{{lib}} و{{proc}} خاصًا به، مركبة من هيكل مشترك في {{skeleton}}. لذلك قد يكون برنامج موجود على الخادم مفقودًا من داخل الحساب، ولهذا تكون رؤية هذه اللوحة للجهاز أضيق من الجهاز نفسه. من داخل البيئة المعزولة تكون نقاط تركيب الهيكل غير مرئية؛ لذلك لا يُثبت الجواب الفارغ هنا غياب CageFS، وتوضح هذه الصفحة أي الأمرين تفحص.',
      }),
      zh: Object.freeze({
        'core-cagefs-shell.title': 'CageFS 骨架',
        'core-cagefs-shell.help':
          'CageFS 为每个账户提供各自的 {{usr}}、{{lib}} 和 {{proc}}，它们由 {{skeleton}} 中的共享骨架组成。因此，服务器上已有的程序可能在账户内部缺失，仪表板看到的机器范围也比机器本身更窄。在隔离环境中，骨架自身的挂载点不可见；所以这里的空结果并不证明 CageFS 不存在，本页会说明当前查看的是两者中的哪一个。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cagefs-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-cagefs-shell.${key}`],
    );
  },
  render() {},
};
