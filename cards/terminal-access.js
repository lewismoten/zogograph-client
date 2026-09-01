/* Core card package: terminal permission, unlock, and lock controls. */
window.ZogographTerminalAccessCard = {
  manifest: Object.freeze({
    id: 'core-terminal-access',
    locales: ['core-terminal-access.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-access.sign_in_to_enable': 'Sign in to switch it on.',
        'core-terminal-access.switch_on': 'Switch it on',
        'core-terminal-access.enable_title': 'Allow a shell in this web page?',
        'core-terminal-access.enable_warning_execution':
          'This runs whatever is typed into it, as the user the web server runs as, and shows the output. It is a web shell.',
        'core-terminal-access.enable_warning_password':
          "That means anybody who has this dashboard's password has it too. If that password is shared, or is the only thing between the internet and this machine, decide about that first.",
        'core-terminal-access.enable_warning_audit':
          'Every command is written to the action log before it runs, so a command that hangs the machine still appears in the record. It stays locked until you open it, and it locks itself again after the time you choose.',
        'core-terminal-access.enable_warning_disable':
          'You can switch it off again here at any time.',
        'core-terminal-access.enabled_locked':
          'The terminal is switched on. It is still locked until you open it.',
        'core-terminal-access.config_disabled':
          'lib/config.php has it off, which is the default. Switching it on here overrules that and is remembered; the config file is not rewritten.',
        'core-terminal-access.locked_help':
          'The terminal is locked. It locks itself after a while on purpose — an always-open shell is one that whoever gets this password inherits. Open it for as long as you need and it will close on its own.',
        'core-terminal-access.unlock': 'Unlock it',
        'core-terminal-access.open_for': 'Open for another {{duration}}.',
        'core-terminal-access.extend': 'Give it longer',
        'core-terminal-access.lock_now': 'Lock it now',
        'core-terminal-access.locked': 'Locked.',
        'core-terminal-access.open_until': 'Open until {{when}}.',
        'core-terminal-access.locked_again': 'It has locked itself again.',
        'core-terminal-access.switch_off': 'Switch the terminal off',
        'core-terminal-access.switch_off_title': 'Switch the terminal off?',
        'core-terminal-access.switch_off_consequence':
          'This withdraws the permission, not just the unlock. Anything running now is stopped and the page goes back to offering a switch.',
        'core-terminal-access.switch_off_retention':
          'Nothing is lost - the transcript of what has been run stays in the action log, and you can switch it on again whenever you want it.',
        'core-terminal-access.switch_off_action': 'Switch it off',
        'core-terminal-access.switched_off': 'The terminal is switched off.',
        'core-terminal-access.switched_off_help':
          'The terminal is switched off. It is off by default because it runs whatever is typed into it — that is not a reason to avoid it, but it is a reason for the decision to be deliberate rather than inherited from a sample file.',
      }),
      es: Object.freeze({
        'core-terminal-access.sign_in_to_enable':
          'Inicie sesión para activarlo.',
        'core-terminal-access.switch_on': 'Activarlo',
        'core-terminal-access.enable_title':
          '¿Permitir una shell en esta página web?',
        'core-terminal-access.enable_warning_execution':
          'Esto ejecuta lo que se escriba en ella como el usuario con el que se ejecuta el servidor web y muestra la salida. Es una shell web.',
        'core-terminal-access.enable_warning_password':
          'Eso significa que también la tiene cualquiera que tenga la contraseña de este panel. Si esa contraseña se comparte, o es lo único entre Internet y esta máquina, piense en ello antes.',
        'core-terminal-access.enable_warning_audit':
          'Cada comando se escribe en el registro de acciones antes de ejecutarse, por lo que un comando que bloquee la máquina seguirá apareciendo en el registro. Permanece bloqueada hasta que la abra y vuelve a bloquearse tras el tiempo que elija.',
        'core-terminal-access.enable_warning_disable':
          'Puede desactivarla de nuevo aquí en cualquier momento.',
        'core-terminal-access.enabled_locked':
          'La terminal está activada. Seguirá bloqueada hasta que la abra.',
        'core-terminal-access.config_disabled':
          'lib/config.php la tiene desactivada, que es el valor predeterminado. Activarla aquí lo anula y se recuerda; el archivo de configuración no se reescribe.',
        'core-terminal-access.locked_help':
          'La terminal está bloqueada. Se bloquea sola al cabo de un tiempo intencionalmente: una shell siempre abierta la hereda cualquiera que obtenga esta contraseña. Ábrala el tiempo que necesite y se cerrará sola.',
        'core-terminal-access.unlock': 'Desbloquearlo',
        'core-terminal-access.open_for': 'Abierto durante {{duration}} más.',
        'core-terminal-access.extend': 'Ampliar el tiempo',
        'core-terminal-access.lock_now': 'Bloquearlo ahora',
        'core-terminal-access.locked': 'Bloqueada.',
        'core-terminal-access.open_until': 'Abierta hasta {{when}}.',
        'core-terminal-access.locked_again': 'Se ha bloqueado de nuevo.',
        'core-terminal-access.switch_off': 'Desactivar la terminal',
        'core-terminal-access.switch_off_title': '¿Desactivar la terminal?',
        'core-terminal-access.switch_off_consequence':
          'Esto retira el permiso, no solo el desbloqueo. Se detiene lo que esté ejecutándose y la página volverá a ofrecer un interruptor.',
        'core-terminal-access.switch_off_retention':
          'No se pierde nada: la transcripción de lo ejecutado permanece en el registro de acciones y puede volver a activarla cuando quiera.',
        'core-terminal-access.switch_off_action': 'Desactivarla',
        'core-terminal-access.switched_off': 'La terminal está desactivada.',
        'core-terminal-access.switched_off_help':
          'El terminal está apagado. Lo está de forma predeterminada porque ejecuta lo que se escribe en él; no es razón para evitarlo, pero sí para que la decisión sea deliberada y no heredada de un archivo de ejemplo.',
      }),
      hi: Object.freeze({
        'core-terminal-access.sign_in_to_enable':
          'इसे चालू करने के लिए साइन इन करें।',
        'core-terminal-access.switch_on': 'इसे चालू करें',
        'core-terminal-access.enable_title':
          'क्या इस वेब पेज में शेल की अनुमति दें?',
        'core-terminal-access.enable_warning_execution':
          'इसमें जो भी टाइप किया जाएगा, वह उसी उपयोगकर्ता के रूप में चलेगा जिससे वेब सर्वर चल रहा है और आउटपुट दिखेगा। यह एक वेब शेल है।',
        'core-terminal-access.enable_warning_password':
          'इसका अर्थ है कि जिसके पास भी इस डैशबोर्ड का पासवर्ड है, उसके पास यह भी है। यदि पासवर्ड साझा है या इंटरनेट और इस मशीन के बीच यही अकेली सुरक्षा है, तो पहले इस पर विचार करें।',
        'core-terminal-access.enable_warning_audit':
          'हर कमांड चलने से पहले कार्रवाई लॉग में लिखा जाता है, इसलिए मशीन को अटकाने वाला कमांड भी रिकॉर्ड में दिखता है। इसे खोलने तक यह लॉक रहता है और आपके चुने समय के बाद फिर लॉक हो जाता है।',
        'core-terminal-access.enable_warning_disable':
          'आप इसे यहाँ से कभी भी फिर बंद कर सकते हैं।',
        'core-terminal-access.enabled_locked':
          'टर्मिनल चालू है। आपके खोलने तक यह लॉक रहेगा।',
        'core-terminal-access.config_disabled':
          'lib/config.php में यह बंद है, जो डिफ़ॉल्ट है। इसे यहाँ चालू करना उस सेटिंग को बदलता है और याद रखा जाता है; कॉन्फ़िग फ़ाइल फिर से नहीं लिखी जाती।',
        'core-terminal-access.locked_help':
          'टर्मिनल लॉक है। यह कुछ समय बाद जानबूझकर खुद लॉक हो जाता है—हमेशा खुला शेल उसे मिल जाता है जिसे यह पासवर्ड मिल जाए। जरूरत भर खोलें, यह अपने आप बंद हो जाएगा।',
        'core-terminal-access.unlock': 'इसे अनलॉक करें',
        'core-terminal-access.open_for': 'और {{duration}} के लिए खुला है।',
        'core-terminal-access.extend': 'ज़्यादा समय दें',
        'core-terminal-access.lock_now': 'अभी लॉक करें',
        'core-terminal-access.locked': 'लॉक किया गया।',
        'core-terminal-access.open_until': '{{when}} तक खुला है।',
        'core-terminal-access.locked_again': 'यह फिर से लॉक हो गया है।',
        'core-terminal-access.switch_off': 'टर्मिनल बंद करें',
        'core-terminal-access.switch_off_title': 'टर्मिनल बंद करें?',
        'core-terminal-access.switch_off_consequence':
          'यह केवल अनलॉक नहीं, अनुमति वापस लेता है। अभी चल रही हर चीज़ रोक दी जाती है और पृष्ठ फिर से इसे चालू करने का विकल्प देगा।',
        'core-terminal-access.switch_off_retention':
          'कुछ भी नहीं खोता—चलाए गए कार्यों का प्रतिलेख कार्रवाई लॉग में रहता है और आप जब चाहें इसे फिर चालू कर सकते हैं।',
        'core-terminal-access.switch_off_action': 'इसे बंद करें',
        'core-terminal-access.switched_off': 'टर्मिनल बंद है।',
        'core-terminal-access.switched_off_help':
          'टर्मिनल बंद है। यह डिफ़ॉल्ट रूप से बंद है क्योंकि इसमें टाइप किया गया कुछ भी चलता है; यह इससे बचने का कारण नहीं, बल्कि निर्णय को नमूना फ़ाइल से विरासत में लेने के बजाय सोच-समझकर लेने का कारण है।',
      }),
      ar: Object.freeze({
        'core-terminal-access.sign_in_to_enable': 'سجّل الدخول لتشغيله.',
        'core-terminal-access.switch_on': 'شغّله',
        'core-terminal-access.enable_title': 'هل تسمح بصدفة في صفحة الويب هذه؟',
        'core-terminal-access.enable_warning_execution':
          'يشغّل هذا أي شيء يُكتب فيه بالمستخدم الذي يعمل به خادم الويب ويعرض المخرجات. إنها صدفة ويب.',
        'core-terminal-access.enable_warning_password':
          'يعني ذلك أن أي شخص لديه كلمة مرور لوحة المعلومات هذه يملكها أيضًا. إذا كانت كلمة المرور مشتركة، أو كانت الحاجز الوحيد بين الإنترنت وهذه الآلة، ففكّر في ذلك أولًا.',
        'core-terminal-access.enable_warning_audit':
          'يُكتب كل أمر في سجل الإجراءات قبل تشغيله، لذا يظهر في السجل حتى الأمر الذي يعلّق الآلة. تبقى مقفلة حتى تفتحها، ثم تقفل نفسها مجددًا بعد المدة التي تختارها.',
        'core-terminal-access.enable_warning_disable':
          'يمكنك إيقافها من هنا مجددًا في أي وقت.',
        'core-terminal-access.enabled_locked':
          'المحطة مفعلة، لكنها تبقى مقفلة حتى تفتحها.',
        'core-terminal-access.config_disabled':
          'يعطّلها lib/config.php، وهذا هو الافتراضي. تشغيلها هنا يتجاوز ذلك ويُتذكر؛ لا يُعاد كتابة ملف الإعداد.',
        'core-terminal-access.locked_help':
          'المحطة مقفلة. تقفل نفسها عمدًا بعد فترة—فالصدفة المفتوحة دائمًا يرثها أي شخص يحصل على كلمة المرور هذه. افتحها للمدة التي تحتاجها وستغلق نفسها.',
        'core-terminal-access.unlock': 'افتح قفله',
        'core-terminal-access.open_for': 'مفتوح لمدة {{duration}} أخرى.',
        'core-terminal-access.extend': 'امنحه وقتًا أطول',
        'core-terminal-access.lock_now': 'اقفله الآن',
        'core-terminal-access.locked': 'مقفلة.',
        'core-terminal-access.open_until': 'مفتوحة حتى {{when}}.',
        'core-terminal-access.locked_again': 'لقد قفل نفسه مجددًا.',
        'core-terminal-access.switch_off': 'أوقف المحطة',
        'core-terminal-access.switch_off_title': 'هل توقف المحطة؟',
        'core-terminal-access.switch_off_consequence':
          'يسحب هذا الإذن، وليس مجرد الفتح. يتوقف أي شيء يعمل الآن وتعود الصفحة إلى عرض مفتاح تشغيل.',
        'core-terminal-access.switch_off_retention':
          'لا يضيع شيء—يبقى نص ما جرى تشغيله في سجل الإجراءات ويمكنك تشغيلها مجددًا متى شئت.',
        'core-terminal-access.switch_off_action': 'أوقفها',
        'core-terminal-access.switched_off': 'المحطة متوقفة.',
        'core-terminal-access.switched_off_help':
          'الطرفية متوقفة. وهي متوقفة افتراضيًا لأنها تشغّل ما يُكتب فيها؛ ليس ذلك سببًا لتجنبها، لكنه سبب لأن يكون القرار مقصودًا لا موروثًا من ملف نموذجي.',
      }),
      zh: Object.freeze({
        'core-terminal-access.sign_in_to_enable': '请登录后启用它。',
        'core-terminal-access.switch_on': '启用它',
        'core-terminal-access.enable_title': '允许在此网页中使用 shell 吗？',
        'core-terminal-access.enable_warning_execution':
          '其中输入的任何内容都会以运行 Web 服务器的用户身份执行并显示输出。这是一个 Web shell。',
        'core-terminal-access.enable_warning_password':
          '这意味着任何拥有此仪表板密码的人也能使用它。如果该密码被共享，或者它是互联网与此机器之间唯一的屏障，请先慎重考虑。',
        'core-terminal-access.enable_warning_audit':
          '每条命令在运行前都会写入操作日志，因此即使会让机器挂起的命令也会留在记录中。打开前它始终锁定，并会在您选择的时间后再次自动锁定。',
        'core-terminal-access.enable_warning_disable':
          '您随时可以在此重新关闭它。',
        'core-terminal-access.enabled_locked':
          '终端已启用。打开前它仍保持锁定。',
        'core-terminal-access.config_disabled':
          'lib/config.php 将其关闭，这是默认值。在此启用会覆盖该设置并被记住；不会重写配置文件。',
        'core-terminal-access.locked_help':
          '终端已锁定。它会在一段时间后故意自行锁定——任何拿到此密码的人都能继承一个始终打开的 shell。按需打开，它会自行关闭。',
        'core-terminal-access.unlock': '解锁它',
        'core-terminal-access.open_for': '还可打开 {{duration}}。',
        'core-terminal-access.extend': '延长时间',
        'core-terminal-access.lock_now': '立即锁定',
        'core-terminal-access.locked': '已锁定。',
        'core-terminal-access.open_until': '将开放至 {{when}}。',
        'core-terminal-access.locked_again': '它已再次锁定。',
        'core-terminal-access.switch_off': '关闭终端',
        'core-terminal-access.switch_off_title': '关闭终端？',
        'core-terminal-access.switch_off_consequence':
          '这会撤销权限，而不仅是锁定。当前正在运行的内容会停止，页面将恢复为提供启用开关的状态。',
        'core-terminal-access.switch_off_retention':
          '不会丢失任何内容——已运行内容的记录会保留在操作日志中，您随时可以再次启用它。',
        'core-terminal-access.switch_off_action': '关闭它',
        'core-terminal-access.switched_off': '终端已关闭。',
        'core-terminal-access.switched_off_help':
          '终端已关闭。它默认关闭，因为它会运行输入的任何内容；这不是避免使用它的理由，却是让决定经过深思而非继承自示例文件的理由。',
      }),
    }),
  }),

  text(key, { i18n }, values = {}) {
    const id = `core-terminal-access.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },

  render({ key, values = {} }, helpers) {
    return this.text(key, helpers, values);
  },
};
