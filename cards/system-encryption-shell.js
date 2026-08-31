/* Core card package: static shell copy for data-protection settings. */
window.ZogographSystemEncryptionShellCard = {
  manifest: Object.freeze({
    id: 'core-system-encryption-shell',
    locales: ['core-system-encryption-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-encryption-shell.title': 'Data protection',
        'core-system-encryption-shell.help':
          'This page reports Zogograph evidence, not assumptions. It never shows key paths or key material. Change protection choices in the setup wizard; recovery and sealing stay CLI-only so a browser session cannot handle a private key.',
        'core-system-encryption-shell.mode_none': 'Off',
        'core-system-encryption-shell.protection_mode': 'Protection mode',
        'core-system-encryption-shell.unavailable': 'Unavailable',
        'core-system-encryption-shell.selected_stores': 'Selected stores',
        'core-system-encryption-shell.none': 'None',
        'core-system-encryption-shell.evidence_none': 'No protection evidence',
        'core-system-encryption-shell.evidence_operator_declared':
          'Operator-declared host/volume coverage (not verified)',
        'core-system-encryption-shell.evidence_verified_copies':
          'Verified encrypted-copy and recovery evidence',
        'core-system-encryption-shell.protection_evidence':
          'Protection evidence',
        'core-system-encryption-shell.needs_attention': 'Needs attention',
        'core-system-encryption-shell.mode_sealed_copies':
          'Verified encrypted copies',
        'core-system-encryption-shell.mode_volume': 'Host/VM volume protection',
        'core-system-encryption-shell.copy_schedule': 'Copy schedule',
        'core-system-encryption-shell.manual': 'manual',
        'core-system-encryption-shell.local_retention': 'Local retention',
        'core-system-encryption-shell.keep_every_copy': 'Keep every copy',
        'core-system-encryption-shell.retention_days': '{{days}} days{{tiers}}',
        'core-system-encryption-shell.setup_configuration_at_rest':
          'Setup configuration at rest',
        'core-system-encryption-shell.encrypted_verified':
          'Encrypted and verified',
        'core-system-encryption-shell.verification_failed':
          'Verification failed',
        'core-system-encryption-shell.private_permissions_only':
          'Private permissions only',
        'core-system-encryption-shell.live_sqlcipher': 'Live SQLCipher option',
        'core-system-encryption-shell.driver_detected_inactive':
          'Driver detected; provider remains inactive',
        'core-system-encryption-shell.driver_not_detected':
          'Driver not detected',
        'core-system-encryption-shell.free_space_unavailable':
          'free space unavailable',
        'core-system-encryption-shell.free_space': '{{amount}} free',
        'core-system-encryption-shell.space_next_seal': 'Space for next seal',
        'core-system-encryption-shell.space_estimated':
          '{{amount}} estimated · {{free}}',
      }),
      es: Object.freeze({
        'core-system-encryption-shell.title': 'Protección de datos',
        'core-system-encryption-shell.help':
          'Esta página informa evidencia de Zogograph, no suposiciones. Nunca muestra rutas ni material de claves. Cambie las opciones de protección en el asistente de configuración; la recuperación y el sellado siguen siendo solo de CLI para que una sesión de navegador no pueda manejar una clave privada.',
        'core-system-encryption-shell.mode_none': 'Desactivado',
        'core-system-encryption-shell.protection_mode': 'Modo de protección',
        'core-system-encryption-shell.unavailable': 'No disponible',
        'core-system-encryption-shell.selected_stores':
          'Almacenes seleccionados',
        'core-system-encryption-shell.none': 'Ninguno',
        'core-system-encryption-shell.evidence_none':
          'Sin evidencia de protección',
        'core-system-encryption-shell.evidence_operator_declared':
          'Cobertura de host/volumen declarada por el operador (sin verificar)',
        'core-system-encryption-shell.evidence_verified_copies':
          'Evidencia de copias cifradas y recuperación verificadas',
        'core-system-encryption-shell.protection_evidence':
          'Evidencia de protección',
        'core-system-encryption-shell.needs_attention': 'Necesita atención',
        'core-system-encryption-shell.mode_sealed_copies':
          'Copias cifradas verificadas',
        'core-system-encryption-shell.mode_volume':
          'Protección de volumen de host/VM',
        'core-system-encryption-shell.copy_schedule': 'Programación de copias',
        'core-system-encryption-shell.manual': 'manual',
        'core-system-encryption-shell.local_retention': 'Retención local',
        'core-system-encryption-shell.keep_every_copy':
          'Conservar todas las copias',
        'core-system-encryption-shell.retention_days': '{{days}} días{{tiers}}',
        'core-system-encryption-shell.setup_configuration_at_rest':
          'Configuración de Setup en reposo',
        'core-system-encryption-shell.encrypted_verified':
          'Cifrada y verificada',
        'core-system-encryption-shell.verification_failed':
          'Verificación fallida',
        'core-system-encryption-shell.private_permissions_only':
          'Solo permisos privados',
        'core-system-encryption-shell.live_sqlcipher':
          'Opción SQLCipher activa',
        'core-system-encryption-shell.driver_detected_inactive':
          'Controlador detectado; el proveedor sigue inactivo',
        'core-system-encryption-shell.driver_not_detected':
          'Controlador no detectado',
        'core-system-encryption-shell.free_space_unavailable':
          'espacio libre no disponible',
        'core-system-encryption-shell.free_space': '{{amount}} libres',
        'core-system-encryption-shell.space_next_seal':
          'Espacio para el próximo sellado',
        'core-system-encryption-shell.space_estimated':
          '{{amount}} estimados · {{free}}',
      }),
      hi: Object.freeze({
        'core-system-encryption-shell.title': 'डेटा सुरक्षा',
        'core-system-encryption-shell.help':
          'यह पृष्ठ अनुमान नहीं, Zogograph प्रमाण रिपोर्ट करता है। यह कभी कुंजी पथ या कुंजी सामग्री नहीं दिखाता। सेटअप विज़ार्ड में सुरक्षा विकल्प बदलें; रिकवरी और सीलिंग केवल CLI पर रहते हैं ताकि ब्राउज़र सत्र निजी कुंजी न संभाल सके।',
        'core-system-encryption-shell.mode_none': 'बंद',
        'core-system-encryption-shell.protection_mode': 'सुरक्षा मोड',
        'core-system-encryption-shell.unavailable': 'अनुपलब्ध',
        'core-system-encryption-shell.selected_stores': 'चयनित स्टोर',
        'core-system-encryption-shell.none': 'कोई नहीं',
        'core-system-encryption-shell.evidence_none': 'कोई सुरक्षा प्रमाण नहीं',
        'core-system-encryption-shell.evidence_operator_declared':
          'ऑपरेटर-घोषित होस्ट/वॉल्यूम कवरेज (सत्यापित नहीं)',
        'core-system-encryption-shell.evidence_verified_copies':
          'सत्यापित एन्क्रिप्टेड-कॉपी और रिकवरी प्रमाण',
        'core-system-encryption-shell.protection_evidence': 'सुरक्षा प्रमाण',
        'core-system-encryption-shell.needs_attention': 'ध्यान आवश्यक',
        'core-system-encryption-shell.mode_sealed_copies':
          'सत्यापित एन्क्रिप्टेड प्रतियां',
        'core-system-encryption-shell.mode_volume': 'होस्ट/VM वॉल्यूम सुरक्षा',
        'core-system-encryption-shell.copy_schedule': 'कॉपी शेड्यूल',
        'core-system-encryption-shell.manual': 'मैन्युअल',
        'core-system-encryption-shell.local_retention': 'स्थानीय प्रतिधारण',
        'core-system-encryption-shell.keep_every_copy': 'हर प्रति रखें',
        'core-system-encryption-shell.retention_days': '{{days}} दिन{{tiers}}',
        'core-system-encryption-shell.setup_configuration_at_rest':
          'स्थिर सेटअप कॉन्फ़िगरेशन',
        'core-system-encryption-shell.encrypted_verified':
          'एन्क्रिप्टेड और सत्यापित',
        'core-system-encryption-shell.verification_failed': 'सत्यापन विफल',
        'core-system-encryption-shell.private_permissions_only':
          'केवल निजी अनुमतियां',
        'core-system-encryption-shell.live_sqlcipher': 'लाइव SQLCipher विकल्प',
        'core-system-encryption-shell.driver_detected_inactive':
          'ड्राइवर मिला; प्रदाता निष्क्रिय है',
        'core-system-encryption-shell.driver_not_detected': 'ड्राइवर नहीं मिला',
        'core-system-encryption-shell.free_space_unavailable':
          'खाली स्थान उपलब्ध नहीं',
        'core-system-encryption-shell.free_space': '{{amount}} खाली',
        'core-system-encryption-shell.space_next_seal': 'अगले सील के लिए स्थान',
        'core-system-encryption-shell.space_estimated':
          '{{amount}} अनुमानित · {{free}}',
      }),
      ar: Object.freeze({
        'core-system-encryption-shell.title': 'حماية البيانات',
        'core-system-encryption-shell.help':
          'تعرض هذه الصفحة دليل Zogograph لا افتراضاته. لا تعرض مسارات المفاتيح ولا مادتها أبدًا. غير خيارات الحماية في معالج الإعداد؛ يبقى الاسترداد والختم في CLI فقط كي لا تتمكن جلسة المتصفح من معالجة مفتاح خاص.',
        'core-system-encryption-shell.mode_none': 'متوقف',
        'core-system-encryption-shell.protection_mode': 'وضع الحماية',
        'core-system-encryption-shell.unavailable': 'غير متاح',
        'core-system-encryption-shell.selected_stores': 'المخازن المحددة',
        'core-system-encryption-shell.none': 'لا شيء',
        'core-system-encryption-shell.evidence_none': 'لا يوجد دليل حماية',
        'core-system-encryption-shell.evidence_operator_declared':
          'تغطية مضيف/وحدة تخزين يعلنها المشغّل (غير متحققة)',
        'core-system-encryption-shell.evidence_verified_copies':
          'دليل نسخ مشفرة واسترداد متحقق منه',
        'core-system-encryption-shell.protection_evidence': 'دليل الحماية',
        'core-system-encryption-shell.needs_attention': 'يحتاج إلى اهتمام',
        'core-system-encryption-shell.mode_sealed_copies': 'نسخ مشفرة مُتحققة',
        'core-system-encryption-shell.mode_volume':
          'حماية وحدة تخزين المضيف/VM',
        'core-system-encryption-shell.copy_schedule': 'جدول النسخ',
        'core-system-encryption-shell.manual': 'يدوياً',
        'core-system-encryption-shell.local_retention': 'الاحتفاظ المحلي',
        'core-system-encryption-shell.keep_every_copy': 'احتفظ بكل نسخة',
        'core-system-encryption-shell.retention_days':
          '{{days}} يوماً{{tiers}}',
        'core-system-encryption-shell.setup_configuration_at_rest':
          'تكوين الإعداد في وضع السكون',
        'core-system-encryption-shell.encrypted_verified': 'مشفر ومتحقق منه',
        'core-system-encryption-shell.verification_failed': 'فشل التحقق',
        'core-system-encryption-shell.private_permissions_only':
          'أذونات خاصة فقط',
        'core-system-encryption-shell.live_sqlcipher': 'خيار SQLCipher المباشر',
        'core-system-encryption-shell.driver_detected_inactive':
          'اكتُشف برنامج التشغيل؛ يظل الموفر غير نشط',
        'core-system-encryption-shell.driver_not_detected':
          'لم يُكتشف برنامج التشغيل',
        'core-system-encryption-shell.free_space_unavailable':
          'المساحة الحرة غير متاحة',
        'core-system-encryption-shell.free_space': '{{amount}} خالية',
        'core-system-encryption-shell.space_next_seal': 'المساحة للختم التالي',
        'core-system-encryption-shell.space_estimated':
          '{{amount}} مقدرة · {{free}}',
      }),
      zh: Object.freeze({
        'core-system-encryption-shell.title': '数据保护',
        'core-system-encryption-shell.help':
          '此页报告 Zogograph 证据，而非假设。它绝不显示密钥路径或密钥材料。在设置向导中更改保护选择；恢复和封存仅限 CLI，因此浏览器会话无法处理私钥。',
        'core-system-encryption-shell.mode_none': '关闭',
        'core-system-encryption-shell.protection_mode': '保护模式',
        'core-system-encryption-shell.unavailable': '不可用',
        'core-system-encryption-shell.selected_stores': '选定的存储',
        'core-system-encryption-shell.none': '无',
        'core-system-encryption-shell.evidence_none': '没有保护证据',
        'core-system-encryption-shell.evidence_operator_declared':
          '由操作员声明的主机/卷覆盖范围（未验证）',
        'core-system-encryption-shell.evidence_verified_copies':
          '已验证的加密副本和恢复证据',
        'core-system-encryption-shell.protection_evidence': '保护证据',
        'core-system-encryption-shell.needs_attention': '需要注意',
        'core-system-encryption-shell.mode_sealed_copies': '已验证的加密副本',
        'core-system-encryption-shell.mode_volume': '主机/虚拟机卷保护',
        'core-system-encryption-shell.copy_schedule': '副本计划',
        'core-system-encryption-shell.manual': '手动',
        'core-system-encryption-shell.local_retention': '本地保留',
        'core-system-encryption-shell.keep_every_copy': '保留每个副本',
        'core-system-encryption-shell.retention_days': '{{days}} 天{{tiers}}',
        'core-system-encryption-shell.setup_configuration_at_rest':
          '静态设置配置',
        'core-system-encryption-shell.encrypted_verified': '已加密并验证',
        'core-system-encryption-shell.verification_failed': '验证失败',
        'core-system-encryption-shell.private_permissions_only': '仅私有权限',
        'core-system-encryption-shell.live_sqlcipher': '实时 SQLCipher 选项',
        'core-system-encryption-shell.driver_detected_inactive':
          '已检测到驱动程序；提供程序仍未激活',
        'core-system-encryption-shell.driver_not_detected': '未检测到驱动程序',
        'core-system-encryption-shell.free_space_unavailable': '可用空间不可用',
        'core-system-encryption-shell.free_space': '{{amount}} 可用',
        'core-system-encryption-shell.space_next_seal': '下次密封所需空间',
        'core-system-encryption-shell.space_estimated':
          '预计 {{amount}} · {{free}}',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-system-encryption-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-system-encryption-shell.${key}`],
    );
  },
  render() {},
};
