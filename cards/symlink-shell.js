/* Core card package: page shell for symlink inspection and cleanup. */
window.ZogographSymlinkShellCard = {
  manifest: Object.freeze({
    id: 'core-symlink-shell',
    locales: ['core-symlink-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-symlink-shell.title': 'Symlinks that eat things',
        'core-symlink-shell.help':
          'The scanner steps over every symlink it meets, deliberately — following one double-counts at best and walks out of the watched directory at worst. The consequence is that a bad link is invisible to this tool while still being the thing that ruins everything else: a link pointing at one of its own ancestors is an infinitely deep directory to anything that {{does}} follow links, and backups, {{du}}, {{tar}} and web crawlers all do. That is how a disk fills up with one file copied ten thousand times.',
        'core-symlink-shell.check_again': 'Check again',
        'core-symlink-shell.check_symlinks': 'Check the symlinks',
      }),
      es: Object.freeze({
        'core-symlink-shell.title': 'Enlaces simbólicos que devoran cosas',
        'core-symlink-shell.help':
          'El escáner salta deliberadamente cada enlace simbólico que encuentra: seguirlo duplica datos en el mejor caso y sale del directorio vigilado en el peor. La consecuencia es que un enlace malo es invisible para esta herramienta y aun así arruina todo lo demás: un enlace que apunta a uno de sus propios antecesores es un directorio infinitamente profundo para todo lo que {{does}} sigue enlaces, y las copias de seguridad, {{du}}, {{tar}} y los rastreadores web lo hacen. Así se llena un disco con un archivo copiado diez mil veces.',
        'core-symlink-shell.check_again': 'Comprobar de nuevo',
        'core-symlink-shell.check_symlinks': 'Comprobar enlaces simbólicos',
      }),
      hi: Object.freeze({
        'core-symlink-shell.title': 'चीज़ें खा जाने वाले सिमलिंक',
        'core-symlink-shell.help':
          'स्कैनर जानबूझकर हर मिले सिमलिंक के ऊपर से निकल जाता है—उसे फॉलो करने पर अच्छे में डबल-काउंट और बुरे में निगरानी डायरेक्टरी से बाहर निकलना है। परिणाम यह है कि खराब लिंक इस टूल को दिखाई नहीं देता, फिर भी बाकी सब बिगाड़ता है: अपने पूर्वज पर इंगित लिंक उस हर चीज़ के लिए अनंत गहरी डायरेक्टरी है जो लिंक {{does}} फॉलो करती है, और बैकअप, {{du}}, {{tar}} और वेब क्रॉलर सभी करते हैं। इसी तरह एक फ़ाइल दस हजार बार कॉपी होकर डिस्क भरती है।',
        'core-symlink-shell.check_again': 'फिर जाँचें',
        'core-symlink-shell.check_symlinks': 'सिमलिंक जाँचें',
      }),
      ar: Object.freeze({
        'core-symlink-shell.title': 'روابط رمزية تلتهم الأشياء',
        'core-symlink-shell.help':
          'يتخطى الماسح عمدًا كل رابط رمزي يلقاه — فاتباع واحد منه يكرر العد في أحسن الأحوال ويخرج من الدليل المراقب في أسوئها. والنتيجة أن الرابط السيئ لا تراه هذه الأداة مع أنه ما يفسد كل شيء آخر: رابط يشير إلى أحد أسلافه هو دليل عميق بلا نهاية لكل ما {{does}} يتبع الروابط، والنسخ الاحتياطية و{{du}} و{{tar}} وزواحف الويب كلها تفعل ذلك. هكذا يمتلئ قرص بملف واحد نُسخ عشرة آلاف مرة.',
        'core-symlink-shell.check_again': 'تحقق مجددًا',
        'core-symlink-shell.check_symlinks': 'افحص الروابط الرمزية',
      }),
      zh: Object.freeze({
        'core-symlink-shell.title': '会吞噬内容的符号链接',
        'core-symlink-shell.help':
          '扫描器会有意跳过遇到的每个符号链接——跟随链接最好的情况是重复计数，最坏的情况是走出受监视目录。结果是坏链接对此工具不可见，却仍会破坏其他一切：指向自身祖先之一的链接对所有{{does}}跟随链接的工具来说都是无限深的目录，备份、{{du}}、{{tar}} 和网络爬虫都会跟随。一个文件被复制一万次就会这样填满磁盘。',
        'core-symlink-shell.check_again': '再次检查',
        'core-symlink-shell.check_symlinks': '检查符号链接',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-symlink-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-symlink-shell.${key}`],
    );
  },
  render() {},
};
