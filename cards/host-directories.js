/* Core card package: largest directories from the host-wide disk walk. */
window.ZogographHostDirectoriesCard = {
  manifest: Object.freeze({
    id: 'core-host-directories',
    locales: ['core-host-directories.'],
    helpers: [
      'target',
      'simpleTable',
      'note',
      'humanBytes',
      'humanCount',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-directories.summary':
          "From root's walk of the whole disk, so this reaches the places the account cannot: other accounts' homes, /var, /backup, and anything mounted somewhere unexpected.",
        'core-host-directories.directory': 'Directory',
        'core-host-directories.size': 'Size',
        'core-host-directories.files': 'Files',
        'core-host-directories.empty':
          'Nothing recorded. Ask for a walk from the Storage page.',
      }),
      es: Object.freeze({
        'core-host-directories.summary':
          'Procede del recorrido de root por todo el disco, así que alcanza lugares a los que la cuenta no puede llegar: los hogares de otras cuentas, /var, /backup y cualquier cosa montada en un lugar inesperado.',
        'core-host-directories.directory': 'Directorio',
        'core-host-directories.size': 'Tamaño',
        'core-host-directories.files': 'Archivos',
        'core-host-directories.empty':
          'No se registró nada. Solicite un recorrido desde la página Almacenamiento.',
      }),
      hi: Object.freeze({
        'core-host-directories.summary':
          'पूरी डिस्क पर root के स्कैन से, इसलिए यह उन जगहों तक पहुँचता है जहाँ खाता नहीं पहुँच सकता: अन्य खातों के होम, /var, /backup और कहीं अनपेक्षित रूप से माउंट की गई कोई भी चीज़।',
        'core-host-directories.directory': 'डायरेक्टरी',
        'core-host-directories.size': 'आकार',
        'core-host-directories.files': 'फ़ाइलें',
        'core-host-directories.empty':
          'कुछ भी दर्ज नहीं है। Storage पेज से वॉक का अनुरोध करें।',
      }),
      ar: Object.freeze({
        'core-host-directories.summary':
          'من جولة root على كامل القرص، لذا تصل إلى أماكن لا يستطيع الحساب الوصول إليها: مجلدات حسابات أخرى و/var و/backup وأي شيء مركب في مكان غير متوقع.',
        'core-host-directories.directory': 'المجلد',
        'core-host-directories.size': 'الحجم',
        'core-host-directories.files': 'الملفات',
        'core-host-directories.empty':
          'لم يُسجَّل شيء. اطلب جولة من صفحة التخزين.',
      }),
      zh: Object.freeze({
        'core-host-directories.summary':
          '来自 root 对整块磁盘的扫描，因此能到达此账户无法到达的位置：其他账户的主目录、/var、/backup，以及意外挂载的任何内容。',
        'core-host-directories.directory': '目录',
        'core-host-directories.size': '大小',
        'core-host-directories.files': '文件',
        'core-host-directories.empty':
          '没有记录内容。请从存储页面请求一次遍历。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-directories.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-directories.${key}`],
    );
  },
  render({ dirs = [] }, helpers) {
    const { target, simpleTable, note, humanBytes, humanCount, i18n } = helpers;
    if (!target) return;
    const t = (key, fallback) =>
      i18n.t(`core-host-directories.${key}`, {}, fallback);
    if (!dirs.length) {
      target.replaceChildren(
        note(
          t('empty', 'Nothing recorded. Ask for a walk from the Storage page.'),
        ),
      );
      return;
    }
    target.replaceChildren(
      simpleTable(
        ['directory', 'size', 'files'].map((key, index) =>
          t(key, ['Directory', 'Size', 'Files'][index]),
        ),
        dirs.map((directory) => [
          directory.path,
          humanBytes(directory.bytes),
          humanCount(directory.files),
        ]),
        ['name', 'size', 'size'],
      ),
    );
  },
};
