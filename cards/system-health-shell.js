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
