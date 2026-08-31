/* Core card package: static controls and guidance for Whois lookups. */
window.ZogographWhoisShellCard = {
  manifest: Object.freeze({
    id: 'core-whois-shell',
    locales: ['core-whois-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-whois-shell.title': 'Look a name up',
        'core-whois-shell.help':
          'A one-off lookup for a name you are not watching — whether it is taken, who holds it, when it expires. Nothing is stored and no domain is added to the list; there is a button for that if the answer makes you want one.',
        'core-whois-shell.ask': 'Ask',
      }),
      es: Object.freeze({
        'core-whois-shell.title': 'Buscar un nombre',
        'core-whois-shell.help':
          'Una consulta puntual de un nombre que no está vigilando: si está ocupado, quién lo posee y cuándo vence. No se almacena nada ni se añade ningún dominio a la lista; hay un botón para ello si la respuesta hace que quiera añadirlo.',
        'core-whois-shell.ask': 'Consultar',
      }),
      hi: Object.freeze({
        'core-whois-shell.title': 'नाम खोजें',
        'core-whois-shell.help':
          'ऐसे नाम के लिए एक बार की खोज जिस पर आप नज़र नहीं रख रहे—क्या वह लिया गया है, किसके पास है और कब समाप्त होता है। कुछ भी संग्रहीत नहीं होता और कोई डोमेन सूची में नहीं जोड़ा जाता; यदि जवाब से आप उसे जोड़ना चाहें तो उसके लिए बटन है।',
        'core-whois-shell.ask': 'पूछें',
      }),
      ar: Object.freeze({
        'core-whois-shell.title': 'ابحث عن اسم',
        'core-whois-shell.help':
          'بحث لمرة واحدة عن اسم لا تراقبه: هل حُجز، ومن يملكه، ومتى ينتهي. لا يُخزن شيء ولا يُضاف أي نطاق إلى القائمة؛ يوجد زر لذلك إذا جعلتك الإجابة ترغب في إضافته.',
        'core-whois-shell.ask': 'اسأل',
      }),
      zh: Object.freeze({
        'core-whois-shell.title': '查询一个名称',
        'core-whois-shell.help':
          '对未监视名称进行一次性查询：它是否已被注册、由谁持有、何时到期。不存储任何内容，也不会将域名添加到列表中；若查询结果让您想添加它，可以使用相应按钮。',
        'core-whois-shell.ask': '查询',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-whois-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-whois-shell.${key}`],
    );
  },
  render() {},
};
