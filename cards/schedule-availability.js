window.ZogographScheduleAvailabilityCard = {
  manifest: Object.freeze({
    id: 'core-schedule-availability',
    locales: ['core-schedule-availability.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'The crontab cannot be read here.',
        'core-schedule-availability.times_unknown_zone':
          "{{job}}: {{expression}} in the server's timezone, which could not be determined",
        'core-schedule-availability.times_frequent':
          '{{job}}: {{profile}}, next at {{next}}',
        'core-schedule-availability.update_policy_readonly':
          'You can view this policy but need write access to change it.',
        'core-schedule-availability.updates_policy_help':
          'This is a root-only host-collector policy. The dashboard records your choices and shows the result; it never runs a package command itself.',
        'core-schedule-availability.update_manager':
          'Host package manager: {{manager}}. Download and Auto-Update always detect first.',
        'core-schedule-availability.never': 'never',
        'core-schedule-availability.due_now': 'due now',
        'core-schedule-availability.host_collector_disabled':
          'The host collector is not enabled, so none of these run. ',
        'core-schedule-availability.dashboard_browsing':
          'Somebody has the dashboard open right now (last seen {{when}}), so anything set to speed up while browsing is currently on its shorter interval.',
        'core-schedule-availability.started':
          'Scan started. It runs in the background — the tables update when it finishes.',
        'core-schedule-availability.set_up': 'Set one up',
        'core-schedule-availability.set_up_title':
          'This one needs choosing before it can be asked for',
        'core-schedule-availability.ask': 'Ask for one',
        'core-schedule-availability.ask_title':
          "It happens on the collector's next pass, not now",
        'core-schedule-availability.what': 'What',
        'core-schedule-availability.how_often': 'How often',
        'core-schedule-availability.recommended': 'Recommended',
        'core-schedule-availability.last_run': 'Last run',
        'core-schedule-availability.next': 'Next',
        'core-schedule-availability.as_recommended': 'as recommended',
        'core-schedule-availability.boost_while_browsing':
          'speed up to every {{interval}} while somebody is looking',
        'core-schedule-availability.only_while_browsing':
          'only while somebody is looking',
        'core-schedule-availability.requested': 'asked {{when}}',
        'core-schedule-availability.request_withdrawn':
          'Request withdrawn. The collector will not run it.',
        'core-schedule-availability.request_already_completed':
          'The collector had already answered that one.',
      }),
      es: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'El crontab no se puede leer aquí.',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}: {{expression}} en la zona horaria del servidor, que no se pudo determinar',
        'core-schedule-availability.times_frequent':
          '{{job}}: {{profile}}, próxima vez a las {{next}}',
        'core-schedule-availability.update_policy_readonly':
          'Puede ver esta política, pero necesita acceso de escritura para cambiarla.',
        'core-schedule-availability.updates_policy_help':
          'Esta es una política del recopilador del host que solo puede ejecutar root. El panel registra sus elecciones y muestra el resultado; nunca ejecuta por sí mismo un comando de paquetes.',
        'core-schedule-availability.update_manager':
          'Gestor de paquetes del host: {{manager}}. Descargar y Actualización automática siempre detectan primero.',
        'core-schedule-availability.never': 'nunca',
        'core-schedule-availability.due_now': 'vence ahora',
        'core-schedule-availability.host_collector_disabled':
          'El recopilador del host no está activado, así que ninguna de estas tareas se ejecuta. ',
        'core-schedule-availability.dashboard_browsing':
          'Alguien tiene abierto el panel ahora mismo (visto por última vez {{when}}), así que todo lo configurado para acelerarse al navegar usa actualmente su intervalo más corto.',
        'core-schedule-availability.started':
          'El análisis se ha iniciado. Se ejecuta en segundo plano; las tablas se actualizan al terminar.',
        'core-schedule-availability.set_up': 'Configurar uno',
        'core-schedule-availability.set_up_title':
          'Debe elegirlo antes de poder solicitarlo',
        'core-schedule-availability.ask': 'Solicitar uno',
        'core-schedule-availability.ask_title':
          'Se ejecutará en la próxima pasada del recolector, no ahora',
        'core-schedule-availability.what': 'Qué',
        'core-schedule-availability.how_often': 'Frecuencia',
        'core-schedule-availability.recommended': 'Recomendado',
        'core-schedule-availability.last_run': 'Última ejecución',
        'core-schedule-availability.next': 'Siguiente',
        'core-schedule-availability.as_recommended': 'según lo recomendado',
        'core-schedule-availability.boost_while_browsing':
          'acelerar a cada {{interval}} mientras alguien está mirando',
        'core-schedule-availability.only_while_browsing':
          'solo mientras alguien está mirando',
        'core-schedule-availability.requested': 'solicitado {{when}}',
        'core-schedule-availability.request_withdrawn':
          'Solicitud retirada. El recolector no la ejecutará.',
        'core-schedule-availability.request_already_completed':
          'El recolector ya había respondido a esa solicitud.',
      }),
      hi: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'यहाँ crontab नहीं पढ़ा जा सकता।',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}: {{expression}} सर्वर के समय क्षेत्र में, जिसे निर्धारित नहीं किया जा सका',
        'core-schedule-availability.times_frequent':
          '{{job}}: {{profile}}, अगला {{next}} पर',
        'core-schedule-availability.update_policy_readonly':
          'आप इस नीति को देख सकते हैं, लेकिन इसे बदलने के लिए लिखने की अनुमति चाहिए।',
        'core-schedule-availability.updates_policy_help':
          'यह केवल root के लिए होस्ट-कलेक्टर नीति है। डैशबोर्ड आपके विकल्प दर्ज करता और परिणाम दिखाता है; यह स्वयं पैकेज कमांड कभी नहीं चलाता।',
        'core-schedule-availability.update_manager':
          'होस्ट पैकेज प्रबंधक: {{manager}}। डाउनलोड और स्वचालित अपडेट हमेशा पहले पता लगाते हैं।',
        'core-schedule-availability.never': 'कभी नहीं',
        'core-schedule-availability.due_now': 'अभी देय',
        'core-schedule-availability.host_collector_disabled':
          'होस्ट कलेक्टर सक्षम नहीं है, इसलिए इनमें से कोई भी नहीं चलता। ',
        'core-schedule-availability.dashboard_browsing':
          'अभी किसी ने डैशबोर्ड खोला हुआ है (अंतिम बार {{when}} देखा गया), इसलिए ब्राउज़ करते समय तेज़ होने के लिए सेट हर चीज़ इस समय अपने छोटे अंतराल पर है।',
        'core-schedule-availability.started':
          'स्कैन शुरू हो गया है। यह पृष्ठभूमि में चलता है — समाप्त होने पर तालिकाएँ अपडेट होंगी।',
        'core-schedule-availability.set_up': 'एक सेट करें',
        'core-schedule-availability.set_up_title':
          'माँगने से पहले इसे चुनना आवश्यक है',
        'core-schedule-availability.ask': 'एक का अनुरोध करें',
        'core-schedule-availability.ask_title':
          'यह अभी नहीं, कलेक्टर की अगली पास में होगा',
        'core-schedule-availability.what': 'क्या',
        'core-schedule-availability.how_often': 'कितनी बार',
        'core-schedule-availability.recommended': 'अनुशंसित',
        'core-schedule-availability.last_run': 'पिछली रन',
        'core-schedule-availability.next': 'अगला',
        'core-schedule-availability.as_recommended': 'अनुशंसा के अनुसार',
        'core-schedule-availability.boost_while_browsing':
          'कोई देख रहा हो तो हर {{interval}} पर तेज़ करें',
        'core-schedule-availability.only_while_browsing':
          'केवल जब कोई देख रहा हो',
        'core-schedule-availability.requested': 'अनुरोध किया गया {{when}}',
        'core-schedule-availability.request_withdrawn':
          'अनुरोध वापस ले लिया गया। कलेक्टर इसे नहीं चलाएगा।',
        'core-schedule-availability.request_already_completed':
          'कलेक्टर उस अनुरोध का जवाब पहले ही दे चुका था।',
      }),
      ar: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'لا يمكن قراءة crontab هنا.',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}: ‏{{expression}} في المنطقة الزمنية للخادم التي تعذر تحديدها',
        'core-schedule-availability.times_frequent':
          '{{job}}: ‏{{profile}}، التشغيل التالي عند {{next}}',
        'core-schedule-availability.update_policy_readonly':
          'يمكنك عرض هذه السياسة، لكنك تحتاج إلى صلاحية كتابة لتغييرها.',
        'core-schedule-availability.updates_policy_help':
          'هذه سياسة لجامع المضيف تعمل بصلاحية root فقط. تسجل اللوحة اختياراتك وتعرض النتيجة؛ ولا تنفذ أمر حزم بنفسها أبدًا.',
        'core-schedule-availability.update_manager':
          'مدير حزم المضيف: {{manager}}. يبدأ التنزيل والتحديث التلقائي بالكشف دائمًا.',
        'core-schedule-availability.never': 'أبدًا',
        'core-schedule-availability.due_now': 'مستحق الآن',
        'core-schedule-availability.host_collector_disabled':
          'جامع المضيف غير مفعّل، لذا لا يعمل أي من هذه. ',
        'core-schedule-availability.dashboard_browsing':
          'هناك من فتح لوحة المعلومات الآن (شوهد آخر مرة {{when}})، لذا فإن كل ما يسرع أثناء التصفح يعمل حاليًا بفاصل أقصر.',
        'core-schedule-availability.started':
          'بدأ الفحص. يعمل في الخلفية — تتحدّث الجداول عند انتهائه.',
        'core-schedule-availability.set_up': 'أعِدّ واحدًا',
        'core-schedule-availability.set_up_title':
          'يجب اختياره قبل أن يمكن طلبه',
        'core-schedule-availability.ask': 'اطلب واحدًا',
        'core-schedule-availability.ask_title':
          'سيحدث في المرور التالي للجامع، وليس الآن',
        'core-schedule-availability.what': 'المهمة',
        'core-schedule-availability.how_often': 'التكرار',
        'core-schedule-availability.recommended': 'الموصى به',
        'core-schedule-availability.last_run': 'آخر تشغيل',
        'core-schedule-availability.next': 'التالي',
        'core-schedule-availability.as_recommended': 'كما هو موصى به',
        'core-schedule-availability.boost_while_browsing':
          'سرّع إلى كل {{interval}} أثناء وجود شخص يشاهد',
        'core-schedule-availability.only_while_browsing':
          'فقط أثناء وجود شخص يشاهد',
        'core-schedule-availability.requested': 'طُلب {{when}}',
        'core-schedule-availability.request_withdrawn':
          'تم سحب الطلب. لن يشغله الجامع.',
        'core-schedule-availability.request_already_completed':
          'كان الجامع قد أجاب عن ذلك الطلب بالفعل.',
      }),
      zh: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          '无法在此处读取 crontab。',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}：{{expression}} 使用无法确定的服务器时区',
        'core-schedule-availability.times_frequent':
          '{{job}}：{{profile}}，下次为 {{next}}',
        'core-schedule-availability.update_policy_readonly':
          '您可以查看此策略，但需要写入权限才能更改它。',
        'core-schedule-availability.updates_policy_help':
          '这是仅供 root 使用的主机收集器策略。仪表板会记录您的选择并显示结果；它本身绝不会运行软件包命令。',
        'core-schedule-availability.update_manager':
          '主机软件包管理器：{{manager}}。下载和自动更新始终先进行检测。',
        'core-schedule-availability.never': '从未',
        'core-schedule-availability.due_now': '现在到期',
        'core-schedule-availability.host_collector_disabled':
          '主机收集器未启用，因此这些任务都不会运行。 ',
        'core-schedule-availability.dashboard_browsing':
          '目前有人打开了仪表板（最后一次看到是在 {{when}}），因此设置为浏览时加快的所有项目当前都在较短间隔运行。',
        'core-schedule-availability.started':
          '扫描已开始。它会在后台运行，完成后表格将更新。',
        'core-schedule-availability.set_up': '设置一个',
        'core-schedule-availability.set_up_title': '需要先选择它，才能请求',
        'core-schedule-availability.ask': '请求一个',
        'core-schedule-availability.ask_title':
          '它会在收集器下一次运行时执行，不是现在',
        'core-schedule-availability.what': '项目',
        'core-schedule-availability.how_often': '频率',
        'core-schedule-availability.recommended': '建议',
        'core-schedule-availability.last_run': '上次运行',
        'core-schedule-availability.next': '下次',
        'core-schedule-availability.as_recommended': '按建议设置',
        'core-schedule-availability.boost_while_browsing':
          '有人查看时加快至每 {{interval}} 一次',
        'core-schedule-availability.only_while_browsing': '仅在有人查看时',
        'core-schedule-availability.requested': '已请求 {{when}}',
        'core-schedule-availability.request_withdrawn':
          '请求已撤销。收集器不会执行它。',
        'core-schedule-availability.request_already_completed':
          '收集器已经处理了该请求。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-availability.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
