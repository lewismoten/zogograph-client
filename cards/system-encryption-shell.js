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
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-system-encryption-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-system-encryption-shell.${key}`],
    );
  },
  render() {},
};
