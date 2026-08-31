/* Core card package: the shell supplies charting, slice construction, and formatters. */
window.ZogographHostMemoryPieCard = {
  manifest: Object.freeze({
    id: 'core-host-memory-pie',
    locales: ['core-host-memory-pie.'],
    helpers: [
      'chart',
      'note',
      'memorySlices',
      'drawPieChart',
      'humanBytes',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-memory-pie.title': 'Where the memory goes',
        'core-host-memory-pie.panel_help':
          'The share of measured process memory each service holds, drawn from {{pss}} rather than {{rss}} — a pie claims its slices make a whole, and resident memory cannot: a page shared by ten processes would be counted ten times.',
        'core-host-memory-pie.empty':
          'No measured process memory in the latest reading.',
        'core-host-memory-pie.help':
          'Share of measured process memory. Drawn from PSS because a pie claims its slices make a whole, and resident memory cannot — a page shared by ten processes would be counted ten times and every slice inflated.',
        'core-host-memory-pie.total':
          ' Total {{amount}} across the {{processes, number}} biggest processes. The ten largest services get a slice; everything under 1%, and everything past the tenth, is folded into one.',
      }),
      es: Object.freeze({
        'core-host-memory-pie.title': 'Adónde va la memoria',
        'core-host-memory-pie.panel_help':
          'La parte de memoria de proceso medida que ocupa cada servicio, calculada con {{pss}} y no con {{rss}}: un gráfico circular supone que sus porciones forman un todo, y la memoria residente no puede hacerlo, pues una página compartida por diez procesos se contaría diez veces.',
        'core-host-memory-pie.empty':
          'No hay memoria de proceso medida en la lectura más reciente.',
        'core-host-memory-pie.help':
          'Parte de la memoria de proceso medida. Se dibuja con PSS porque un gráfico circular afirma que sus porciones forman un todo, y la memoria residente no puede hacerlo: una página compartida por diez procesos se contaría diez veces e inflaría cada porción.',
        'core-host-memory-pie.total':
          ' {{amount}} en los {{processes, number}} procesos más grandes. Los diez servicios más grandes reciben una porción; todo lo que esté por debajo del 1%, y todo lo que quede después del décimo, se agrupa en una sola.',
      }),
      hi: Object.freeze({
        'core-host-memory-pie.title': 'मेमोरी कहाँ जाती है',
        'core-host-memory-pie.panel_help':
          'हर सेवा के पास मापी गई प्रक्रिया मेमोरी का हिस्सा, {{rss}} की जगह {{pss}} से लिया गया है। पाई चार्ट मानता है कि उसके हिस्से पूरा बनाते हैं, जबकि रेज़िडेंट मेमोरी ऐसा नहीं कर सकती: दस प्रक्रियाओं के बीच साझा पेज को दस बार गिना जाएगा।',
        'core-host-memory-pie.empty':
          'नवीनतम रीडिंग में मापी गई प्रक्रिया मेमोरी नहीं है।',
        'core-host-memory-pie.help':
          'मापी गई प्रक्रिया मेमोरी का हिस्सा। PSS से बनाया गया है क्योंकि पाई चार्ट की स्लाइस एक पूरा हिस्सा बताती हैं, और रेज़िडेंट मेमोरी ऐसा नहीं कर सकती — दस प्रक्रियाओं में साझा पृष्ठ दस बार गिना जाएगा और हर स्लाइस बढ़ जाएगी।',
        'core-host-memory-pie.total':
          ' सबसे बड़ी {{processes, number}} प्रक्रियाओं में कुल {{amount}}। दस सबसे बड़ी सेवाओं को स्लाइस मिलती है; 1% से कम और दसवीं के बाद की हर चीज़ एक में जोड़ी जाती है।',
      }),
      ar: Object.freeze({
        'core-host-memory-pie.title': 'أين تذهب الذاكرة',
        'core-host-memory-pie.panel_help':
          'حصة ذاكرة العمليات المقاسة التي يشغلها كلّ خادم، مأخوذة من {{pss}} بدل {{rss}}. يفترض المخطط الدائري أن شرائحه تكوّن الكل، أما الذاكرة المقيمة فلا يمكنها ذلك: فالصفحة المشتركة بين عشر عمليات ستُحسب عشر مرات.',
        'core-host-memory-pie.empty':
          'لا توجد ذاكرة عمليات مقاسة في أحدث قراءة.',
        'core-host-memory-pie.help':
          'حصة من ذاكرة العمليات المقاسة. تُرسم من PSS لأن المخطط الدائري يدّعي أن شرائحه تكوّن الكل، والذاكرة المقيمة لا تستطيع ذلك — فالصفحة المشتركة بين عشر عمليات ستُحسب عشر مرات وتُضخّم كل شريحة.',
        'core-host-memory-pie.total':
          ' {{amount}} عبر أكبر {{processes, number}} عملية. تحصل أكبر عشر خدمات على شريحة؛ وكل ما دون 1% وكل ما بعد العاشرة يُطوى في شريحة واحدة.',
      }),
      zh: Object.freeze({
        'core-host-memory-pie.title': '内存去了哪里',
        'core-host-memory-pie.panel_help':
          '每项服务占用的已测量进程内存份额，采用 {{pss}} 而非 {{rss}}。饼图要求各分块构成整体，而常驻内存做不到：被十个进程共享的一页会被计算十次。',
        'core-host-memory-pie.empty': '最新读数中没有测得的进程内存。',
        'core-host-memory-pie.help':
          '已测量进程内存的份额。采用 PSS 绘制，因为饼图意味着各部分构成整体，而常驻内存无法如此：被十个进程共享的一页会被计算十次，每一块都会被放大。',
        'core-host-memory-pie.total':
          ' 最大的 {{processes, number}} 个进程共 {{amount}}。最大的十项服务各占一块；低于 1% 的部分及第十项之后的所有内容都合并为一块。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-memory-pie.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-memory-pie.${key}`],
    );
  },
  render(input, helpers) {
    const { services, sample } = input;
    const { chart, note, memorySlices, drawPieChart, humanBytes, i18n } =
      helpers;
    const measured = Number(sample?.pss_sum_bytes) || 0;
    drawPieChart(chart, memorySlices(services, measured), {
      empty: i18n.t(
        'core-host-memory-pie.empty',
        {},
        'No measured process memory in the latest reading.',
      ),
    });
    if (note)
      note.textContent =
        i18n.t(
          'core-host-memory-pie.help',
          {},
          'Share of measured process memory. Drawn from PSS because a pie claims its slices make a whole, and resident memory cannot — a page shared by ten processes would be counted ten times and every slice inflated.',
        ) +
        (measured
          ? i18n.t(
              'core-host-memory-pie.total',
              { amount: humanBytes(measured), processes: sample.pss_procs },
              ' Total {{amount}} across the {{processes, number}} biggest processes. The ten largest services get a slice; everything under 1%, and everything past the tenth, is folded into one.',
            )
          : '');
  },
};
