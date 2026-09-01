/* Core card package: terminal editor recovery and syntax-preview controls. */
window.ZogographTerminalEditorRecoveryCard = {
  manifest: Object.freeze({
    id: 'core-terminal-editor-recovery',
    locales: ['core-terminal-editor-recovery.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-editor-recovery.recovery_restore_selected':
          'Restore selected recovery copy',
        'core-terminal-editor-recovery.syntax_preview': 'Syntax preview',
        'core-terminal-editor-recovery.recovery_restore_title':
          'Restore this recovery copy?',
        'core-terminal-editor-recovery.recovery_restore_consequence':
          'Your current file is copied privately before the restore. Any unsaved text in this dialog will be discarded.',
        'core-terminal-editor-recovery.recovery_restore_action': 'Restore copy',
        'core-terminal-editor-recovery.recovery_restored':
          'Restored {{path}}; the prior version was copied for recovery.',
        'core-terminal-editor-recovery.recovery_label': 'Recovery copy',
        'core-terminal-editor-recovery.recovery_help':
          'Private pre-save copy. Restoring creates a new copy of the current version.',
        'core-terminal-editor-recovery.syntax_preview_title':
          'Read the current {{syntax}} text with local syntax colors',
        'core-terminal-editor-recovery.syntax_preview_dialog_title':
          'Syntax preview · {{name}}',
        'core-terminal-editor-recovery.syntax_preview_help':
          'This local, read-only preview never executes or sends file contents. The native editor remains the place to edit and save.',
      }),
      es: Object.freeze({
        'core-terminal-editor-recovery.recovery_restore_selected':
          'Restaurar la copia de recuperación seleccionada',
        'core-terminal-editor-recovery.syntax_preview':
          'Vista previa de sintaxis',
        'core-terminal-editor-recovery.recovery_restore_title':
          '¿Restaurar esta copia de recuperación?',
        'core-terminal-editor-recovery.recovery_restore_consequence':
          'Su archivo actual se copia de forma privada antes de restaurarlo. Se descartará cualquier texto sin guardar de este diálogo.',
        'core-terminal-editor-recovery.recovery_restore_action':
          'Restaurar copia',
        'core-terminal-editor-recovery.recovery_restored':
          'Se restauró {{path}}; la versión anterior se copió para su recuperación.',
        'core-terminal-editor-recovery.recovery_label': 'Copia de recuperación',
        'core-terminal-editor-recovery.recovery_help':
          'Copia privada previa al guardado. Restaurarla crea una nueva copia de la versión actual.',
        'core-terminal-editor-recovery.syntax_preview_title':
          'Leer el texto {{syntax}} actual con colores de sintaxis locales',
        'core-terminal-editor-recovery.syntax_preview_dialog_title':
          'Vista previa de sintaxis · {{name}}',
        'core-terminal-editor-recovery.syntax_preview_help':
          'Esta vista previa local de solo lectura nunca ejecuta ni envía contenido de archivos. El editor nativo sigue siendo el lugar para editar y guardar.',
      }),
      hi: Object.freeze({
        'core-terminal-editor-recovery.recovery_restore_selected':
          'चुनी हुई पुनर्प्राप्ति कॉपी बहाल करें',
        'core-terminal-editor-recovery.syntax_preview': 'सिंटैक्स पूर्वावलोकन',
        'core-terminal-editor-recovery.recovery_restore_title':
          'इस पुनर्प्राप्ति कॉपी को बहाल करें?',
        'core-terminal-editor-recovery.recovery_restore_consequence':
          'बहाली से पहले आपकी वर्तमान फ़ाइल की निजी कॉपी बनाई जाती है। इस संवाद में मौजूद बिना सहेजा गया पाठ हटा दिया जाएगा।',
        'core-terminal-editor-recovery.recovery_restore_action':
          'कॉपी बहाल करें',
        'core-terminal-editor-recovery.recovery_restored':
          '{{path}} बहाल किया गया; पिछले संस्करण की पुनर्प्राप्ति कॉपी बनाई गई।',
        'core-terminal-editor-recovery.recovery_label': 'पुनर्प्राप्ति कॉपी',
        'core-terminal-editor-recovery.recovery_help':
          'सहेजने से पहले की निजी कॉपी। बहाल करने पर वर्तमान संस्करण की नई कॉपी बनती है।',
        'core-terminal-editor-recovery.syntax_preview_title':
          'स्थानीय सिंटैक्स रंगों के साथ वर्तमान {{syntax}} पाठ पढ़ें',
        'core-terminal-editor-recovery.syntax_preview_dialog_title':
          'सिंटैक्स पूर्वावलोकन · {{name}}',
        'core-terminal-editor-recovery.syntax_preview_help':
          'यह स्थानीय, केवल-पढ़ने योग्य पूर्वावलोकन कभी फ़ाइल सामग्री निष्पादित या भेजता नहीं है। संपादन और सहेजने के लिए मूल संपादक ही स्थान है।',
      }),
      ar: Object.freeze({
        'core-terminal-editor-recovery.recovery_restore_selected':
          'استعد نسخة الاسترداد المختارة',
        'core-terminal-editor-recovery.syntax_preview': 'معاينة الصياغة',
        'core-terminal-editor-recovery.recovery_restore_title':
          'هل تستعيد نسخة الاسترداد هذه؟',
        'core-terminal-editor-recovery.recovery_restore_consequence':
          'تُنسخ ملفك الحالي نسخًا خاصًا قبل الاستعادة. سيُهمل أي نص غير محفوظ في هذا الحوار.',
        'core-terminal-editor-recovery.recovery_restore_action': 'استعد النسخة',
        'core-terminal-editor-recovery.recovery_restored':
          'استُعيد {{path}}؛ نُسخت النسخة السابقة للاسترداد.',
        'core-terminal-editor-recovery.recovery_label': 'نسخة استرداد',
        'core-terminal-editor-recovery.recovery_help':
          'نسخة خاصة قبل الحفظ. تنشئ الاستعادة نسخة جديدة من الإصدار الحالي.',
        'core-terminal-editor-recovery.syntax_preview_title':
          'اقرأ نص {{syntax}} الحالي بألوان صياغة محلية',
        'core-terminal-editor-recovery.syntax_preview_dialog_title':
          'معاينة الصياغة · {{name}}',
        'core-terminal-editor-recovery.syntax_preview_help':
          'هذه المعاينة المحلية للقراءة فقط لا تنفذ محتويات الملفات ولا ترسلها أبدًا. يبقى المحرر الأصلي مكان التحرير والحفظ.',
      }),
      zh: Object.freeze({
        'core-terminal-editor-recovery.recovery_restore_selected':
          '恢复选定的恢复副本',
        'core-terminal-editor-recovery.syntax_preview': '语法预览',
        'core-terminal-editor-recovery.recovery_restore_title':
          '恢复此恢复副本？',
        'core-terminal-editor-recovery.recovery_restore_consequence':
          '恢复前会私下复制当前文件。此对话框中所有未保存的文本都将被丢弃。',
        'core-terminal-editor-recovery.recovery_restore_action': '恢复副本',
        'core-terminal-editor-recovery.recovery_restored':
          '已恢复 {{path}}；此前版本已复制以供恢复。',
        'core-terminal-editor-recovery.recovery_label': '恢复副本',
        'core-terminal-editor-recovery.recovery_help':
          '保存前的私有副本。恢复会创建当前版本的新副本。',
        'core-terminal-editor-recovery.syntax_preview_title':
          '使用本地语法颜色查看当前 {{syntax}} 文本',
        'core-terminal-editor-recovery.syntax_preview_dialog_title':
          '语法预览 · {{name}}',
        'core-terminal-editor-recovery.syntax_preview_help':
          '此本地只读预览绝不会执行或发送文件内容。原生编辑器仍是编辑和保存的位置。',
      }),
    }),
  }),

  render({ key, values = {} }, { i18n }) {
    const id = `core-terminal-editor-recovery.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
