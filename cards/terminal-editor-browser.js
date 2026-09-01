/* Core card package: safe file browser labels in the terminal editor. */
window.ZogographTerminalEditorBrowserCard = {
  manifest: Object.freeze({
    id: 'core-terminal-editor-browser',
    locales: ['core-terminal-editor-browser.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-editor-browser.parent_directory': '↑ Parent directory',
        'core-terminal-editor-browser.symbolic_link_unfollowed':
          'symbolic link — not followed',
        'core-terminal-editor-browser.directory_writable':
          'directory · writable',
        'core-terminal-editor-browser.directory_read_only':
          'directory · read-only',
        'core-terminal-editor-browser.file_access': '{{size}} · {{access}}',
        'core-terminal-editor-browser.writable': 'writable',
        'core-terminal-editor-browser.read_only': 'read-only',
        'core-terminal-editor-browser.not_regular_file': 'not a regular file',
        'core-terminal-editor-browser.browser_more':
          '{{count}} additional entries not shown. Open a narrower directory to keep browsing responsive.',
        'core-terminal-editor-browser.browser_help':
          'Only direct children are shown. Symbolic links are never followed here, and opening a file repeats its path and access checks.',
      }),
      es: Object.freeze({
        'core-terminal-editor-browser.parent_directory':
          '↑ Directorio superior',
        'core-terminal-editor-browser.symbolic_link_unfollowed':
          'enlace simbólico — no seguido',
        'core-terminal-editor-browser.directory_writable':
          'directorio · escritura permitida',
        'core-terminal-editor-browser.directory_read_only':
          'directorio · solo lectura',
        'core-terminal-editor-browser.file_access': '{{size}} · {{access}}',
        'core-terminal-editor-browser.writable': 'escritura permitida',
        'core-terminal-editor-browser.read_only': 'solo lectura',
        'core-terminal-editor-browser.not_regular_file':
          'no es un archivo normal',
        'core-terminal-editor-browser.browser_more':
          'No se muestran {{count}} entradas adicionales. Abra un directorio más específico para que la exploración siga respondiendo.',
        'core-terminal-editor-browser.browser_help':
          'Solo se muestran los hijos directos. Aquí nunca se siguen enlaces simbólicos y abrir un archivo repite las comprobaciones de ruta y acceso.',
      }),
      hi: Object.freeze({
        'core-terminal-editor-browser.parent_directory': '↑ मूल डायरेक्टरी',
        'core-terminal-editor-browser.symbolic_link_unfollowed':
          'सिम्बॉलिक लिंक — अनुसरण नहीं किया गया',
        'core-terminal-editor-browser.directory_writable':
          'डायरेक्टरी · लिखने योग्य',
        'core-terminal-editor-browser.directory_read_only':
          'डायरेक्टरी · केवल पढ़ने योग्य',
        'core-terminal-editor-browser.file_access': '{{size}} · {{access}}',
        'core-terminal-editor-browser.writable': 'लिखने योग्य',
        'core-terminal-editor-browser.read_only': 'केवल पढ़ने योग्य',
        'core-terminal-editor-browser.not_regular_file': 'नियमित फ़ाइल नहीं है',
        'core-terminal-editor-browser.browser_more':
          '{{count}} अतिरिक्त प्रविष्टियाँ नहीं दिखाई गईं। ब्राउज़िंग को प्रत्युत्तरशील रखने के लिए और संकरी डायरेक्टरी खोलें।',
        'core-terminal-editor-browser.browser_help':
          'केवल सीधे बच्चे दिखाए जाते हैं। यहाँ सिम्बॉलिक लिंक का कभी अनुसरण नहीं होता और फ़ाइल खोलने पर उसके पथ व पहुंच की जांच दोहराई जाती है।',
      }),
      ar: Object.freeze({
        'core-terminal-editor-browser.parent_directory': '↑ المجلد الأب',
        'core-terminal-editor-browser.symbolic_link_unfollowed':
          'رابط رمزي — لم يُتبع',
        'core-terminal-editor-browser.directory_writable':
          'مجلد · قابل للكتابة',
        'core-terminal-editor-browser.directory_read_only':
          'مجلد · للقراءة فقط',
        'core-terminal-editor-browser.file_access': '{{size}} · {{access}}',
        'core-terminal-editor-browser.writable': 'قابل للكتابة',
        'core-terminal-editor-browser.read_only': 'للقراءة فقط',
        'core-terminal-editor-browser.not_regular_file': 'ليس ملفًا عاديًا',
        'core-terminal-editor-browser.browser_more':
          'لم تُعرض {{count}} إدخالات إضافية. افتح مجلدًا أضيق ليبقى التصفح مستجيبًا.',
        'core-terminal-editor-browser.browser_help':
          'تُعرض العناصر الأبناء المباشرة فقط. لا تُتبع الروابط الرمزية هنا أبدًا، وفتح ملف يعيد فحص مساره ووصوله.',
      }),
      zh: Object.freeze({
        'core-terminal-editor-browser.parent_directory': '↑ 上级目录',
        'core-terminal-editor-browser.symbolic_link_unfollowed':
          '符号链接 — 未跟随',
        'core-terminal-editor-browser.directory_writable': '目录 · 可写',
        'core-terminal-editor-browser.directory_read_only': '目录 · 只读',
        'core-terminal-editor-browser.file_access': '{{size}} · {{access}}',
        'core-terminal-editor-browser.writable': '可写',
        'core-terminal-editor-browser.read_only': '只读',
        'core-terminal-editor-browser.not_regular_file': '不是常规文件',
        'core-terminal-editor-browser.browser_more':
          '未显示 {{count}} 个额外条目。请打开更窄的目录，以保持浏览响应迅速。',
        'core-terminal-editor-browser.browser_help':
          '仅显示直接子项。这里绝不会跟随符号链接，打开文件会再次进行路径和访问检查。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-terminal-editor-browser.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
