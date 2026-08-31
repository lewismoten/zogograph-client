/* Core card package: static shell copy for the runtime-identity view. */
window.ZogographSystemWhoamiShellCard = {
  manifest: Object.freeze({
    id: 'core-system-whoami-shell',
    locales: ['core-system-whoami-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-whoami-shell.title': 'Who am I?',
        'core-system-whoami-shell.sign_in': 'Sign in to see who you are.',
        'core-system-whoami-shell.your_address': 'Your address',
        'core-system-whoami-shell.not_reported': 'not reported',
        'core-system-whoami-shell.your_note': 'Your note',
        'core-system-whoami-shell.resolves_to': 'Resolves to',
        'core-system-whoami-shell.connection': 'Connection',
        'core-system-whoami-shell.encrypted': 'Encrypted',
        'core-system-whoami-shell.help':
          'Two identities that get confused constantly: the browser at the other end, and the account this process runs as. The second is the one that answers permission questions — ‘this account cannot read that’ means nothing until you know which account it is.',
      }),
      es: Object.freeze({
        'core-system-whoami-shell.title': '¿Quién soy?',
        'core-system-whoami-shell.sign_in': 'Inicie sesión para ver quién es.',
        'core-system-whoami-shell.your_address': 'Su dirección',
        'core-system-whoami-shell.not_reported': 'sin informar',
        'core-system-whoami-shell.your_note': 'Su nota',
        'core-system-whoami-shell.resolves_to': 'Resuelve a',
        'core-system-whoami-shell.connection': 'Conexión',
        'core-system-whoami-shell.encrypted': 'Cifrada',
        'core-system-whoami-shell.help':
          'Hay dos identidades que se confunden constantemente: el navegador del otro lado y la cuenta con la que se ejecuta este proceso. La segunda responde las preguntas de permisos: «esta cuenta no puede leer eso» no significa nada hasta saber de qué cuenta se trata.',
      }),
      hi: Object.freeze({
        'core-system-whoami-shell.title': 'मैं कौन हूँ?',
        'core-system-whoami-shell.sign_in':
          'यह जानने के लिए साइन इन करें कि आप कौन हैं।',
        'core-system-whoami-shell.your_address': 'आपका पता',
        'core-system-whoami-shell.not_reported': 'रिपोर्ट नहीं किया गया',
        'core-system-whoami-shell.your_note': 'आपका नोट',
        'core-system-whoami-shell.resolves_to': 'इस पर हल होता है',
        'core-system-whoami-shell.connection': 'कनेक्शन',
        'core-system-whoami-shell.encrypted': 'एन्क्रिप्टेड',
        'core-system-whoami-shell.help':
          'दो पहचानें लगातार भ्रमित होती हैं: दूसरे छोर का ब्राउज़र और वह खाता जिससे यह प्रक्रिया चलती है। दूसरी ही अनुमति के प्रश्नों का उत्तर देती है—‘यह खाता उसे पढ़ नहीं सकता’ का कोई मतलब नहीं, जब तक आपको पता न हो कि वह कौन सा खाता है।',
      }),
      ar: Object.freeze({
        'core-system-whoami-shell.title': 'من أنا؟',
        'core-system-whoami-shell.sign_in': 'سجّل الدخول لمعرفة هويتك.',
        'core-system-whoami-shell.your_address': 'عنوانك',
        'core-system-whoami-shell.not_reported': 'غير مُبلّغ عنه',
        'core-system-whoami-shell.your_note': 'ملاحظتك',
        'core-system-whoami-shell.resolves_to': 'يُحل إلى',
        'core-system-whoami-shell.connection': 'الاتصال',
        'core-system-whoami-shell.encrypted': 'مشفّر',
        'core-system-whoami-shell.help':
          'تختلط هويتان باستمرار: المتصفح في الطرف الآخر، والحساب الذي تعمل به هذه العملية. الثانية هي التي تجيب عن أسئلة الأذونات — «لا يستطيع هذا الحساب قراءة ذلك» لا تعني شيئًا حتى تعرف أي حساب هو.',
      }),
      zh: Object.freeze({
        'core-system-whoami-shell.title': '我是谁？',
        'core-system-whoami-shell.sign_in': '登录以查看您的身份。',
        'core-system-whoami-shell.your_address': '您的地址',
        'core-system-whoami-shell.not_reported': '未报告',
        'core-system-whoami-shell.your_note': '您的备注',
        'core-system-whoami-shell.resolves_to': '解析为',
        'core-system-whoami-shell.connection': '连接',
        'core-system-whoami-shell.encrypted': '已加密',
        'core-system-whoami-shell.help':
          '有两种身份总是容易混淆：另一端的浏览器，以及运行此进程的账户。后者才是权限问题的答案——“此账户无法读取那个内容”在您知道它是哪个账户之前毫无意义。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-whoami-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-whoami-shell.${key}`],
    );
  },
  render() {},
};
