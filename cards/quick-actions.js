/* Core card package: persistent shortcuts for the actions most often needed in context. */
window.ZogographQuickActionsCard = {
  manifest: Object.freeze({
    id: 'core-quick-actions',
    locales: ['core-quick-actions.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-quick-actions.diagnose': '⌁ Run diagnosis',
        'core-quick-actions.write_note': '✎ Write a note',
        'core-quick-actions.ask_assistant': '✦ Ask assistant',
        'core-quick-actions.open_title': 'Open quick actions',
      }),
      es: Object.freeze({
        'core-quick-actions.diagnose': '⌁ Ejecutar diagnóstico',
        'core-quick-actions.write_note': '✎ Escribir una nota',
        'core-quick-actions.ask_assistant': '✦ Preguntar al asistente',
        'core-quick-actions.open_title': 'Abrir acciones rápidas',
      }),
      hi: Object.freeze({
        'core-quick-actions.diagnose': '⌁ निदान चलाएँ',
        'core-quick-actions.write_note': '✎ नोट लिखें',
        'core-quick-actions.ask_assistant': '✦ सहायक से पूछें',
        'core-quick-actions.open_title': 'त्वरित क्रियाएँ खोलें',
      }),
      ar: Object.freeze({
        'core-quick-actions.diagnose': '⌁ شغّل التشخيص',
        'core-quick-actions.write_note': '✎ اكتب ملاحظة',
        'core-quick-actions.ask_assistant': '✦ اسأل المساعد',
        'core-quick-actions.open_title': 'افتح الإجراءات السريعة',
      }),
      zh: Object.freeze({
        'core-quick-actions.diagnose': '⌁ 运行诊断',
        'core-quick-actions.write_note': '✎ 写一条备注',
        'core-quick-actions.ask_assistant': '✦ 询问助手',
        'core-quick-actions.open_title': '打开快速操作',
      }),
    }),
  }),
  render(_input, helpers) {
    const t = (key, fallback) =>
      helpers.i18n.t(`core-quick-actions.${key}`, {}, fallback);
    return {
      diagnose: t('diagnose', '⌁ Run diagnosis'),
      writeNote: t('write_note', '✎ Write a note'),
      askAssistant: t('ask_assistant', '✦ Ask assistant'),
      openTitle: t('open_title', 'Open quick actions'),
    };
  },
};
