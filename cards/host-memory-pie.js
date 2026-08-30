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
        'core-host-memory-pie.empty':
          'No measured process memory in the latest reading.',
      }),
      es: Object.freeze({
        'core-host-memory-pie.empty':
          'No hay memoria de proceso medida en la lectura más reciente.',
      }),
      hi: Object.freeze({
        'core-host-memory-pie.empty':
          'नवीनतम रीडिंग में मापी गई प्रक्रिया मेमोरी नहीं है।',
      }),
      ar: Object.freeze({
        'core-host-memory-pie.empty':
          'لا توجد ذاكرة عمليات مقاسة في أحدث قراءة.',
      }),
      zh: Object.freeze({
        'core-host-memory-pie.empty': '最新读数中没有测得的进程内存。',
      }),
    }),
  }),
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
          'host.memory_pie_help',
          {},
          'Share of measured process memory. Drawn from PSS because a pie claims its slices make a whole, and resident memory cannot — a page shared by ten processes would be counted ten times and every slice inflated.',
        ) +
        (measured
          ? i18n.t(
              'host.memory_pie_total',
              { amount: humanBytes(measured), processes: sample.pss_procs },
              ' Total {{amount}} across the {{processes, number}} biggest processes. The ten largest services get a slice; everything under 1%, and everything past the tenth, is folded into one.',
            )
          : '');
  },
};
