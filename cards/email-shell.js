/* Core card package: static controls and delivery guidance for email alerts. */
window.ZogographEmailShellCard = {
  manifest: Object.freeze({
    id: 'core-email-shell',
    locales: ['core-email-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-email-shell.title': 'Email alerts',
        'core-email-shell.dashboard_address': "This dashboard's address",
        'core-email-shell.dashboard_address_placeholder':
          'https://stats.example.com',
        'core-email-shell.dashboard_address_help':
          'So the messages can link to the page that answers the question. The cron that sends them has no request to work this out from, so it has to be written down — otherwise a message says what is wrong and leaves you to find the page at two in the morning.',
        'core-email-shell.send_from': 'Send from',
        'core-email-shell.send_from_placeholder': 'monitor@example.com',
        'core-email-shell.send_from_help':
          'Optional. Some hosts reject mail with no From header; others rewrite it whatever you put here.',
        'core-email-shell.send_to': 'Send to',
        'core-email-shell.send_to_placeholder':
          'you@example.com; oncall@example.com',
        'core-email-shell.send_to_help':
          'Several addresses, separated by semicolons. Each gets its own message rather than one with everybody in the To — a single send fails as a unit, so one bad mailbox would mean nobody hears.',
        'core-email-shell.enabled': 'Send alerts by email',
        'core-email-shell.enabled_help':
          'Nothing is sent while this is off, however bad things get.',
        'core-email-shell.where_to_send': 'Where to send',
        'core-email-shell.how_loud': 'How loud, and how often',
        'core-email-shell.what_to_send': 'What to send about, and when',
        'core-email-shell.test_email': 'Send a test email',
        'core-email-shell.test_telegram': 'Send a test Telegram',
        'core-email-shell.settings_sign_in':
          'Sign in to change the email settings.',
        'core-email-shell.settings_saved':
          'Saved. New settings apply from the next alerting run.',
        'core-email-shell.test_sent':
          "Test email sent to {{recipient}}. If it does not arrive, check the spam folder and the host's mail log.",
        'core-email-shell.could_not_send': 'Could not send.',
        'core-email-shell.help':
          'Mail goes out through PHP’s {{mail}}, which on a cPanel host means the server’s own mail queue. One message per alerting run rather than one per rule — a disk filling up trips several rules at once, and five separate emails about the same event is how people learn to ignore them. The message takes the volume of its loudest line, lists them loudest first, links each one to the page that answers it, and ends with the commands to diagnose the same thing from a shell — because the most likely moment for this dashboard to be unreachable is the moment the disk is full, which is exactly when its email arrives.',
      }),
      es: Object.freeze({
        'core-email-shell.title': 'Alertas por correo',
        'core-email-shell.dashboard_address': 'Dirección de este panel',
        'core-email-shell.dashboard_address_placeholder':
          'https://stats.ejemplo.com',
        'core-email-shell.dashboard_address_help':
          'Así los mensajes pueden enlazar a la página que responde la pregunta. El cron que los envía no puede deducirla, por lo que debe anotarse; de otro modo un aviso dice qué está mal y le deja buscar la página a las dos de la madrugada.',
        'core-email-shell.send_from': 'Enviar desde',
        'core-email-shell.send_from_placeholder': 'monitor@ejemplo.com',
        'core-email-shell.send_from_help':
          'Opcional. Algunos alojamientos rechazan correo sin cabecera From; otros la reescriben sin importar lo que ponga aquí.',
        'core-email-shell.send_to': 'Enviar a',
        'core-email-shell.send_to_placeholder':
          'usted@ejemplo.com; guardia@ejemplo.com',
        'core-email-shell.send_to_help':
          'Varias direcciones separadas por punto y coma. Cada una recibe su propio mensaje; un envío con todas falla como unidad, así que un buzón malo impediría que todos reciban avisos.',
        'core-email-shell.enabled': 'Enviar alertas por correo',
        'core-email-shell.enabled_help':
          'No se envía nada mientras esté desactivado, por graves que sean los problemas.',
        'core-email-shell.where_to_send': 'Dónde enviar',
        'core-email-shell.how_loud': 'Con qué intensidad y frecuencia',
        'core-email-shell.what_to_send': 'Qué enviar y cuándo',
        'core-email-shell.test_email': 'Enviar correo de prueba',
        'core-email-shell.test_telegram': 'Enviar Telegram de prueba',
        'core-email-shell.settings_sign_in':
          'Inicie sesión para cambiar la configuración de correo.',
        'core-email-shell.settings_saved':
          'Guardado. La nueva configuración se aplicará a partir de la siguiente ejecución de alertas.',
        'core-email-shell.test_sent':
          'Se envió un correo de prueba a {{recipient}}. Si no llega, revise la carpeta de spam y el registro de correo del servidor.',
        'core-email-shell.could_not_send': 'No se pudo enviar.',
        'core-email-shell.help':
          'El correo sale mediante {{mail}} de PHP, que en un host cPanel significa la propia cola de correo del servidor. Se envía un mensaje por ejecución de alertas, no uno por regla: un disco que se llena activa varias reglas a la vez, y cinco correos separados sobre el mismo evento es como la gente aprende a ignorarlos. El mensaje adopta el volumen de su línea más fuerte, las enumera de mayor a menor, enlaza cada una a la página que la responde y termina con los comandos para diagnosticar lo mismo desde una shell, porque el momento más probable en que este panel no esté disponible es cuando el disco está lleno, justo cuando llega su correo.',
      }),
      hi: Object.freeze({
        'core-email-shell.title': 'ईमेल अलर्ट',
        'core-email-shell.dashboard_address': 'इस डैशबोर्ड का पता',
        'core-email-shell.dashboard_address_placeholder':
          'https://stats.example.com',
        'core-email-shell.dashboard_address_help':
          'ताकि संदेश उस पृष्ठ से जुड़ सकें जो प्रश्न का उत्तर देता है। उन्हें भेजने वाले क्रॉन के पास इसे निकालने का अनुरोध नहीं है, इसलिए इसे लिखना पड़ता है; अन्यथा संदेश बताता है कि क्या गलत है और आपको रात दो बजे पृष्ठ ढूँढने देता है।',
        'core-email-shell.send_from': 'से भेजें',
        'core-email-shell.send_from_placeholder': 'monitor@example.com',
        'core-email-shell.send_from_help':
          'वैकल्पिक। कुछ होस्ट From हेडर के बिना मेल अस्वीकार करते हैं; दूसरे आप यहाँ जो भी डालें उसे फिर से लिखते हैं।',
        'core-email-shell.send_to': 'को भेजें',
        'core-email-shell.send_to_placeholder':
          'you@example.com; oncall@example.com',
        'core-email-shell.send_to_help':
          'सेमीकोलन से अलग कई पते। हर एक को अपना संदेश मिलता है; एक भेजना एक इकाई के रूप में विफल होता है, इसलिए एक खराब मेलबॉक्स का अर्थ होगा कि किसी को नहीं सुनाई देगा।',
        'core-email-shell.enabled': 'ईमेल से चेतावनी भेजें',
        'core-email-shell.enabled_help':
          'यह बंद रहने पर, समस्याएँ कितनी भी खराब हों, कुछ नहीं भेजा जाता।',
        'core-email-shell.where_to_send': 'कहाँ भेजें',
        'core-email-shell.how_loud': 'कितनी चेतावनी और कितनी बार',
        'core-email-shell.what_to_send': 'क्या और कब भेजें',
        'core-email-shell.test_email': 'परीक्षण ईमेल भेजें',
        'core-email-shell.test_telegram': 'परीक्षण Telegram भेजें',
        'core-email-shell.settings_sign_in':
          'ईमेल सेटिंग बदलने के लिए साइन इन करें।',
        'core-email-shell.settings_saved':
          'सहेजा गया। नई सेटिंग अगले अलर्टिंग रन से लागू होंगी।',
        'core-email-shell.test_sent':
          '{{recipient}} को परीक्षण ईमेल भेज दिया गया है। यदि वह नहीं पहुँचता, तो स्पैम फ़ोल्डर और होस्ट के मेल लॉग की जाँच करें।',
        'core-email-shell.could_not_send': 'भेजा नहीं जा सका।',
        'core-email-shell.help':
          'मेल PHP के {{mail}} से जाता है, जिसका cPanel होस्ट पर अर्थ सर्वर की अपनी मेल कतार है। हर नियम के बजाय हर अलर्टिंग रन पर एक संदेश—भरती डिस्क एक साथ कई नियम ट्रिगर करती है और उसी घटना के पाँच अलग ईमेल लोगों को उन्हें अनदेखा करना सिखाते हैं। संदेश अपनी सबसे तेज़ पंक्ति का स्तर लेता है, उन्हें तेज़ से धीमा सूचीबद्ध करता है, हर एक को उसे समझाने वाले पृष्ठ से जोड़ता है और शेल से वही बात जाँचने के कमांड पर समाप्त होता है—क्योंकि इस डैशबोर्ड के उपलब्ध न होने का सबसे संभावित समय डिस्क भरने का समय है, और ठीक उसी समय उसका ईमेल आता है।',
      }),
      ar: Object.freeze({
        'core-email-shell.title': 'تنبيهات البريد',
        'core-email-shell.dashboard_address': 'عنوان هذه اللوحة',
        'core-email-shell.dashboard_address_placeholder':
          'https://stats.example.com',
        'core-email-shell.dashboard_address_help':
          'كي ترتبط الرسائل بالصفحة التي تجيب عن السؤال. لا يملك cron الذي يرسلها طلبًا ليستنتج ذلك منه، لذا يجب تدوينه؛ وإلا قالت الرسالة ما الخطأ وتركتك تبحث عن الصفحة في الثانية صباحًا.',
        'core-email-shell.send_from': 'أرسل من',
        'core-email-shell.send_from_placeholder': 'monitor@example.com',
        'core-email-shell.send_from_help':
          'اختياري. ترفض بعض المضيفات البريد بلا ترويسة From؛ وتعاد كتابة الترويسة في مضيفات أخرى مهما وضعت هنا.',
        'core-email-shell.send_to': 'أرسل إلى',
        'core-email-shell.send_to_placeholder':
          'you@example.com; oncall@example.com',
        'core-email-shell.send_to_help':
          'عدة عناوين تفصلها فاصلة منقوطة. يتلقى كل عنوان رسالته الخاصة؛ يفشل الإرسال الواحد كوحدة، لذا سيعني صندوق بريد سيئ عدم تلقي أحد للرسالة.',
        'core-email-shell.enabled': 'أرسل التنبيهات بالبريد',
        'core-email-shell.enabled_help':
          'لا يُرسل شيء أثناء إيقاف هذا الخيار مهما ساءت الأمور.',
        'core-email-shell.where_to_send': 'إلى أين يُرسل',
        'core-email-shell.how_loud': 'مدى الإلحاح والتكرار',
        'core-email-shell.what_to_send': 'ما الذي يرسل ومتى',
        'core-email-shell.test_email': 'أرسل رسالة اختبار',
        'core-email-shell.test_telegram': 'أرسل Telegram اختباريًا',
        'core-email-shell.settings_sign_in':
          'سجّل الدخول لتغيير إعدادات البريد الإلكتروني.',
        'core-email-shell.settings_saved':
          'حُفظت. ستُطبّق الإعدادات الجديدة بدءًا من تشغيل التنبيهات التالي.',
        'core-email-shell.test_sent':
          'أُرسل بريد اختبار إلى {{recipient}}. إن لم يصل، فتحقق من مجلد البريد المزعج وسجل بريد المضيف.',
        'core-email-shell.could_not_send': 'تعذّر الإرسال.',
        'core-email-shell.help':
          'يخرج البريد عبر {{mail}} الخاص بـ PHP، وهو في مضيف cPanel يعني طابور بريد الخادم نفسه. رسالة واحدة لكل تشغيل تنبيهات لا لكل قاعدة — فالقرص الممتلئ يطلق عدة قواعد دفعة واحدة، وخمس رسائل منفصلة عن الحدث نفسه هي كيف يتعلم الناس تجاهلها. تأخذ الرسالة شدة أعلى سطر فيها، وتسردها من الأعلى شدة، وتربط كل واحد بالصفحة التي تجيب عنه، وتنتهي بأوامر تشخيص الأمر نفسه من shell — لأن أرجح لحظة يتعذر فيها الوصول إلى لوحة المعلومات هذه هي لحظة امتلاء القرص، وهي اللحظة نفسها التي يصل فيها بريدها.',
      }),
      zh: Object.freeze({
        'core-email-shell.title': '电子邮件警报',
        'core-email-shell.dashboard_address': '此仪表板的地址',
        'core-email-shell.dashboard_address_placeholder':
          'https://stats.example.com',
        'core-email-shell.dashboard_address_help':
          '让邮件能够链接到回答问题的页面。发送邮件的 cron 无法据此推断地址，因此必须写下来；否则邮件只会说明问题，却让您在凌晨两点寻找页面。',
        'core-email-shell.send_from': '发件人',
        'core-email-shell.send_from_placeholder': 'monitor@example.com',
        'core-email-shell.send_from_help':
          '可选。有些主机会拒绝没有 From 标头的邮件；另一些则会重写您在此填写的任何内容。',
        'core-email-shell.send_to': '发送给',
        'core-email-shell.send_to_placeholder':
          'you@example.com; oncall@example.com',
        'core-email-shell.send_to_help':
          '多个地址以分号分隔。每个地址都会收到自己的邮件；一次发送会作为整体失败，因此一个坏邮箱会导致所有人都收不到通知。',
        'core-email-shell.enabled': '通过电子邮件发送警报',
        'core-email-shell.enabled_help':
          '关闭此选项时，无论问题多么严重都不会发送任何内容。',
        'core-email-shell.where_to_send': '发送到哪里',
        'core-email-shell.how_loud': '提醒强度和频率',
        'core-email-shell.what_to_send': '发送什么及何时发送',
        'core-email-shell.test_email': '发送测试邮件',
        'core-email-shell.test_telegram': '发送测试 Telegram',
        'core-email-shell.settings_sign_in': '请登录以更改电子邮件设置。',
        'core-email-shell.settings_saved':
          '已保存。新设置将从下一次告警运行开始生效。',
        'core-email-shell.test_sent':
          '测试邮件已发送至 {{recipient}}。如果没有收到，请检查垃圾邮件文件夹和主机的邮件日志。',
        'core-email-shell.could_not_send': '无法发送。',
        'core-email-shell.help':
          '邮件通过 PHP 的 {{mail}} 发出；在 cPanel 主机上，这意味着服务器自身的邮件队列。每次警报运行只发一封邮件，而不是每条规则一封——磁盘填满会同时触发多条规则，同一事件的五封独立邮件只会让人学会忽略它们。邮件采用最严重一行的严重程度，按严重程度列出每一项，链接到能解释它的页面，并以从 shell 诊断同一问题的命令结尾；因为此仪表板最可能无法访问的时刻正是磁盘已满，而恰好也是邮件送达的时刻。',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-email-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-email-shell.${key}`],
    );
  },
  render() {},
};
