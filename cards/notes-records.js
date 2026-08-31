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
