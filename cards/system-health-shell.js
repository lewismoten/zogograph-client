/* Core card package: static shell copy for system health. */
window.ZogographSystemHealthShellCard = {
  manifest: Object.freeze({
    id: 'core-system-health-shell',
    locales: ['core-system-health-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-health-shell.title': 'Health',
        'core-system-health-shell.help':
          'What this install can and cannot do. The first three are the usual reason a fresh deployment returns errors.',
        'core-system-health-shell.php_version': 'PHP {{version}}',
        'core-system-health-shell.kernel_version': 'kernel {{version}}',
        'core-system-health-shell.schema_version': 'schema v{{version}}',
        'core-system-health-shell.problem_logged_one':
          '{{count, number}} problem logged',
        'core-system-health-shell.problem_logged_many':
          '{{count, number}} problems logged',
        'core-system-health-shell.php_logs_to': 'PHP logs to {{path}}',
        'core-system-health-shell.this_log': 'this log: {{path}}',
        'core-system-health-shell.data_dir_writable': 'Data directory writable',
        'core-system-health-shell.data_dir_writable_help':
          'The database cannot be created or updated.',
        'core-system-health-shell.database_writable': 'Database writable',
        'core-system-health-shell.database_writable_help':
          'Readings cannot be recorded.',
        'core-system-health-shell.scan_root_readable':
          'Watched directory readable',
        'core-system-health-shell.scan_root_readable_help':
          'Scans will find nothing.',
        'core-system-health-shell.php_new_enough': 'PHP 8.1 or newer',
        'core-system-health-shell.php_new_enough_help':
          'This code will not run correctly.',
        'core-system-health-shell.sqlite': 'SQLite driver',
        'core-system-health-shell.sqlite_help': 'Nothing can be stored at all.',
        'core-system-health-shell.mail_available': 'mail() available',
        'core-system-health-shell.mail_available_help':
          'Alert emails cannot be sent from here.',
        'core-system-health-shell.shell_available': 'shell_exec available',
        'core-system-health-shell.shell_available_help':
          'Inodes and the crontab editor are unavailable.',
        'core-system-health-shell.proc_readable': '/proc readable',
        'core-system-health-shell.proc_readable_help':
          'CPU, memory and network stay empty.',
      }),
      es: Object.freeze({
        'core-system-health-shell.title': 'Estado',
        'core-system-health-shell.help':
          'Lo que esta instalación puede y no puede hacer. Los tres primeros son la razón habitual por la que una implementación nueva devuelve errores.',
        'core-system-health-shell.php_version': 'PHP {{version}}',
        'core-system-health-shell.kernel_version': 'núcleo {{version}}',
        'core-system-health-shell.schema_version': 'esquema v{{version}}',
        'core-system-health-shell.problem_logged_one':
          '{{count, number}} problema registrado',
        'core-system-health-shell.problem_logged_many':
          '{{count, number}} problemas registrados',
        'core-system-health-shell.php_logs_to': 'PHP registra en {{path}}',
        'core-system-health-shell.this_log': 'este registro: {{path}}',
        'core-system-health-shell.data_dir_writable':
          'Directorio de datos escribible',
        'core-system-health-shell.data_dir_writable_help':
          'La base de datos no se puede crear ni actualizar.',
        'core-system-health-shell.database_writable':
          'Base de datos escribible',
        'core-system-health-shell.database_writable_help':
          'No se pueden registrar lecturas.',
        'core-system-health-shell.scan_root_readable':
          'Directorio vigilado legible',
        'core-system-health-shell.scan_root_readable_help':
          'Los escaneos no encontrarán nada.',
        'core-system-health-shell.php_new_enough': 'PHP 8.1 o posterior',
        'core-system-health-shell.php_new_enough_help':
          'Este código no funcionará correctamente.',
        'core-system-health-shell.sqlite': 'Controlador SQLite',
        'core-system-health-shell.sqlite_help': 'No se puede guardar nada.',
        'core-system-health-shell.mail_available': 'mail() disponible',
        'core-system-health-shell.mail_available_help':
          'No se pueden enviar correos de alerta desde aquí.',
        'core-system-health-shell.shell_available': 'shell_exec disponible',
        'core-system-health-shell.shell_available_help':
          'Los inodos y el editor de crontab no están disponibles.',
        'core-system-health-shell.proc_readable': '/proc legible',
        'core-system-health-shell.proc_readable_help':
          'CPU, memoria y red quedarán vacíos.',
      }),
      hi: Object.freeze({
        'core-system-health-shell.title': 'स्वास्थ्य',
        'core-system-health-shell.help':
          'यह इंस्टॉलेशन क्या कर सकता है और क्या नहीं। पहले तीन नए डिप्लॉयमेंट के त्रुटियाँ लौटाने का सामान्य कारण हैं।',
        'core-system-health-shell.php_version': 'PHP {{version}}',
        'core-system-health-shell.kernel_version': 'कर्नेल {{version}}',
        'core-system-health-shell.schema_version': 'स्कीमा v{{version}}',
        'core-system-health-shell.problem_logged_one':
          '{{count, number}} समस्या दर्ज',
        'core-system-health-shell.problem_logged_many':
          '{{count, number}} समस्याएँ दर्ज',
        'core-system-health-shell.php_logs_to':
          'PHP लॉग यहाँ लिखता है: {{path}}',
        'core-system-health-shell.this_log': 'यह लॉग: {{path}}',
        'core-system-health-shell.data_dir_writable':
          'डेटा निर्देशिका लिखने योग्य',
        'core-system-health-shell.data_dir_writable_help':
          'डेटाबेस बनाया या अपडेट नहीं किया जा सकता।',
        'core-system-health-shell.database_writable': 'डेटाबेस लिखने योग्य',
        'core-system-health-shell.database_writable_help':
          'रीडिंग दर्ज नहीं की जा सकतीं।',
        'core-system-health-shell.scan_root_readable':
          'निगरानी निर्देशिका पढ़ने योग्य',
        'core-system-health-shell.scan_root_readable_help':
          'स्कैन को कुछ नहीं मिलेगा।',
        'core-system-health-shell.php_new_enough': 'PHP 8.1 या नया',
        'core-system-health-shell.php_new_enough_help':
          'यह कोड सही ढंग से नहीं चलेगा।',
        'core-system-health-shell.sqlite': 'SQLite ड्राइवर',
        'core-system-health-shell.sqlite_help':
          'कुछ भी संग्रहीत नहीं किया जा सकता।',
        'core-system-health-shell.mail_available': 'mail() उपलब्ध',
        'core-system-health-shell.mail_available_help':
          'यहाँ से अलर्ट ईमेल नहीं भेजे जा सकते।',
        'core-system-health-shell.shell_available': 'shell_exec उपलब्ध',
        'core-system-health-shell.shell_available_help':
          'इनोड और क्रॉनटैब संपादक उपलब्ध नहीं हैं।',
        'core-system-health-shell.proc_readable': '/proc पढ़ने योग्य',
        'core-system-health-shell.proc_readable_help':
          'CPU, मेमोरी और नेटवर्क खाली रहेंगे।',
      }),
      ar: Object.freeze({
        'core-system-health-shell.title': 'الحالة الصحية',
        'core-system-health-shell.help':
          'ما يستطيع هذا التثبيت فعله وما لا يستطيع. الثلاثة الأولى هي السبب المعتاد لعودة أخطاء من نشر جديد.',
        'core-system-health-shell.php_version': 'PHP {{version}}',
        'core-system-health-shell.kernel_version': 'النواة {{version}}',
        'core-system-health-shell.schema_version': 'المخطط v{{version}}',
        'core-system-health-shell.problem_logged_one': 'مشكلة واحدة مسجلة',
        'core-system-health-shell.problem_logged_many':
          '{{count, number}} مشكلات مسجلة',
        'core-system-health-shell.php_logs_to': 'يسجل PHP في {{path}}',
        'core-system-health-shell.this_log': 'هذا السجل: {{path}}',
        'core-system-health-shell.data_dir_writable':
          'دليل البيانات قابل للكتابة',
        'core-system-health-shell.data_dir_writable_help':
          'لا يمكن إنشاء قاعدة البيانات أو تحديثها.',
        'core-system-health-shell.database_writable':
          'قاعدة البيانات قابلة للكتابة',
        'core-system-health-shell.database_writable_help':
          'لا يمكن تسجيل القراءات.',
        'core-system-health-shell.scan_root_readable':
          'دليل المراقبة قابل للقراءة',
        'core-system-health-shell.scan_root_readable_help':
          'لن تعثر عمليات المسح على شيء.',
        'core-system-health-shell.php_new_enough': 'PHP 8.1 أو أحدث',
        'core-system-health-shell.php_new_enough_help':
          'لن يعمل هذا الرمز بشكل صحيح.',
        'core-system-health-shell.sqlite': 'مشغل SQLite',
        'core-system-health-shell.sqlite_help': 'لا يمكن تخزين أي شيء إطلاقاً.',
        'core-system-health-shell.mail_available': 'mail() متاح',
        'core-system-health-shell.mail_available_help':
          'لا يمكن إرسال رسائل تنبيه من هنا.',
        'core-system-health-shell.shell_available': 'shell_exec متاح',
        'core-system-health-shell.shell_available_help':
          'العقد ومحرر crontab غير متاحين.',
        'core-system-health-shell.proc_readable': '/proc قابل للقراءة',
        'core-system-health-shell.proc_readable_help':
          'ستبقى وحدة المعالجة والذاكرة والشبكة فارغة.',
      }),
      zh: Object.freeze({
        'core-system-health-shell.title': '运行状况',
        'core-system-health-shell.help':
          '此安装能够做什么以及不能做什么。前三项通常是全新部署返回错误的原因。',
        'core-system-health-shell.php_version': 'PHP {{version}}',
        'core-system-health-shell.kernel_version': '内核 {{version}}',
        'core-system-health-shell.schema_version': '架构 v{{version}}',
        'core-system-health-shell.problem_logged_one':
          '已记录 {{count, number}} 个问题',
        'core-system-health-shell.problem_logged_many':
          '已记录 {{count, number}} 个问题',
        'core-system-health-shell.php_logs_to': 'PHP 记录到 {{path}}',
        'core-system-health-shell.this_log': '此日志：{{path}}',
        'core-system-health-shell.data_dir_writable': '数据目录可写',
        'core-system-health-shell.data_dir_writable_help':
          '无法创建或更新数据库。',
        'core-system-health-shell.database_writable': '数据库可写',
        'core-system-health-shell.database_writable_help': '无法记录读数。',
        'core-system-health-shell.scan_root_readable': '受监视目录可读',
        'core-system-health-shell.scan_root_readable_help':
          '扫描将找不到任何内容。',
        'core-system-health-shell.php_new_enough': 'PHP 8.1 或更高版本',
        'core-system-health-shell.php_new_enough_help':
          '此代码将无法正确运行。',
        'core-system-health-shell.sqlite': 'SQLite 驱动程序',
        'core-system-health-shell.sqlite_help': '根本无法存储任何内容。',
        'core-system-health-shell.mail_available': 'mail() 可用',
        'core-system-health-shell.mail_available_help':
          '无法从此处发送警报邮件。',
        'core-system-health-shell.shell_available': 'shell_exec 可用',
        'core-system-health-shell.shell_available_help':
          'Inode 和 crontab 编辑器不可用。',
        'core-system-health-shell.proc_readable': '/proc 可读',
        'core-system-health-shell.proc_readable_help':
          'CPU、内存和网络将保持为空。',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-system-health-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-system-health-shell.${key}`],
    );
  },
  render() {},
};
