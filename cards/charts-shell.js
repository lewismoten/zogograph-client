/* Core card package: shared guidance for the vital-charts view. */
window.ZogographChartsShellCard = {
  manifest: Object.freeze({
    id: 'core-charts-shell',
    locales: ['core-charts-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-charts-shell.help':
          'The five measurements that describe the machine as a whole, over whatever range is selected. The gauges on the overview say where each one is {{now}}; these say how it got there, which is the half that tells you whether it is going to be a problem.',
      }),
      es: Object.freeze({
        'core-charts-shell.help':
          'Las cinco mediciones que describen la máquina completa en el intervalo seleccionado. Los indicadores de la vista general dicen dónde está cada una {{now}}; estas gráficas muestran cómo llegó ahí, la mitad que indica si será un problema.',
      }),
      hi: Object.freeze({
        'core-charts-shell.help':
          'चुनी गई सीमा पर पूरी मशीन को बताने वाले पाँच माप। ओवरव्यू के गेज कहते हैं हर एक {{now}} कहाँ है; ये बताते हैं वह वहाँ कैसे पहुँचा, वही आधा बताता है कि समस्या बनेगा या नहीं।',
      }),
      ar: Object.freeze({
        'core-charts-shell.help':
          'القياسات الخمسة التي تصف الآلة كلها على أي نطاق مختار. تقول المقاييس في النظرة العامة أين يقع كل واحد {{now}}؛ وتقول هذه كيف وصل إلى هناك، وهو النصف الذي يخبرك هل سيصبح مشكلة.',
      }),
      zh: Object.freeze({
        'core-charts-shell.help':
          '在所选范围内描述整台机器的五项测量。概览中的仪表说明每项目前{{now}}处于何处；这些图表说明它是如何到达那里，这一半能告诉您它是否会成为问题。',
      }),
    }),
  }),
  render() {},
};
