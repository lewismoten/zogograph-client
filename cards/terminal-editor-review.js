/* Core card package: protected review and save flow in the terminal editor. */
window.ZogographTerminalEditorReviewCard = {
  manifest: Object.freeze({
    id: 'core-terminal-editor-review',
    locales: ['core-terminal-editor-review.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-editor-review.review_title':
          'Show a bounded line-by-line preview before saving (Ctrl/Cmd+Shift+D)',
        'core-terminal-editor-review.review_no_changes':
          'No changes since this file was opened.',
        'core-terminal-editor-review.review_dialog_title':
          'Review changes · {{name}}',
        'core-terminal-editor-review.review_help':
          'Removed lines start with − and added lines start with +. This is a review only; saving still uses the protected atomic write.',
        'core-terminal-editor-review.view_dialog_title': 'View {{name}}',
        'core-terminal-editor-review.view_help':
          'This file is shown for review only. Find works locally; no text is sent back to the server.',
        'core-terminal-editor-review.edit_dialog_title': 'Edit {{name}}',
        'core-terminal-editor-review.saved_with_recovery':
          'Saved {{path}} ({{size}}); recovery copy made.',
        'core-terminal-editor-review.saved': 'Saved {{path}} ({{size}}).',
        'core-terminal-editor-review.save': 'Save it',
      }),
      es: Object.freeze({
        'core-terminal-editor-review.review_title':
          'Mostrar una vista previa limitada línea por línea antes de guardar (Ctrl/Cmd+Mayús+D)',
        'core-terminal-editor-review.review_no_changes':
          'No hay cambios desde que se abrió este archivo.',
        'core-terminal-editor-review.review_dialog_title':
          'Revisar cambios · {{name}}',
        'core-terminal-editor-review.review_help':
          'Las líneas eliminadas comienzan con − y las añadidas con +. Esto es solo una revisión; guardar sigue usando la escritura atómica protegida.',
        'core-terminal-editor-review.view_dialog_title': 'Ver {{name}}',
        'core-terminal-editor-review.view_help':
          'Este archivo se muestra solo para revisión. Buscar funciona localmente; no se envía texto de vuelta al servidor.',
        'core-terminal-editor-review.edit_dialog_title': 'Editar {{name}}',
        'core-terminal-editor-review.saved_with_recovery':
          'Se guardó {{path}} ({{size}}); se creó una copia de recuperación.',
        'core-terminal-editor-review.saved': 'Se guardó {{path}} ({{size}}).',
        'core-terminal-editor-review.save': 'Guardarlo',
      }),
      hi: Object.freeze({
        'core-terminal-editor-review.review_title':
          'सहेजने से पहले सीमित पंक्ति-दर-पंक्ति पूर्वावलोकन दिखाएँ (Ctrl/Cmd+Shift+D)',
        'core-terminal-editor-review.review_no_changes':
          'इस फ़ाइल के खुलने के बाद कोई बदलाव नहीं हुआ है।',
        'core-terminal-editor-review.review_dialog_title':
          'बदलावों की समीक्षा · {{name}}',
        'core-terminal-editor-review.review_help':
          'हटाई गई पंक्तियाँ − से और जोड़ी गई पंक्तियाँ + से शुरू होती हैं। यह केवल समीक्षा है; सहेजना अब भी संरक्षित एटॉमिक लेखन का उपयोग करता है।',
        'core-terminal-editor-review.view_dialog_title': '{{name}} देखें',
        'core-terminal-editor-review.view_help':
          'यह फ़ाइल केवल समीक्षा के लिए दिखाई गई है। खोज स्थानीय रूप से काम करती है; कोई पाठ सर्वर को वापस नहीं भेजा जाता।',
        'core-terminal-editor-review.edit_dialog_title':
          '{{name}} संपादित करें',
        'core-terminal-editor-review.saved_with_recovery':
          '{{path}} ({{size}}) सहेजा गया; पुनर्प्राप्ति कॉपी बनाई गई।',
        'core-terminal-editor-review.saved': '{{path}} ({{size}}) सहेजा गया।',
        'core-terminal-editor-review.save': 'इसे सहेजें',
      }),
      ar: Object.freeze({
        'core-terminal-editor-review.review_title':
          'اعرض معاينة محدودة سطرًا بسطر قبل الحفظ (Ctrl/Cmd+Shift+D)',
        'core-terminal-editor-review.review_no_changes':
          'لا تغييرات منذ فتح هذا الملف.',
        'core-terminal-editor-review.review_dialog_title':
          'راجع التغييرات · {{name}}',
        'core-terminal-editor-review.review_help':
          'تبدأ الأسطر المحذوفة بـ − والأسطر المضافة بـ +. هذه مراجعة فقط؛ ما زال الحفظ يستخدم الكتابة الذرية المحمية.',
        'core-terminal-editor-review.view_dialog_title': 'اعرض {{name}}',
        'core-terminal-editor-review.view_help':
          'يُعرض هذا الملف للمراجعة فقط. يعمل البحث محليًا؛ لا يُرسل نص إلى الخادم.',
        'core-terminal-editor-review.edit_dialog_title': 'حرر {{name}}',
        'core-terminal-editor-review.saved_with_recovery':
          'حُفظ {{path}} ({{size}})؛ أُنشئت نسخة استرداد.',
        'core-terminal-editor-review.saved': 'حُفظ {{path}} ({{size}}).',
        'core-terminal-editor-review.save': 'احفظه',
      }),
      zh: Object.freeze({
        'core-terminal-editor-review.review_title':
          '保存前显示受限的逐行预览 (Ctrl/Cmd+Shift+D)',
        'core-terminal-editor-review.review_no_changes':
          '自打开此文件后没有更改。',
        'core-terminal-editor-review.review_dialog_title':
          '审核更改 · {{name}}',
        'core-terminal-editor-review.review_help':
          '删除的行以 − 开头，新增的行以 + 开头。这仅供审核；保存仍使用受保护的原子写入。',
        'core-terminal-editor-review.view_dialog_title': '查看 {{name}}',
        'core-terminal-editor-review.view_help':
          '此文件仅供审核。查找在本地运行；不会将文本发送回服务器。',
        'core-terminal-editor-review.edit_dialog_title': '编辑 {{name}}',
        'core-terminal-editor-review.saved_with_recovery':
          '已保存 {{path}} ({{size}})；已创建恢复副本。',
        'core-terminal-editor-review.saved': '已保存 {{path}} ({{size}}).',
        'core-terminal-editor-review.save': '保存它',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-terminal-editor-review.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
