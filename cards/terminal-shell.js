/* Core card package: static shell copy for the terminal page. */
window.ZogographTerminalShellCard = {
  manifest: Object.freeze({
    id: 'core-terminal-shell',
    locales: ['core-terminal-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-shell.title': 'Terminal',
        'core-terminal-shell.sign_in': 'Sign in to use the terminal.',
        'core-terminal-shell.browse_watched': 'Browse watched files',
        'core-terminal-shell.command_placeholder':
          'df -h; echo; ls -la ~/public_html | head',
        'core-terminal-shell.suggested_commands':
          '{{assistant}} suggested {{count, number}} commands',
        'core-terminal-shell.help':
          'This runs what you type, as the user the web server runs as, and shows the output. Be clear-eyed about what that means: anybody who has the dashboard password has this, so it stays off until somebody switches it on here, and every command is written to the action log before it runs. Each command is its own process; the directory it runs in carries over between them and is shown above the box, so {{cd}} works and you can see where you are. What does not carry over is anything else a shell remembers — variables, functions, background jobs — because there is no shell sitting between requests to remember them.',
        'core-terminal-shell.interactive_help':
          'It is a transcript, not a screen. Colour, bold, tabs and progress lines that rewrite themselves all render. Text editors such as {{vi}} and {{nano}} open a constrained browser editor instead. Programs that need an interactive terminal — {{top}}, {{ssh}}, or anything asking for a password — cannot be driven from here.',
        'core-terminal-shell.bell_title': 'The command rang the terminal bell',
        'core-terminal-shell.unavailable_reason':
          '{{reason}} There is nothing this page can do about that, and nothing it should try — a host that has taken shell functions away has taken them away on purpose.',
        'core-terminal-shell.limits':
          'Runs as this account with {{shell}}. Anything still going after {{duration}} is stopped, and output stops at {{output}}.',
        'core-terminal-shell.just_started': 'Just started…',
        'core-terminal-shell.running_for_so_far':
          'Running for {{duration}} — {{size}} so far.',
        'core-terminal-shell.running_so_far': 'Running — {{size}} so far.',
        'core-terminal-shell.running_in': 'Running in ',
        'core-terminal-shell.return_home': 'back to the top',
        'core-terminal-shell.return_home_title': 'Return to {{home}}',
      }),
      es: Object.freeze({
        'core-terminal-shell.title': 'Terminal',
        'core-terminal-shell.sign_in': 'Inicie sesión para usar la terminal.',
        'core-terminal-shell.browse_watched': 'Explorar archivos vigilados',
        'core-terminal-shell.command_placeholder':
          'df -h; echo; ls -la ~/public_html | head',
        'core-terminal-shell.suggested_commands':
          '{{assistant}} sugirió {{count, number}} comandos',
        'core-terminal-shell.help':
          'Esto ejecuta lo que escribe como el usuario con el que se ejecuta el servidor web y muestra la salida. Sea consciente de lo que significa: cualquiera que tenga la contraseña del panel tiene esto, por lo que permanece apagado hasta que alguien lo active aquí, y cada comando se escribe en el registro de acciones antes de ejecutarse. Cada comando es su propio proceso; el directorio donde se ejecuta se conserva entre ellos y se muestra encima del cuadro, así que {{cd}} funciona y puede ver dónde está. Lo que no se conserva es cualquier otra cosa que recuerde una shell —variables, funciones, trabajos en segundo plano— porque no hay una shell entre solicitudes que lo recuerde.',
        'core-terminal-shell.interactive_help':
          'Es una transcripción, no una pantalla. Se representan color, negrita, tabulaciones y líneas de progreso que se reescriben. Los editores de texto como {{vi}} y {{nano}} abren en su lugar un editor de navegador restringido. Los programas que necesitan una terminal interactiva —{{top}}, {{ssh}} o cualquier cosa que pida una contraseña— no se pueden usar desde aquí.',
        'core-terminal-shell.bell_title':
          'El comando hizo sonar la campana del terminal',
        'core-terminal-shell.unavailable_reason':
          '{{reason}} Esta página no puede hacer nada al respecto ni debería intentarlo: un alojamiento que ha retirado las funciones de shell lo ha hecho deliberadamente.',
        'core-terminal-shell.limits':
          'Se ejecuta como esta cuenta con {{shell}}. Todo lo que siga ejecutándose después de {{duration}} se detiene y la salida se corta en {{output}}.',
        'core-terminal-shell.just_started': 'Acaba de iniciarse…',
        'core-terminal-shell.running_for_so_far':
          'En ejecución desde hace {{duration}} — {{size}} hasta ahora.',
        'core-terminal-shell.running_so_far':
          'En ejecución — {{size}} hasta ahora.',
        'core-terminal-shell.running_in': 'Ejecutando en ',
        'core-terminal-shell.return_home': 'volver al inicio',
        'core-terminal-shell.return_home_title': 'Volver a {{home}}',
      }),
      hi: Object.freeze({
        'core-terminal-shell.title': 'टर्मिनल',
        'core-terminal-shell.sign_in':
          'टर्मिनल का उपयोग करने के लिए साइन इन करें।',
        'core-terminal-shell.browse_watched':
          'देखी जा रही फ़ाइलें ब्राउज़ करें',
        'core-terminal-shell.command_placeholder':
          'df -h; echo; ls -la ~/public_html | head',
        'core-terminal-shell.suggested_commands':
          '{{assistant}} ने {{count, number}} कमांड सुझाए',
        'core-terminal-shell.help':
          'यह आपके टाइप किए को उस उपयोगकर्ता के रूप में चलाता है जिसके रूप में वेब सर्वर चलता है और आउटपुट दिखाता है। इसका अर्थ स्पष्ट रखें: जिसके पास डैशबोर्ड पासवर्ड है उसके पास यह है, इसलिए कोई इसे यहाँ चालू करे तब तक यह बंद रहता है और हर कमांड चलने से पहले एक्शन लॉग में लिखा जाता है। हर कमांड अपनी प्रक्रिया है; जिस डायरेक्टरी में वह चलता है वह उनके बीच बनी रहती है और बॉक्स के ऊपर दिखती है, इसलिए {{cd}} काम करता है और आप देख सकते हैं कहाँ हैं। शेल को याद रहने वाली बाकी चीज़ें—वेरिएबल, फंक्शन, बैकग्राउंड जॉब—नहीं रहतीं, क्योंकि अनुरोधों के बीच याद रखने के लिए कोई शेल नहीं है।',
        'core-terminal-shell.interactive_help':
          'यह स्क्रीन नहीं, ट्रांसक्रिप्ट है। रंग, बोल्ड, टैब और खुद को फिर लिखने वाली प्रगति पंक्तियाँ रेंडर होती हैं। {{vi}} और {{nano}} जैसे टेक्स्ट एडिटर इसके बजाय सीमित ब्राउज़र एडिटर खोलते हैं। इंटरैक्टिव टर्मिनल चाहिए वाले प्रोग्राम—{{top}}, {{ssh}} या पासवर्ड माँगने वाली कोई चीज़—यहाँ से नहीं चलाए जा सकते।',
        'core-terminal-shell.bell_title': 'कमांड ने टर्मिनल की घंटी बजाई',
        'core-terminal-shell.unavailable_reason':
          '{{reason}} यह पृष्ठ इसके बारे में कुछ नहीं कर सकता और न ही कोशिश करनी चाहिए — जिस होस्ट ने शेल फ़ंक्शन हटा दिए हैं, उसने उन्हें जानबूझकर हटाया है।',
        'core-terminal-shell.limits':
          'यह {{shell}} के साथ इस खाते के रूप में चलता है। {{duration}} के बाद भी चल रही कोई भी चीज़ रोक दी जाती है और आउटपुट {{output}} पर रुक जाता है।',
        'core-terminal-shell.just_started': 'अभी शुरू हुआ…',
        'core-terminal-shell.running_for_so_far':
          '{{duration}} से चल रहा है — अब तक {{size}}।',
        'core-terminal-shell.running_so_far': 'चल रहा है — अब तक {{size}}।',
        'core-terminal-shell.running_in': 'इसमें चल रहा है ',
        'core-terminal-shell.return_home': 'शीर्ष पर वापस जाएँ',
        'core-terminal-shell.return_home_title': '{{home}} पर लौटें',
      }),
      ar: Object.freeze({
        'core-terminal-shell.title': 'الطرفية',
        'core-terminal-shell.sign_in': 'سجّل الدخول لاستخدام الطرفية.',
        'core-terminal-shell.browse_watched': 'تصفح الملفات المراقبة',
        'core-terminal-shell.command_placeholder':
          'df -h; echo; ls -la ~/public_html | head',
        'core-terminal-shell.suggested_commands':
          'اقترح {{assistant}} {{count, number}} من الأوامر',
        'core-terminal-shell.help':
          'يشغّل هذا ما تكتبه كمستخدم خادم الويب ويعرض المخرجات. كن واضحًا بشأن معنى ذلك: أي شخص لديه كلمة مرور لوحة المعلومات لديه هذا، لذا يبقى معطلاً حتى يشغله أحد هنا، ويُكتب كل أمر في سجل الإجراءات قبل تشغيله. كل أمر عمليته الخاصة؛ يبقى الدليل الذي يعمل فيه بينها ويظهر فوق المربع، لذا يعمل {{cd}} ويمكنك رؤية مكانك. ما لا يبقى هو أي شيء آخر تتذكره shell — المتغيرات والدوال والوظائف الخلفية — لأنه لا توجد shell بين الطلبات لتتذكرها.',
        'core-terminal-shell.interactive_help':
          'هذا سجل نصي لا شاشة. تُعرض الألوان والخط العريض وعلامات الجدولة وخطوط التقدم التي تعيد كتابة نفسها. يفتح محررا النصوص مثل {{vi}} و{{nano}} محرر متصفح مقيدًا بدلًا من ذلك. لا يمكن تشغيل البرامج التي تحتاج طرفية تفاعلية — {{top}} أو {{ssh}} أو أي شيء يطلب كلمة مرور — من هنا.',
        'core-terminal-shell.bell_title': 'أطلق الأمر جرس الطرفية',
        'core-terminal-shell.unavailable_reason':
          '{{reason}} لا تستطيع هذه الصفحة فعل شيء حيال ذلك، ولا ينبغي لها أن تحاول — فالمضيف الذي أزال وظائف الصدفة أزالها عن قصد.',
        'core-terminal-shell.limits':
          'يعمل بهذا الحساب باستخدام {{shell}}. يُوقَف أي شيء ما زال يعمل بعد {{duration}}، وتتوقف المخرجات عند {{output}}.',
        'core-terminal-shell.just_started': 'بدأ للتو…',
        'core-terminal-shell.running_for_so_far':
          'يعمل منذ {{duration}} — {{size}} حتى الآن.',
        'core-terminal-shell.running_so_far': 'يعمل — {{size}} حتى الآن.',
        'core-terminal-shell.running_in': 'يعمل في ',
        'core-terminal-shell.return_home': 'العودة إلى الأعلى',
        'core-terminal-shell.return_home_title': 'العودة إلى {{home}}',
      }),
      zh: Object.freeze({
        'core-terminal-shell.title': '终端',
        'core-terminal-shell.sign_in': '登录后可使用终端。',
        'core-terminal-shell.browse_watched': '浏览受监视的文件',
        'core-terminal-shell.command_placeholder':
          'df -h; echo; ls -la ~/public_html | head',
        'core-terminal-shell.suggested_commands':
          '{{assistant}} 建议了 {{count, number}} 条命令',
        'core-terminal-shell.help':
          '这里会以 Web 服务器运行所用的用户身份执行您输入的内容，并显示输出。请清楚其含义：任何拥有仪表板密码的人都拥有此功能，所以在有人在此启用前它保持关闭，每条命令都会在运行前写入操作日志。每条命令都是独立进程；它运行的目录会在命令之间保持，并显示在输入框上方，因此 {{cd}} 可以使用，您也能看到当前位置。不会保留的是 shell 记住的其他内容——变量、函数、后台作业——因为请求之间没有一个 shell 来记住它们。',
        'core-terminal-shell.interactive_help':
          '这是记录，不是屏幕。颜色、粗体、制表符和会自行重写的进度行都会呈现。{{vi}} 和 {{nano}} 等文本编辑器会改为打开受限的浏览器编辑器。需要交互式终端的程序——{{top}}、{{ssh}} 或任何要求输入密码的程序——无法从这里操作。',
        'core-terminal-shell.bell_title': '该命令触发了终端响铃',
        'core-terminal-shell.unavailable_reason':
          '{{reason}} 此页面无法对此做任何事，也不应尝试——已移除 shell 功能的主机是有意这样做的。',
        'core-terminal-shell.limits':
          '它以此帐户身份通过 {{shell}} 运行。{{duration}} 后仍在运行的内容会被停止，输出将在 {{output}} 处截断。',
        'core-terminal-shell.just_started': '刚刚开始…',
        'core-terminal-shell.running_for_so_far':
          '已运行 {{duration}} — 目前 {{size}}。',
        'core-terminal-shell.running_so_far': '正在运行 — 目前 {{size}}。',
        'core-terminal-shell.running_in': '正在运行于 ',
        'core-terminal-shell.return_home': '返回顶部',
        'core-terminal-shell.return_home_title': '返回 {{home}}',
      }),
    }),
  }),
  text(key, helpers, values = {}) {
    return helpers.i18n.t(
      `core-terminal-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-terminal-shell.${key}`],
    );
  },
  render() {},
};
