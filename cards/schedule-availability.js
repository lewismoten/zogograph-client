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
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-availability.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
