/* Core card package: symlink result-table kinds and labels. */
window.ZogographSymlinkTableCard = {
  manifest: Object.freeze({
    id: 'core-symlink-table',
    locales: ['core-symlink-table.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-table.kind_loop': 'Loop',
        'core-symlink-table.kind_ancestor': 'Endless directory',
        'core-symlink-table.kind_outside': 'Leaves the tree',
        'core-symlink-table.kind_broken': 'Broken',
        'core-symlink-table.link': 'Link',
        'core-symlink-table.new_marker': ' new',
        'core-symlink-table.points_at': 'Points at',
        'core-symlink-table.why_matters': 'Why it matters',
        'core-symlink-table.showing_capped':
          'Showing {{shown}} of {{total}} problems. The rest are real and were counted — only the drawing is capped. Clearing out a path below works on all of them, not just the ones on screen.',
      }),
      es: Object.freeze({
        'core-symlink-table.kind_loop': 'Bucle',
        'core-symlink-table.kind_ancestor': 'Directorio infinito',
        'core-symlink-table.kind_outside': 'Sale del árbol',
        'core-symlink-table.kind_broken': 'Roto',
        'core-symlink-table.link': 'Enlace',
        'core-symlink-table.new_marker': ' nuevo',
        'core-symlink-table.points_at': 'Apunta a',
        'core-symlink-table.why_matters': 'Por qué importa',
        'core-symlink-table.showing_capped':
          'Se muestran {{shown}} de {{total}} problemas. El resto son reales y se contaron; solo se limita el dibujo. Limpiar una ruta más abajo funciona con todos ellos, no solo con los que aparecen en pantalla.',
      }),
      hi: Object.freeze({
        'core-symlink-table.kind_loop': 'लूप',
        'core-symlink-table.kind_ancestor': 'अनंत डायरेक्टरी',
        'core-symlink-table.kind_outside': 'ट्री से बाहर जाता है',
        'core-symlink-table.kind_broken': 'टूटा हुआ',
        'core-symlink-table.link': 'लिंक',
        'core-symlink-table.new_marker': ' नया',
        'core-symlink-table.points_at': 'इंगित करता है',
        'core-symlink-table.why_matters': 'यह क्यों महत्वपूर्ण है',
        'core-symlink-table.showing_capped':
          '{{total}} समस्याओं में से {{shown}} दिखाई जा रही हैं। बाकी वास्तविक हैं और गिनी गई हैं — केवल चित्रण की सीमा है। नीचे पथ साफ़ करना केवल स्क्रीन वाली नहीं, सभी पर काम करता है।',
      }),
      ar: Object.freeze({
        'core-symlink-table.kind_loop': 'حلقة',
        'core-symlink-table.kind_ancestor': 'دليل لا نهائي',
        'core-symlink-table.kind_outside': 'يخرج من الشجرة',
        'core-symlink-table.kind_broken': 'معطل',
        'core-symlink-table.link': 'رابط',
        'core-symlink-table.new_marker': ' جديد',
        'core-symlink-table.points_at': 'يشير إلى',
        'core-symlink-table.why_matters': 'لماذا يهم',
        'core-symlink-table.showing_capped':
          'يُعرض {{shown}} من {{total}} مشكلة. البقية حقيقية وقد عُدّت — يقتصر الحد على الرسم فقط. تنظيف مسار أدناه يعمل عليها جميعًا، لا على الظاهر على الشاشة فقط.',
      }),
      zh: Object.freeze({
        'core-symlink-table.kind_loop': '循环',
        'core-symlink-table.kind_ancestor': '无限目录',
        'core-symlink-table.kind_outside': '离开目录树',
        'core-symlink-table.kind_broken': '损坏',
        'core-symlink-table.link': '链接',
        'core-symlink-table.new_marker': ' 新',
        'core-symlink-table.points_at': '指向',
        'core-symlink-table.why_matters': '重要原因',
        'core-symlink-table.showing_capped':
          '显示 {{total}} 个问题中的 {{shown}} 个。其余问题真实存在且已计数——只有绘制受限。清理下方路径会作用于全部问题，不仅是屏幕上显示的问题。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    return i18n.t(
      `core-symlink-table.${key}`,
      values,
      this.manifest.catalogs.en[`core-symlink-table.${key}`],
    );
  },
};
