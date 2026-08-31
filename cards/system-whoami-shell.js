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
        'core-system-whoami-shell.private_address_help':
          'A private address — you are on the same network as this machine, or behind a proxy that is.',
        'core-system-whoami-shell.browser_says': 'Browser says',
        'core-system-whoami-shell.you': 'You',
        'core-system-whoami-shell.unlabelled_address_help':
          'This address has no name yet. Naming it means it is recognised wherever it appears — in captures, in the connection list, in the ports table.',
        'core-system-whoami-shell.proxy_claims_title':
          'What the proxy chain claims',
        'core-system-whoami-shell.proxy_claims_help':
          'These are set by whatever spoke to the web server last. Behind a proxy they carry the real address; sent straight to the server they can say anything at all. The address above is the only one this process observed.',
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
        'core-system-whoami-shell.private_address_help':
          'Una dirección privada: está en la misma red que esta máquina o detrás de un proxy que sí lo está.',
        'core-system-whoami-shell.browser_says': 'El navegador indica',
        'core-system-whoami-shell.you': 'Usted',
        'core-system-whoami-shell.unlabelled_address_help':
          'Esta dirección aún no tiene nombre. Al nombrarla se reconocerá donde aparezca: en capturas, en la lista de conexiones y en la tabla de puertos.',
        'core-system-whoami-shell.proxy_claims_title':
          'Lo que afirma la cadena de proxy',
        'core-system-whoami-shell.proxy_claims_help':
          'Los establece quien haya hablado por última vez con el servidor web. Detrás de un proxy contienen la dirección real; enviados directamente al servidor pueden decir cualquier cosa. La dirección de arriba es la única que observó este proceso.',
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
        'core-system-whoami-shell.private_address_help':
          'निजी पता — आप इस मशीन के समान नेटवर्क पर हैं या ऐसे प्रॉक्सी के पीछे हैं।',
        'core-system-whoami-shell.browser_says': 'ब्राउज़र बताता है',
        'core-system-whoami-shell.you': 'आप',
        'core-system-whoami-shell.unlabelled_address_help':
          'इस पते का अभी कोई नाम नहीं है। नाम देने पर यह जहाँ भी दिखेगा, पहचाना जाएगा — कैप्चर, कनेक्शन सूची और पोर्ट तालिका में।',
        'core-system-whoami-shell.proxy_claims_title':
          'प्रॉक्सी श्रृंखला क्या दावा करती है',
        'core-system-whoami-shell.proxy_claims_help':
          'इन्हें उस चीज़ ने सेट किया है जिसने वेब सर्वर से आखिरी बार बात की थी। प्रॉक्सी के पीछे इनमें वास्तविक पता होता है; सीधे सर्वर पर भेजने पर ये कुछ भी कह सकते हैं। ऊपर का पता ही एकमात्र पता है जिसे इस प्रक्रिया ने देखा।',
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
        'core-system-whoami-shell.private_address_help':
          'عنوان خاص — أنت على نفس الشبكة التي توجد عليها هذه الآلة، أو خلف وكيل موجود عليها.',
        'core-system-whoami-shell.browser_says': 'يقول المتصفح',
        'core-system-whoami-shell.you': 'أنت',
        'core-system-whoami-shell.unlabelled_address_help':
          'ليس لهذا العنوان اسم بعد. تعني تسميته أنه يُعرَف أينما ظهر — في اللقطات وقائمة الاتصالات وجدول المنافذ.',
        'core-system-whoami-shell.proxy_claims_title': 'ما تدّعيه سلسلة الوكيل',
        'core-system-whoami-shell.proxy_claims_help':
          'يضبطها آخر ما تواصل مع خادم الويب. خلف وكيل تحمل العنوان الحقيقي؛ أما عند إرسالها مباشرةً إلى الخادم فيمكنها قول أي شيء. العنوان أعلاه هو الوحيد الذي لاحظته هذه العملية.',
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
        'core-system-whoami-shell.private_address_help':
          '这是一个私有地址——您与此机器位于同一网络，或位于这样的代理之后。',
        'core-system-whoami-shell.browser_says': '浏览器显示',
        'core-system-whoami-shell.you': '您',
        'core-system-whoami-shell.unlabelled_address_help':
          '此地址尚未命名。命名后，无论它出现在哪里——捕获记录、连接列表或端口表——都能被识别。',
        'core-system-whoami-shell.proxy_claims_title': '代理链的声明',
        'core-system-whoami-shell.proxy_claims_help':
          '它们由最后与 Web 服务器通信的对象设置。在代理后面，它们携带真实地址；直接发送到服务器时，它们可以声称任何内容。上方地址是此进程唯一观察到的地址。',
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
