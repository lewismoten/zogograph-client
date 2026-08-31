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
