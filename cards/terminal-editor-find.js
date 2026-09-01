/* Core card package: find and replace controls in the terminal editor. */
window.ZogographTerminalEditorFindCard = {
  manifest: Object.freeze({
    id: 'core-terminal-editor-find',
    locales: ['core-terminal-editor-find.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-editor-find.contents_of': 'Contents of {{path}}',
        'core-terminal-editor-find.find': 'Find',
        'core-terminal-editor-find.find_text': 'Find text in file',
        'core-terminal-editor-find.replace_with': 'Replace with',
        'core-terminal-editor-find.replacement_text': 'Replacement text',
        'core-terminal-editor-find.find_next': 'Find next',
        'core-terminal-editor-find.replace': 'Replace',
        'core-terminal-editor-find.replace_all': 'Replace all',
      }),
      es: Object.freeze({
        'core-terminal-editor-find.contents_of': 'Contenido de {{path}}',
        'core-terminal-editor-find.find': 'Buscar',
        'core-terminal-editor-find.find_text': 'Buscar texto en el archivo',
        'core-terminal-editor-find.replace_with': 'Reemplazar con',
        'core-terminal-editor-find.replacement_text': 'Texto de reemplazo',
        'core-terminal-editor-find.find_next': 'Buscar siguiente',
        'core-terminal-editor-find.replace': 'Reemplazar',
        'core-terminal-editor-find.replace_all': 'Reemplazar todo',
      }),
      hi: Object.freeze({
        'core-terminal-editor-find.contents_of': '{{path}} की सामग्री',
        'core-terminal-editor-find.find': 'ढूँढें',
        'core-terminal-editor-find.find_text': 'फ़ाइल में पाठ ढूँढें',
        'core-terminal-editor-find.replace_with': 'इसके बदले रखें',
        'core-terminal-editor-find.replacement_text': 'बदलने वाला पाठ',
        'core-terminal-editor-find.find_next': 'अगला ढूँढें',
        'core-terminal-editor-find.replace': 'बदलें',
        'core-terminal-editor-find.replace_all': 'सभी बदलें',
      }),
      ar: Object.freeze({
        'core-terminal-editor-find.contents_of': 'محتويات {{path}}',
        'core-terminal-editor-find.find': 'ابحث',
        'core-terminal-editor-find.find_text': 'ابحث عن نص في الملف',
        'core-terminal-editor-find.replace_with': 'استبدل بـ',
        'core-terminal-editor-find.replacement_text': 'نص الاستبدال',
        'core-terminal-editor-find.find_next': 'ابحث عن التالي',
        'core-terminal-editor-find.replace': 'استبدل',
        'core-terminal-editor-find.replace_all': 'استبدل الكل',
      }),
      zh: Object.freeze({
        'core-terminal-editor-find.contents_of': '{{path}} 的内容',
        'core-terminal-editor-find.find': '查找',
        'core-terminal-editor-find.find_text': '在文件中查找文本',
        'core-terminal-editor-find.replace_with': '替换为',
        'core-terminal-editor-find.replacement_text': '替换文本',
        'core-terminal-editor-find.find_next': '查找下一个',
        'core-terminal-editor-find.replace': '替换',
        'core-terminal-editor-find.replace_all': '全部替换',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-terminal-editor-find.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
