/* Core card package: chart annotation for compacted history. */
window.ZogographChartCompactionNoteCard = {
  manifest: Object.freeze({
    id: 'core-chart-compaction-note',
    locales: ['core-chart-compaction-note.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chart-compaction-note.bucket':
          'Each point covers {{duration}} and shows the worst value in that period, not an average.',
        'core-chart-compaction-note.disk':
          'The oldest readings here are also compacted on disk, up to {{readings, number}} readings to a row. Readings from around an alert are never compacted.',
      }),
      es: Object.freeze({
        'core-chart-compaction-note.bucket':
          'Cada punto cubre {{duration}} y muestra el peor valor de ese período, no un promedio.',
        'core-chart-compaction-note.disk':
          'Las lecturas más antiguas también se compactan en el disco, hasta {{readings, number}} lecturas por fila. Las lecturas cercanas a una alerta nunca se compactan.',
      }),
      hi: Object.freeze({
        'core-chart-compaction-note.bucket':
          'हर बिंदु {{duration}} को कवर करता है और उस अवधि का सबसे खराब मान दिखाता है, औसत नहीं।',
        'core-chart-compaction-note.disk':
          'यहाँ की सबसे पुरानी रीडिंग डिस्क पर भी कॉम्पैक्ट की गई हैं, एक पंक्ति में {{readings, number}} रीडिंग तक। अलर्ट के आस-पास की रीडिंग कभी कॉम्पैक्ट नहीं की जातीं।',
      }),
      ar: Object.freeze({
        'core-chart-compaction-note.bucket':
          'تغطي كل نقطة {{duration}} وتعرض أسوأ قيمة في تلك الفترة، لا متوسطًا.',
        'core-chart-compaction-note.disk':
          'تُضغط أقدم القراءات هنا أيضًا على القرص، حتى {{readings, number}} قراءة في الصف. لا تُضغط القراءات المحيطة بتنبيه أبدًا.',
      }),
      zh: Object.freeze({
        'core-chart-compaction-note.bucket':
          '每个点覆盖 {{duration}}，并显示该时段内的最坏值，而非平均值。',
        'core-chart-compaction-note.disk':
          '此处最早的读数也会在磁盘上压缩，每行最多 {{readings, number}} 条读数。警报附近的读数永远不会压缩。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-chart-compaction-note.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
