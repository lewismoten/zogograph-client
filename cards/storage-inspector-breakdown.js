/* Core card package: storage inspector breakdown-chart empty state. */
window.ZogographStorageInspectorBreakdownCard = {
  manifest: Object.freeze({
    id: 'core-storage-inspector-breakdown',
    locales: ['core-storage-inspector-breakdown.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-inspector-breakdown.inspector_title':
          'What is taking the space',
        'core-storage-inspector-breakdown.empty': 'Nothing to show',
        'core-storage-inspector-breakdown.title': 'What it is made of',
        'core-storage-inspector-breakdown.category_trash': 'Trash',
        'core-storage-inspector-breakdown.category_logs': 'Logs',
        'core-storage-inspector-breakdown.category_temp': 'Temp',
        'core-storage-inspector-breakdown.kind_files': 'Files',
        'core-storage-inspector-breakdown.kind_directories': 'Directories',
        'core-storage-inspector-breakdown.sort_size': 'Largest',
        'core-storage-inspector-breakdown.sort_growth':
          'Grew most since last scan',
        'core-storage-inspector-breakdown.sort_hour':
          'Grew most in the last hour',
        'core-storage-inspector-breakdown.sort_today': 'Grew most today',
        'core-storage-inspector-breakdown.sort_week': 'Grew most in 7 days',
        'core-storage-inspector-breakdown.sort_new': 'Newly appeared',
        'core-storage-inspector-breakdown.sort_deleted': 'Gone since last scan',
        'core-storage-inspector-breakdown.masked_names':
          'Names are hidden until you sign in. The sizes and the growth are real.',
      }),
      es: Object.freeze({
        'core-storage-inspector-breakdown.inspector_title':
          'Qué está ocupando el espacio',
        'core-storage-inspector-breakdown.empty': 'Nada que mostrar',
        'core-storage-inspector-breakdown.title': 'De qué está hecho',
        'core-storage-inspector-breakdown.category_trash': 'Papelera',
        'core-storage-inspector-breakdown.category_logs': 'Registros',
        'core-storage-inspector-breakdown.category_temp': 'Temporales',
        'core-storage-inspector-breakdown.kind_files': 'Archivos',
        'core-storage-inspector-breakdown.kind_directories': 'Directorios',
        'core-storage-inspector-breakdown.sort_size': 'Más grandes',
        'core-storage-inspector-breakdown.sort_growth':
          'Crecieron más desde el último análisis',
        'core-storage-inspector-breakdown.sort_hour':
          'Crecieron más en la última hora',
        'core-storage-inspector-breakdown.sort_today': 'Crecieron más hoy',
        'core-storage-inspector-breakdown.sort_week': 'Crecieron más en 7 días',
        'core-storage-inspector-breakdown.sort_new':
          'Aparecieron recientemente',
        'core-storage-inspector-breakdown.sort_deleted':
          'Desaparecieron desde el último análisis',
        'core-storage-inspector-breakdown.masked_names':
          'Los nombres se ocultan hasta que inicie sesión. Los tamaños y el crecimiento son reales.',
      }),
      hi: Object.freeze({
        'core-storage-inspector-breakdown.inspector_title': 'कौन जगह ले रहा है',
        'core-storage-inspector-breakdown.empty': 'दिखाने के लिए कुछ नहीं',
        'core-storage-inspector-breakdown.title': 'यह किससे बना है',
        'core-storage-inspector-breakdown.category_trash': 'कचरा',
        'core-storage-inspector-breakdown.category_logs': 'लॉग',
        'core-storage-inspector-breakdown.category_temp': 'अस्थायी',
        'core-storage-inspector-breakdown.kind_files': 'फ़ाइलें',
        'core-storage-inspector-breakdown.kind_directories': 'डायरेक्टरी',
        'core-storage-inspector-breakdown.sort_size': 'सबसे बड़े',
        'core-storage-inspector-breakdown.sort_growth':
          'पिछले स्कैन के बाद सबसे अधिक बढ़े',
        'core-storage-inspector-breakdown.sort_hour':
          'पिछले घंटे में सबसे अधिक बढ़े',
        'core-storage-inspector-breakdown.sort_today': 'आज सबसे अधिक बढ़े',
        'core-storage-inspector-breakdown.sort_week':
          '7 दिनों में सबसे अधिक बढ़े',
        'core-storage-inspector-breakdown.sort_new': 'नए दिखाई दिए',
        'core-storage-inspector-breakdown.sort_deleted':
          'पिछले स्कैन के बाद गायब',
        'core-storage-inspector-breakdown.masked_names':
          'साइन इन करने तक नाम छिपे रहते हैं। आकार और वृद्धि वास्तविक हैं।',
      }),
      ar: Object.freeze({
        'core-storage-inspector-breakdown.inspector_title':
          'ما الذي يستهلك المساحة',
        'core-storage-inspector-breakdown.empty': 'لا شيء لعرضه',
        'core-storage-inspector-breakdown.title': 'ممّ يتكون',
        'core-storage-inspector-breakdown.category_trash': 'سلة المهملات',
        'core-storage-inspector-breakdown.category_logs': 'السجلات',
        'core-storage-inspector-breakdown.category_temp': 'ملفات مؤقتة',
        'core-storage-inspector-breakdown.kind_files': 'ملفات',
        'core-storage-inspector-breakdown.kind_directories': 'مجلدات',
        'core-storage-inspector-breakdown.sort_size': 'الأكبر',
        'core-storage-inspector-breakdown.sort_growth': 'نما أكثر منذ آخر فحص',
        'core-storage-inspector-breakdown.sort_hour':
          'نما أكثر في الساعة الماضية',
        'core-storage-inspector-breakdown.sort_today': 'نما أكثر اليوم',
        'core-storage-inspector-breakdown.sort_week': 'نما أكثر خلال 7 أيام',
        'core-storage-inspector-breakdown.sort_new': 'ظهر حديثًا',
        'core-storage-inspector-breakdown.sort_deleted': 'اختفى منذ آخر فحص',
        'core-storage-inspector-breakdown.masked_names':
          'تُخفى الأسماء حتى تسجّل الدخول. الأحجام والنمو حقيقيان.',
      }),
      zh: Object.freeze({
        'core-storage-inspector-breakdown.inspector_title':
          '哪些内容占用了空间',
        'core-storage-inspector-breakdown.empty': '没有可显示的内容',
        'core-storage-inspector-breakdown.title': '它由什么构成',
        'core-storage-inspector-breakdown.category_trash': '回收站',
        'core-storage-inspector-breakdown.category_logs': '日志',
        'core-storage-inspector-breakdown.category_temp': '临时文件',
        'core-storage-inspector-breakdown.kind_files': '文件',
        'core-storage-inspector-breakdown.kind_directories': '目录',
        'core-storage-inspector-breakdown.sort_size': '最大',
        'core-storage-inspector-breakdown.sort_growth': '自上次扫描后增长最多',
        'core-storage-inspector-breakdown.sort_hour': '过去一小时增长最多',
        'core-storage-inspector-breakdown.sort_today': '今天增长最多',
        'core-storage-inspector-breakdown.sort_week': '7 天内增长最多',
        'core-storage-inspector-breakdown.sort_new': '新出现',
        'core-storage-inspector-breakdown.sort_deleted': '自上次扫描后消失',
        'core-storage-inspector-breakdown.masked_names':
          '登录前名称会被隐藏。大小和增长数据是真实的。',
      }),
    }),
  }),
  render({ key = 'empty' } = {}, { i18n }) {
    return i18n.t(
      `core-storage-inspector-breakdown.${key}`,
      {},
      this.manifest.catalogs.en[`core-storage-inspector-breakdown.${key}`],
    );
  },
};
