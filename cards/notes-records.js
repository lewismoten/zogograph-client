/* Core card package: localized labels for the saved-notes table. */
window.ZogographNotesRecordsCard = {
  manifest: Object.freeze({
    id: 'core-notes-records',
    locales: ['core-notes-records.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-notes-records.table_time': 'Time',
        'core-notes-records.table_icons': 'Icons',
        'core-notes-records.table_note': 'Note',
        'core-notes-records.table_size': 'Size',
        'core-notes-records.somebody': 'somebody',
        'core-notes-records.selected': '{{count}} selected',
        'core-notes-records.select_to_act': 'Select notes to dismiss or delete',
        'core-notes-records.dismiss_selected_help':
          'Clears selected broadcast notes; ordinary notes are left alone.',
        'core-notes-records.select_from': 'Select note from {{when}}',
        'core-notes-records.capture_size': '{{captures}} captures · {{size}}',
        'core-notes-records.pictures_kept':
          '{{count}} pictures kept with this note',
        'core-notes-records.broadcast_cleared':
          'Was shown to everyone; cleared by {{actor}}.',
        'core-notes-records.broadcast_active':
          'Shown to everyone until it is cleared.',
        'core-notes-records.about': 'About',
        'core-notes-records.written': 'Written',
        'core-notes-records.page': 'Page',
        'core-notes-records.holding': 'Holding',
        'core-notes-records.from': 'Note from {{when}}',
        'core-notes-records.inbox_help':
          'Click a note in the inbox to read it without stretching the table.',
        'core-notes-records.what_captured': 'What it captured',
        'core-notes-records.capture_loading': 'Looking…',
        'core-notes-records.capture_empty':
          'Nothing was captured with this one.',
        'core-notes-records.capture_retention_help':
          'Captures are held outside every retention rule, which is the point of one — and also means nothing expires them.',
        'core-notes-records.capture_page': 'The data behind one page',
        'core-notes-records.capture_site': 'The data behind every page',
        'core-notes-records.capture_databases': 'A copy of both databases',
        'core-notes-records.capture_image': 'A picture',
      }),
      es: Object.freeze({
        'core-notes-records.table_time': 'Hora',
        'core-notes-records.table_icons': 'Iconos',
        'core-notes-records.table_note': 'Nota',
        'core-notes-records.table_size': 'Tamaño',
        'core-notes-records.somebody': 'alguien',
        'core-notes-records.selected': '{{count}} seleccionadas',
        'core-notes-records.select_to_act':
          'Seleccione notas para descartarlas o eliminarlas',
        'core-notes-records.dismiss_selected_help':
          'Borra las notas de difusión seleccionadas; las notas normales se dejan intactas.',
        'core-notes-records.select_from': 'Seleccionar nota de {{when}}',
        'core-notes-records.capture_size': '{{captures}} capturas · {{size}}',
        'core-notes-records.pictures_kept':
          '{{count}} imágenes guardadas con esta nota',
        'core-notes-records.broadcast_cleared':
          'Se mostró a todos; {{actor}} la borró.',
        'core-notes-records.broadcast_active':
          'Se muestra a todos hasta que se borre.',
        'core-notes-records.about': 'Acerca de',
        'core-notes-records.written': 'Escrito',
        'core-notes-records.page': 'Página',
        'core-notes-records.holding': 'Contiene',
        'core-notes-records.from': 'Nota de {{when}}',
        'core-notes-records.inbox_help':
          'Haga clic en una nota de la bandeja de entrada para leerla sin estirar la tabla.',
        'core-notes-records.what_captured': 'Lo que capturó',
        'core-notes-records.capture_loading': 'Buscando…',
        'core-notes-records.capture_empty': 'No se capturó nada con esta nota.',
        'core-notes-records.capture_retention_help':
          'Las capturas se conservan fuera de toda regla de retención, que es su propósito, y también significa que nada las caduca.',
        'core-notes-records.capture_page': 'Los datos detrás de una página',
        'core-notes-records.capture_site': 'Los datos detrás de cada página',
        'core-notes-records.capture_databases':
          'Una copia de ambas bases de datos',
        'core-notes-records.capture_image': 'Una imagen',
      }),
      hi: Object.freeze({
        'core-notes-records.table_time': 'समय',
        'core-notes-records.table_icons': 'चिह्न',
        'core-notes-records.table_note': 'नोट',
        'core-notes-records.table_size': 'आकार',
        'core-notes-records.somebody': 'किसी ने',
        'core-notes-records.selected': '{{count}} चुनी गईं',
        'core-notes-records.select_to_act': 'खारिज या हटाने के लिए नोट चुनें',
        'core-notes-records.dismiss_selected_help':
          'चुने गए प्रसारण नोट साफ़ करता है; सामान्य नोट वैसे ही रहते हैं।',
        'core-notes-records.select_from': '{{when}} का नोट चुनें',
        'core-notes-records.capture_size': '{{captures}} कैप्चर · {{size}}',
        'core-notes-records.pictures_kept':
          'इस नोट के साथ {{count}} चित्र रखे गए',
        'core-notes-records.broadcast_cleared':
          'सभी को दिखाया गया था; {{actor}} ने साफ़ किया।',
        'core-notes-records.broadcast_active':
          'साफ़ होने तक सभी को दिखाया जाता है।',
        'core-notes-records.about': 'विषय',
        'core-notes-records.written': 'लिखा गया',
        'core-notes-records.page': 'पृष्ठ',
        'core-notes-records.holding': 'इसमें है',
        'core-notes-records.from': '{{when}} की टिप्पणी',
        'core-notes-records.inbox_help':
          'तालिका को फैलाए बिना पढ़ने के लिए इनबॉक्स में किसी टिप्पणी पर क्लिक करें।',
        'core-notes-records.what_captured': 'इसने क्या कैप्चर किया',
        'core-notes-records.capture_loading': 'देख रहे हैं…',
        'core-notes-records.capture_empty': 'इसमें कुछ भी कैप्चर नहीं हुआ।',
        'core-notes-records.capture_retention_help':
          'कैप्चर हर प्रतिधारण नियम से बाहर रखे जाते हैं, जो उनका उद्देश्य है — और इसका अर्थ यह भी है कि कुछ भी उन्हें समाप्त नहीं करता।',
        'core-notes-records.capture_page': 'एक पृष्ठ के पीछे का डेटा',
        'core-notes-records.capture_site': 'हर पृष्ठ के पीछे का डेटा',
        'core-notes-records.capture_databases': 'दोनों डेटाबेस की एक प्रति',
        'core-notes-records.capture_image': 'एक चित्र',
      }),
      ar: Object.freeze({
        'core-notes-records.table_time': 'الوقت',
        'core-notes-records.table_icons': 'الأيقونات',
        'core-notes-records.table_note': 'ملاحظة',
        'core-notes-records.table_size': 'الحجم',
        'core-notes-records.somebody': 'شخص ما',
        'core-notes-records.selected': '{{count}} محددة',
        'core-notes-records.select_to_act': 'حدد ملاحظات لمسحها أو حذفها',
        'core-notes-records.dismiss_selected_help':
          'يمسح ملاحظات البث المحددة؛ تُترك الملاحظات العادية كما هي.',
        'core-notes-records.select_from': 'حدد ملاحظة من {{when}}',
        'core-notes-records.capture_size': '{{captures}} لقطات · {{size}}',
        'core-notes-records.pictures_kept':
          '{{count}} صور محفوظة مع هذه الملاحظة',
        'core-notes-records.broadcast_cleared':
          'عُرضت للجميع؛ مسحها {{actor}}.',
        'core-notes-records.broadcast_active': 'تُعرض للجميع حتى تُمسح.',
        'core-notes-records.about': 'حول',
        'core-notes-records.written': 'كُتبت',
        'core-notes-records.page': 'الصفحة',
        'core-notes-records.holding': 'تحتوي',
        'core-notes-records.from': 'ملاحظة من {{when}}',
        'core-notes-records.inbox_help':
          'انقر ملاحظة في صندوق الوارد لقراءتها دون تمديد الجدول.',
        'core-notes-records.what_captured': 'ما التقطه',
        'core-notes-records.capture_loading': 'يجري البحث…',
        'core-notes-records.capture_empty': 'لم يُلتقط شيء مع هذه الملاحظة.',
        'core-notes-records.capture_retention_help':
          'تُحفظ اللقطات خارج كل قاعدة احتفاظ، وهذا هو الغرض منها — ويعني أيضاً أنه لا شيء يُنهي صلاحيتها.',
        'core-notes-records.capture_page': 'البيانات وراء صفحة واحدة',
        'core-notes-records.capture_site': 'البيانات وراء كل صفحة',
        'core-notes-records.capture_databases': 'نسخة من قاعدتي البيانات',
        'core-notes-records.capture_image': 'صورة',
      }),
      zh: Object.freeze({
        'core-notes-records.table_time': '时间',
        'core-notes-records.table_icons': '图标',
        'core-notes-records.table_note': '注释',
        'core-notes-records.table_size': '大小',
        'core-notes-records.somebody': '某人',
        'core-notes-records.selected': '已选 {{count}} 项',
        'core-notes-records.select_to_act': '选择要关闭或删除的注释',
        'core-notes-records.dismiss_selected_help':
          '清除选定的广播注释；普通注释保持不变。',
        'core-notes-records.select_from': '选择 {{when}} 的注释',
        'core-notes-records.capture_size': '{{captures}} 个捕获 · {{size}}',
        'core-notes-records.pictures_kept': '此注释保留了 {{count}} 张图片',
        'core-notes-records.broadcast_cleared':
          '曾向所有人显示；已由 {{actor}} 清除。',
        'core-notes-records.broadcast_active': '会向所有人显示，直到被清除。',
        'core-notes-records.about': '关于',
        'core-notes-records.written': '写于',
        'core-notes-records.page': '页面',
        'core-notes-records.holding': '包含',
        'core-notes-records.from': '来自 {{when}} 的备注',
        'core-notes-records.inbox_help':
          '单击收件箱中的备注即可阅读，而不会拉长表格。',
        'core-notes-records.what_captured': '它捕获的内容',
        'core-notes-records.capture_loading': '正在查看…',
        'core-notes-records.capture_empty': '此注释没有捕获任何内容。',
        'core-notes-records.capture_retention_help':
          '捕获内容保留在所有保留规则之外，这正是它的用途——也意味着没有任何内容会使其过期。',
        'core-notes-records.capture_page': '一个页面背后的数据',
        'core-notes-records.capture_site': '每个页面背后的数据',
        'core-notes-records.capture_databases': '两个数据库的副本',
        'core-notes-records.capture_image': '一张图片',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-notes-records.${key}`,
      values,
      this.manifest.catalogs.en[`core-notes-records.${key}`],
    );
  },
  render() {},
};
