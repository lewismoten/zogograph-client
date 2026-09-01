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
        'core-terminal-editor-find.enter_find': 'Enter text to find.',
        'core-terminal-editor-find.no_matches': 'No matches.',
        'core-terminal-editor-find.wrapped_first':
          'Wrapped to the first match.',
        'core-terminal-editor-find.match_selected': 'Match selected.',
        'core-terminal-editor-find.one_match_replaced': 'One match replaced.',
        'core-terminal-editor-find.matches_replaced':
          '{{count}} matches replaced.',
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
        'core-terminal-editor-find.enter_find':
          'Escriba el texto que desea buscar.',
        'core-terminal-editor-find.no_matches': 'No hay coincidencias.',
        'core-terminal-editor-find.wrapped_first':
          'Se volvió a la primera coincidencia.',
        'core-terminal-editor-find.match_selected':
          'Coincidencia seleccionada.',
        'core-terminal-editor-find.one_match_replaced':
          'Se reemplazó una coincidencia.',
        'core-terminal-editor-find.matches_replaced':
          'Se reemplazaron {{count}} coincidencias.',
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
        'core-terminal-editor-find.enter_find': 'ढूँढने के लिए पाठ लिखें।',
        'core-terminal-editor-find.no_matches': 'कोई मिलान नहीं।',
        'core-terminal-editor-find.wrapped_first': 'पहले मिलान पर वापस आ गया।',
        'core-terminal-editor-find.match_selected': 'मिलान चुना गया।',
        'core-terminal-editor-find.one_match_replaced': 'एक मिलान बदला गया।',
        'core-terminal-editor-find.matches_replaced':
          '{{count}} मिलान बदले गए।',
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
        'core-terminal-editor-find.enter_find': 'أدخل نصًا للبحث عنه.',
        'core-terminal-editor-find.no_matches': 'لا توجد مطابقات.',
        'core-terminal-editor-find.wrapped_first': 'عادت إلى أول مطابقة.',
        'core-terminal-editor-find.match_selected': 'تم تحديد المطابقة.',
        'core-terminal-editor-find.one_match_replaced':
          'استُبدلت مطابقة واحدة.',
        'core-terminal-editor-find.matches_replaced':
          'استُبدلت {{count}} مطابقات.',
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
        'core-terminal-editor-find.enter_find': '请输入要查找的文本。',
        'core-terminal-editor-find.no_matches': '没有匹配项。',
        'core-terminal-editor-find.wrapped_first': '已回到第一个匹配项。',
        'core-terminal-editor-find.match_selected': '已选中匹配项。',
        'core-terminal-editor-find.one_match_replaced': '已替换一个匹配项。',
        'core-terminal-editor-find.matches_replaced':
          '已替换 {{count}} 个匹配项。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-terminal-editor-find.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
