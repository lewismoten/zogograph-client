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
        'core-system-encryption-shell.recovery_drill_review':
          'Recovery-drill review',
        'core-system-encryption-shell.initial_drill_only': 'Initial drill only',
        'core-system-encryption-shell.every_days': 'Every {{days}} days',
        'core-system-encryption-shell.waiting_until_present':
          'Waiting until present',
        'core-system-encryption-shell.recovery_drill': 'Recovery drill',
        'core-system-encryption-shell.recovery_stores_recorded':
          '{{drilled, number}}/{{total, number}} stores recorded',
        'core-system-encryption-shell.recovery_after_first_seal':
          'Run after the first seal',
        'core-system-encryption-shell.superseded_recovery_copies':
          'Superseded recovery copies',
        'core-system-encryption-shell.retired_recovery_copies':
          'Retired recovery copies',
        'core-system-encryption-shell.optional_remote_envelopes':
          'Optional remote envelopes',
        'core-system-encryption-shell.table_store': 'Store',
        'core-system-encryption-shell.table_verified_copies': 'Verified copies',
        'core-system-encryption-shell.table_newest_proof': 'Newest proof',
        'core-system-encryption-shell.table_last_recheck': 'Last re-check',
        'core-system-encryption-shell.not_rechecked': 'not rechecked',
        'core-system-encryption-shell.required': 'required',
        'core-system-encryption-shell.setup_configuration_status':
          'Setup configuration: {{detail}}',
        'core-system-encryption-shell.status_unavailable': 'status unavailable',
        'core-system-encryption-shell.live_sqlcipher_status':
          'Live SQLCipher option: {{detail}}',
        'core-system-encryption-shell.create_verified_copies':
          'Create verified encrypted copies',
        'core-system-encryption-shell.create_copies_title':
          'Create encrypted copies',
        'core-system-encryption-shell.copies_created':
          'Created {{count, number}} verified encrypted copies.',
        'core-system-encryption-shell.recovery_drill_title':
          'Run recovery drill',
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
        'core-system-encryption-shell.recovery_drill_review':
          'Revisión del simulacro de recuperación',
        'core-system-encryption-shell.initial_drill_only':
          'Solo simulacro inicial',
        'core-system-encryption-shell.every_days': 'Cada {{days}} días',
        'core-system-encryption-shell.waiting_until_present':
          'En espera hasta estar presentes',
        'core-system-encryption-shell.recovery_drill':
          'Simulacro de recuperación',
        'core-system-encryption-shell.recovery_stores_recorded':
          '{{drilled, number}}/{{total, number}} almacenes registrados',
        'core-system-encryption-shell.recovery_after_first_seal':
          'Ejecutar después del primer sellado',
        'core-system-encryption-shell.superseded_recovery_copies':
          'Copias de recuperación reemplazadas',
        'core-system-encryption-shell.retired_recovery_copies':
          'Copias de recuperación retiradas',
        'core-system-encryption-shell.optional_remote_envelopes':
          'Sobres remotos opcionales',
        'core-system-encryption-shell.table_store': 'Almacén',
        'core-system-encryption-shell.table_verified_copies':
          'Copias verificadas',
        'core-system-encryption-shell.table_newest_proof':
          'Prueba más reciente',
        'core-system-encryption-shell.table_last_recheck':
          'Última comprobación',
        'core-system-encryption-shell.not_rechecked': 'sin volver a comprobar',
        'core-system-encryption-shell.required': 'obligatorio',
        'core-system-encryption-shell.setup_configuration_status':
          'Configuración de instalación: {{detail}}',
        'core-system-encryption-shell.status_unavailable':
          'estado no disponible',
        'core-system-encryption-shell.live_sqlcipher_status':
          'Opción SQLCipher activa: {{detail}}',
        'core-system-encryption-shell.create_verified_copies':
          'Crear copias cifradas verificadas',
        'core-system-encryption-shell.create_copies_title':
          'Crear copias cifradas',
        'core-system-encryption-shell.copies_created':
          'Se crearon {{count, number}} copias cifradas verificadas.',
        'core-system-encryption-shell.recovery_drill_title':
          'Ejecutar prueba de recuperación',
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
        'core-system-encryption-shell.recovery_drill_review':
          'रिकवरी ड्रिल समीक्षा',
        'core-system-encryption-shell.initial_drill_only':
          'केवल प्रारंभिक ड्रिल',
        'core-system-encryption-shell.every_days': 'हर {{days}} दिन',
        'core-system-encryption-shell.waiting_until_present':
          'मौजूद होने तक प्रतीक्षा',
        'core-system-encryption-shell.recovery_drill': 'रिकवरी ड्रिल',
        'core-system-encryption-shell.recovery_stores_recorded':
          '{{drilled, number}}/{{total, number}} स्टोर दर्ज हैं',
        'core-system-encryption-shell.recovery_after_first_seal':
          'पहली सील के बाद चलाएँ',
        'core-system-encryption-shell.superseded_recovery_copies':
          'प्रतिस्थापित रिकवरी प्रतियां',
        'core-system-encryption-shell.retired_recovery_copies':
          'सेवानिवृत्त रिकवरी प्रतियां',
        'core-system-encryption-shell.optional_remote_envelopes':
          'वैकल्पिक रिमोट एनवेलप',
        'core-system-encryption-shell.table_store': 'स्टोर',
        'core-system-encryption-shell.table_verified_copies':
          'सत्यापित प्रतियां',
        'core-system-encryption-shell.table_newest_proof': 'नवीनतम प्रमाण',
        'core-system-encryption-shell.table_last_recheck': 'अंतिम पुनः जाँच',
        'core-system-encryption-shell.not_rechecked': 'पुनः जाँचा नहीं गया',
        'core-system-encryption-shell.required': 'आवश्यक',
        'core-system-encryption-shell.setup_configuration_status':
          'सेटअप कॉन्फ़िगरेशन: {{detail}}',
        'core-system-encryption-shell.status_unavailable':
          'स्थिति उपलब्ध नहीं है',
        'core-system-encryption-shell.live_sqlcipher_status':
          'लाइव SQLCipher विकल्प: {{detail}}',
        'core-system-encryption-shell.create_verified_copies':
          'सत्यापित एन्क्रिप्टेड प्रतियाँ बनाएँ',
        'core-system-encryption-shell.create_copies_title':
          'एन्क्रिप्टेड प्रतियां बनाएँ',
        'core-system-encryption-shell.copies_created':
          '{{count, number}} सत्यापित एन्क्रिप्टेड प्रतियां बनाई गईं।',
        'core-system-encryption-shell.recovery_drill_title':
          'रिकवरी जाँच चलाएँ',
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
        'core-system-encryption-shell.recovery_drill_review':
          'مراجعة تمرين الاسترداد',
        'core-system-encryption-shell.initial_drill_only': 'التمرين الأولي فقط',
        'core-system-encryption-shell.every_days': 'كل {{days}} يوم',
        'core-system-encryption-shell.waiting_until_present': 'بانتظار توفرها',
        'core-system-encryption-shell.recovery_drill': 'تمرين الاسترداد',
        'core-system-encryption-shell.recovery_stores_recorded':
          '{{drilled, number}}/{{total, number}} مخازن مسجلة',
        'core-system-encryption-shell.recovery_after_first_seal':
          'شغّل بعد أول ختم',
        'core-system-encryption-shell.superseded_recovery_copies':
          'نسخ استرداد مستبدلة',
        'core-system-encryption-shell.retired_recovery_copies':
          'نسخ استرداد متقاعدة',
        'core-system-encryption-shell.optional_remote_envelopes':
          'مغلفات بعيدة اختيارية',
        'core-system-encryption-shell.table_store': 'المخزن',
        'core-system-encryption-shell.table_verified_copies': 'نسخ متحقق منها',
        'core-system-encryption-shell.table_newest_proof': 'أحدث دليل',
        'core-system-encryption-shell.table_last_recheck': 'آخر إعادة فحص',
        'core-system-encryption-shell.not_rechecked': 'لم يُفحص مجدداً',
        'core-system-encryption-shell.required': 'مطلوب',
        'core-system-encryption-shell.setup_configuration_status':
          'إعداد التكوين: {{detail}}',
        'core-system-encryption-shell.status_unavailable': 'الحالة غير متاحة',
        'core-system-encryption-shell.live_sqlcipher_status':
          'خيار SQLCipher المباشر: {{detail}}',
        'core-system-encryption-shell.create_verified_copies':
          'أنشئ نُسخًا مشفرة متحققًا منها',
        'core-system-encryption-shell.create_copies_title': 'إنشاء نسخ مشفرة',
        'core-system-encryption-shell.copies_created':
          'تم إنشاء {{count, number}} نسخة مشفرة متحقق منها.',
        'core-system-encryption-shell.recovery_drill_title':
          'تشغيل تمرين الاسترداد',
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
        'core-system-encryption-shell.recovery_drill_review': '恢复演练审查',
        'core-system-encryption-shell.initial_drill_only': '仅初始演练',
        'core-system-encryption-shell.every_days': '每 {{days}} 天',
        'core-system-encryption-shell.waiting_until_present': '等待可用',
        'core-system-encryption-shell.recovery_drill': '恢复演练',
        'core-system-encryption-shell.recovery_stores_recorded':
          '已记录 {{drilled, number}}/{{total, number}} 个存储',
        'core-system-encryption-shell.recovery_after_first_seal':
          '首次封装后运行',
        'core-system-encryption-shell.superseded_recovery_copies':
          '已替换的恢复副本',
        'core-system-encryption-shell.retired_recovery_copies':
          '已退役的恢复副本',
        'core-system-encryption-shell.optional_remote_envelopes':
          '可选远程信封',
        'core-system-encryption-shell.table_store': '存储',
        'core-system-encryption-shell.table_verified_copies': '已验证副本',
        'core-system-encryption-shell.table_newest_proof': '最新证明',
        'core-system-encryption-shell.table_last_recheck': '上次重新检查',
        'core-system-encryption-shell.not_rechecked': '未重新检查',
        'core-system-encryption-shell.required': '必需',
        'core-system-encryption-shell.setup_configuration_status':
          '安装配置：{{detail}}',
        'core-system-encryption-shell.status_unavailable': '状态不可用',
        'core-system-encryption-shell.live_sqlcipher_status':
          '实时 SQLCipher 选项：{{detail}}',
        'core-system-encryption-shell.create_verified_copies':
          '创建已验证的加密副本',
        'core-system-encryption-shell.create_copies_title': '创建加密副本',
        'core-system-encryption-shell.copies_created':
          '已创建 {{count, number}} 个经过验证的加密副本。',
        'core-system-encryption-shell.recovery_drill_title': '运行恢复演练',
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
