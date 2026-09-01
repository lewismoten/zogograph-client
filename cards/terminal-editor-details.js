/* Core card package: metadata and guidance in the terminal editor. */
window.ZogographTerminalEditorDetailsCard = {
  manifest: Object.freeze({
    id: 'core-terminal-editor-details',
    locales: ['core-terminal-editor-details.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-editor-details.shortcuts_editable':
          'Find: Ctrl/Cmd+F · Replace: Ctrl/Cmd+H · Undo: Ctrl/Cmd+Z · Redo: Ctrl/Cmd+Shift+Z · Review: Ctrl/Cmd+Shift+D',
        'core-terminal-editor-details.shortcuts_read_only': 'Find: Ctrl/Cmd+F',
        'core-terminal-editor-details.writable_note':
          '{{size}} · text files up to 1 MB · saves preserve line endings, make a private recovery copy, and refuse to overwrite newer changes.',
        'core-terminal-editor-details.read_only_note':
          '{{size}} · read-only: this account cannot save this file.',
        'core-terminal-editor-details.unknown': 'unknown',
        'core-terminal-editor-details.encoding': 'Encoding',
        'core-terminal-editor-details.line_endings': 'Line endings',
        'core-terminal-editor-details.modified': 'Modified',
        'core-terminal-editor-details.permissions': 'Permissions',
      }),
      es: Object.freeze({
        'core-terminal-editor-details.shortcuts_editable':
          'Buscar: Ctrl/Cmd+F · Reemplazar: Ctrl/Cmd+H · Deshacer: Ctrl/Cmd+Z · Rehacer: Ctrl/Cmd+Mayús+Z · Revisar: Ctrl/Cmd+Mayús+D',
        'core-terminal-editor-details.shortcuts_read_only':
          'Buscar: Ctrl/Cmd+F',
        'core-terminal-editor-details.writable_note':
          '{{size}} · archivos de texto de hasta 1 MB · al guardar se conservan los finales de línea, se crea una copia de recuperación privada y se rechaza sobrescribir cambios más recientes.',
        'core-terminal-editor-details.read_only_note':
          '{{size}} · solo lectura: esta cuenta no puede guardar este archivo.',
        'core-terminal-editor-details.unknown': 'desconocido',
        'core-terminal-editor-details.encoding': 'Codificación',
        'core-terminal-editor-details.line_endings': 'Finales de línea',
        'core-terminal-editor-details.modified': 'Modificado',
        'core-terminal-editor-details.permissions': 'Permisos',
      }),
      hi: Object.freeze({
        'core-terminal-editor-details.shortcuts_editable':
          'खोजें: Ctrl/Cmd+F · बदलें: Ctrl/Cmd+H · पूर्ववत: Ctrl/Cmd+Z · फिर करें: Ctrl/Cmd+Shift+Z · समीक्षा: Ctrl/Cmd+Shift+D',
        'core-terminal-editor-details.shortcuts_read_only': 'खोजें: Ctrl/Cmd+F',
        'core-terminal-editor-details.writable_note':
          '{{size}} · 1 MB तक की पाठ फ़ाइलें · सहेजने पर पंक्ति-अंत सुरक्षित रहते हैं, निजी पुनर्प्राप्ति कॉपी बनती है और नए बदलावों को अधिलेखित करने से मना किया जाता है।',
        'core-terminal-editor-details.read_only_note':
          '{{size}} · केवल पढ़ने के लिए: यह खाता इस फ़ाइल को सहेज नहीं सकता।',
        'core-terminal-editor-details.unknown': 'अज्ञात',
        'core-terminal-editor-details.encoding': 'एन्कोडिंग',
        'core-terminal-editor-details.line_endings': 'पंक्ति अंत',
        'core-terminal-editor-details.modified': 'बदला गया',
        'core-terminal-editor-details.permissions': 'अनुमतियाँ',
      }),
      ar: Object.freeze({
        'core-terminal-editor-details.shortcuts_editable':
          'بحث: Ctrl/Cmd+F · استبدال: Ctrl/Cmd+H · تراجع: Ctrl/Cmd+Z · إعادة: Ctrl/Cmd+Shift+Z · مراجعة: Ctrl/Cmd+Shift+D',
        'core-terminal-editor-details.shortcuts_read_only': 'بحث: Ctrl/Cmd+F',
        'core-terminal-editor-details.writable_note':
          '{{size}} · ملفات نصية حتى 1 م.ب · يحفظ الحفظ نهايات السطور، وينشئ نسخة استرداد خاصة، ويرفض الكتابة فوق تغييرات أحدث.',
        'core-terminal-editor-details.read_only_note':
          '{{size}} · للقراءة فقط: لا يستطيع هذا الحساب حفظ هذا الملف.',
        'core-terminal-editor-details.unknown': 'غير معروف',
        'core-terminal-editor-details.encoding': 'الترميز',
        'core-terminal-editor-details.line_endings': 'نهايات السطور',
        'core-terminal-editor-details.modified': 'عُدّل',
        'core-terminal-editor-details.permissions': 'الأذونات',
      }),
      zh: Object.freeze({
        'core-terminal-editor-details.shortcuts_editable':
          '查找：Ctrl/Cmd+F · 替换：Ctrl/Cmd+H · 撤销：Ctrl/Cmd+Z · 重做：Ctrl/Cmd+Shift+Z · 审核：Ctrl/Cmd+Shift+D',
        'core-terminal-editor-details.shortcuts_read_only': '查找：Ctrl/Cmd+F',
        'core-terminal-editor-details.writable_note':
          '{{size}} · 最大 1 MB 的文本文件 · 保存会保留行尾、创建私有恢复副本，并拒绝覆盖较新的更改。',
        'core-terminal-editor-details.read_only_note':
          '{{size}} · 只读：此帐户无法保存此文件。',
        'core-terminal-editor-details.unknown': '未知',
        'core-terminal-editor-details.encoding': '编码',
        'core-terminal-editor-details.line_endings': '行尾',
        'core-terminal-editor-details.modified': '已修改',
        'core-terminal-editor-details.permissions': '权限',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-terminal-editor-details.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
