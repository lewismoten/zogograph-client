/* Core card package: static shell copy for the marketplace page. */
window.ZogographSystemMarketplaceShellCard = {
  manifest: Object.freeze({
    id: 'core-system-marketplace-shell',
    locales: ['core-system-marketplace-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-marketplace-shell.title': 'Marketplace',
        'core-system-marketplace-shell.help':
          'Local package manifests only. Enabling never executes package code. A few fixed, core-owned capabilities may be authorized; requested capabilities remain visible for review.',
        'core-system-marketplace-shell.search_label':
          'Search marketplace packages',
        'core-system-marketplace-shell.search_placeholder':
          'Search packages, publishers, or capabilities',
        'core-system-marketplace-shell.sign_in':
          'Sign in to view local marketplace packages.',
        'core-system-marketplace-shell.discovery_notice':
          'Packages are discovered from the configured local marketplace directory. Remote downloads and executable hooks are not supported. A package can only authorize a documented core-owned capability.',
        'core-system-marketplace-shell.ignored_manifests':
          'Ignored manifests: {{detail}}',
        'core-system-marketplace-shell.install_local': 'Install local manifest',
        'core-system-marketplace-shell.install_path_hygiene':
          'Install Path-name hygiene',
        'core-system-marketplace-shell.path_hygiene_installed':
          'Path-name hygiene installed for review. Enable it, then run a disk scan.',
        'core-system-marketplace-shell.none_found':
          'No valid local package manifests were found.',
        'core-system-marketplace-shell.no_matches':
          'No marketplace packages match that search.',
        'core-system-marketplace-shell.server_will_verify':
          'server will verify',
        'core-system-marketplace-shell.review_required': 'review required',
        'core-system-marketplace-shell.enabled': 'enabled',
        'core-system-marketplace-shell.not_enabled': 'not enabled',
        'core-system-marketplace-shell.review_guidance': 'Review guidance',
        'core-system-marketplace-shell.guidance_title': '{{name}} guidance',
        'core-system-marketplace-shell.guidance_boundary':
          'Recommendations are advisory only. This package cannot change alert settings or run code.',
        'core-system-marketplace-shell.disable': 'Disable',
        'core-system-marketplace-shell.enable': 'Enable',
        'core-system-marketplace-shell.toggle_confirmation':
          '{{verb}} {{name}}?\n\nCapabilities: {{capabilities}}',
        'core-system-marketplace-shell.disable_package': 'Disable package',
        'core-system-marketplace-shell.enable_package': 'Enable package',
        'core-system-marketplace-shell.toggle_success': '{{name}} {{status}}.',
        'core-system-marketplace-shell.history': 'History',
        'core-system-marketplace-shell.disabled': 'disabled',
        'core-system-marketplace-shell.install_dialog':
          'Install local marketplace manifest',
        'core-system-marketplace-shell.manifest_json': 'Manifest JSON',
        'core-system-marketplace-shell.manifest_help':
          'This saves only validated JSON, up to 64 KiB, in the local catalog. It cannot upload or execute code.',
        'core-system-marketplace-shell.manifest_installed':
          'Local package manifest installed for review.',
        'core-system-marketplace-shell.install_for_review':
          'Install for review',
        'core-system-marketplace-shell.history_title': '{{name}} history',
        'core-system-marketplace-shell.no_history':
          'No prior local revisions are available.',
        'core-system-marketplace-shell.saved_revision':
          '{{version}} · saved {{saved}}',
        'core-system-marketplace-shell.restore_for_review':
          'Restore for review',
        'core-system-marketplace-shell.restore_confirmation':
          'Restore {{name}} {{version}}? The restored package remains disabled until reviewed again.',
        'core-system-marketplace-shell.restore_package': 'Restore package',
        'core-system-marketplace-shell.restore_success':
          '{{name}} restored for review.',
        'core-system-marketplace-shell.history_boundary':
          'Restoring a prior revision preserves administrator control: it stays disabled until explicitly enabled again.',
        'core-system-marketplace-shell.table_package': 'Package',
        'core-system-marketplace-shell.table_publisher': 'Publisher',
        'core-system-marketplace-shell.table_summary': 'What it offers',
        'core-system-marketplace-shell.table_capabilities': 'Capabilities',
        'core-system-marketplace-shell.table_trust': 'Trust',
        'core-system-marketplace-shell.table_compatibility': 'Compatibility',
        'core-system-marketplace-shell.table_license': 'License',
        'core-system-marketplace-shell.table_status': 'Status',
      }),
      es: Object.freeze({
        'core-system-marketplace-shell.title': 'Mercado',
        'core-system-marketplace-shell.help':
          'Solo manifiestos de paquetes locales. Activarlos nunca ejecuta código de paquetes. Pueden autorizarse unas pocas capacidades fijas propiedad del núcleo; las capacidades solicitadas siguen visibles para su revisión.',
        'core-system-marketplace-shell.search_label':
          'Buscar paquetes del mercado',
        'core-system-marketplace-shell.search_placeholder':
          'Buscar paquetes, editores o capacidades',
        'core-system-marketplace-shell.sign_in':
          'Inicie sesión para ver los paquetes locales del mercado.',
        'core-system-marketplace-shell.discovery_notice':
          'Los paquetes se descubren en el directorio local del mercado configurado. No se admiten descargas remotas ni enlaces ejecutables. Un paquete solo puede autorizar una capacidad documentada y propiedad del núcleo.',
        'core-system-marketplace-shell.ignored_manifests':
          'Manifiestos ignorados: {{detail}}',
        'core-system-marketplace-shell.install_local':
          'Instalar manifiesto local',
        'core-system-marketplace-shell.install_path_hygiene':
          'Instalar higiene de nombres de ruta',
        'core-system-marketplace-shell.path_hygiene_installed':
          'La higiene de nombres de ruta se instaló para revisión. Actívela y ejecute después un análisis de disco.',
        'core-system-marketplace-shell.none_found':
          'No se encontraron manifiestos de paquetes locales válidos.',
        'core-system-marketplace-shell.no_matches':
          'Ningún paquete del mercado coincide con esa búsqueda.',
        'core-system-marketplace-shell.server_will_verify':
          'el servidor lo verificará',
        'core-system-marketplace-shell.review_required': 'requiere revisión',
        'core-system-marketplace-shell.enabled': 'activado',
        'core-system-marketplace-shell.not_enabled': 'sin activar',
        'core-system-marketplace-shell.review_guidance': 'Revisar orientación',
        'core-system-marketplace-shell.guidance_title':
          'Orientación de {{name}}',
        'core-system-marketplace-shell.guidance_boundary':
          'Las recomendaciones son solo orientativas. Este paquete no puede cambiar la configuración de alertas ni ejecutar código.',
        'core-system-marketplace-shell.disable': 'Desactivar',
        'core-system-marketplace-shell.enable': 'Activar',
        'core-system-marketplace-shell.toggle_confirmation':
          '¿{{verb}} {{name}}?\n\nCapacidades: {{capabilities}}',
        'core-system-marketplace-shell.disable_package': 'Desactivar paquete',
        'core-system-marketplace-shell.enable_package': 'Activar paquete',
        'core-system-marketplace-shell.toggle_success': '{{name}}: {{status}}.',
        'core-system-marketplace-shell.history': 'Historial',
        'core-system-marketplace-shell.disabled': 'desactivado',
        'core-system-marketplace-shell.install_dialog':
          'Instalar manifiesto local del mercado',
        'core-system-marketplace-shell.manifest_json': 'JSON del manifiesto',
        'core-system-marketplace-shell.manifest_help':
          'Esto guarda solo JSON validado, de hasta 64 KiB, en el catálogo local. No puede cargar ni ejecutar código.',
        'core-system-marketplace-shell.manifest_installed':
          'El manifiesto local del paquete se instaló para revisión.',
        'core-system-marketplace-shell.install_for_review':
          'Instalar para revisión',
        'core-system-marketplace-shell.history_title': 'Historial de {{name}}',
        'core-system-marketplace-shell.no_history':
          'No hay revisiones locales anteriores disponibles.',
        'core-system-marketplace-shell.saved_revision':
          '{{version}} · guardado {{saved}}',
        'core-system-marketplace-shell.restore_for_review':
          'Restaurar para revisión',
        'core-system-marketplace-shell.restore_confirmation':
          '¿Restaurar {{name}} {{version}}? El paquete restaurado permanecerá desactivado hasta revisarlo de nuevo.',
        'core-system-marketplace-shell.restore_package': 'Restaurar paquete',
        'core-system-marketplace-shell.restore_success':
          '{{name}} se restauró para revisión.',
        'core-system-marketplace-shell.history_boundary':
          'Restaurar una revisión anterior preserva el control del administrador: permanece desactivada hasta activarla explícitamente de nuevo.',
        'core-system-marketplace-shell.table_package': 'Paquete',
        'core-system-marketplace-shell.table_publisher': 'Editor',
        'core-system-marketplace-shell.table_summary': 'Qué ofrece',
        'core-system-marketplace-shell.table_capabilities': 'Capacidades',
        'core-system-marketplace-shell.table_trust': 'Confianza',
        'core-system-marketplace-shell.table_compatibility': 'Compatibilidad',
        'core-system-marketplace-shell.table_license': 'Licencia',
        'core-system-marketplace-shell.table_status': 'Estado',
      }),
      hi: Object.freeze({
        'core-system-marketplace-shell.title': 'मार्केटप्लेस',
        'core-system-marketplace-shell.help':
          'केवल स्थानीय पैकेज मैनिफेस्ट। सक्षम करना पैकेज कोड कभी निष्पादित नहीं करता। कुछ निश्चित, कोर-स्वामित्व वाली क्षमताएँ अधिकृत हो सकती हैं; अनुरोधित क्षमताएँ समीक्षा के लिए दिखाई देती रहती हैं।',
        'core-system-marketplace-shell.search_label':
          'मार्केटप्लेस पैकेज खोजें',
        'core-system-marketplace-shell.search_placeholder':
          'पैकेज, प्रकाशक या क्षमताएँ खोजें',
        'core-system-marketplace-shell.sign_in':
          'स्थानीय मार्केटप्लेस पैकेज देखने के लिए साइन इन करें।',
        'core-system-marketplace-shell.discovery_notice':
          'पैकेज कॉन्फ़िगर की गई स्थानीय मार्केटप्लेस निर्देशिका से खोजे जाते हैं। रिमोट डाउनलोड और निष्पादित हुक समर्थित नहीं हैं। कोई पैकेज केवल दस्तावेज़ित, कोर-स्वामित्व वाली क्षमता को अधिकृत कर सकता है।',
        'core-system-marketplace-shell.ignored_manifests':
          'अनदेखे मैनिफ़ेस्ट: {{detail}}',
        'core-system-marketplace-shell.install_local':
          'स्थानीय मैनिफ़ेस्ट स्थापित करें',
        'core-system-marketplace-shell.install_path_hygiene':
          'पथ-नाम स्वच्छता स्थापित करें',
        'core-system-marketplace-shell.path_hygiene_installed':
          'पथ-नाम स्वच्छता समीक्षा के लिए स्थापित की गई। इसे सक्षम करें, फिर डिस्क स्कैन चलाएँ।',
        'core-system-marketplace-shell.none_found':
          'कोई मान्य स्थानीय पैकेज मैनिफ़ेस्ट नहीं मिला।',
        'core-system-marketplace-shell.no_matches':
          'इस खोज से कोई मार्केटप्लेस पैकेज मेल नहीं खाता।',
        'core-system-marketplace-shell.server_will_verify':
          'सर्वर सत्यापित करेगा',
        'core-system-marketplace-shell.review_required': 'समीक्षा आवश्यक',
        'core-system-marketplace-shell.enabled': 'सक्षम',
        'core-system-marketplace-shell.not_enabled': 'सक्षम नहीं',
        'core-system-marketplace-shell.review_guidance': 'मार्गदर्शन देखें',
        'core-system-marketplace-shell.guidance_title': '{{name}} मार्गदर्शन',
        'core-system-marketplace-shell.guidance_boundary':
          'सिफारिशें केवल सलाह हैं। यह पैकेज चेतावनी सेटिंग नहीं बदल सकता या कोड नहीं चला सकता।',
        'core-system-marketplace-shell.disable': 'अक्षम करें',
        'core-system-marketplace-shell.enable': 'सक्षम करें',
        'core-system-marketplace-shell.toggle_confirmation':
          '{{verb}} {{name}}?\n\nक्षमताएँ: {{capabilities}}',
        'core-system-marketplace-shell.disable_package': 'पैकेज अक्षम करें',
        'core-system-marketplace-shell.enable_package': 'पैकेज सक्षम करें',
        'core-system-marketplace-shell.toggle_success': '{{name}} {{status}}।',
        'core-system-marketplace-shell.history': 'इतिहास',
        'core-system-marketplace-shell.disabled': 'अक्षम',
        'core-system-marketplace-shell.install_dialog':
          'स्थानीय मार्केटप्लेस मैनिफ़ेस्ट स्थापित करें',
        'core-system-marketplace-shell.manifest_json': 'मैनिफ़ेस्ट JSON',
        'core-system-marketplace-shell.manifest_help':
          'यह स्थानीय कैटलॉग में केवल 64 KiB तक का मान्य JSON सहेजता है। यह कोड अपलोड या निष्पादित नहीं कर सकता।',
        'core-system-marketplace-shell.manifest_installed':
          'स्थानीय पैकेज मैनिफ़ेस्ट समीक्षा के लिए स्थापित किया गया।',
        'core-system-marketplace-shell.install_for_review':
          'समीक्षा के लिए स्थापित करें',
        'core-system-marketplace-shell.history_title': '{{name}} इतिहास',
        'core-system-marketplace-shell.no_history':
          'कोई पिछला स्थानीय संशोधन उपलब्ध नहीं है।',
        'core-system-marketplace-shell.saved_revision':
          '{{version}} · सहेजा गया {{saved}}',
        'core-system-marketplace-shell.restore_for_review':
          'समीक्षा के लिए पुनर्स्थापित करें',
        'core-system-marketplace-shell.restore_confirmation':
          '{{name}} {{version}} पुनर्स्थापित करें? पुनर्स्थापित पैकेज फिर से समीक्षा होने तक अक्षम रहेगा।',
        'core-system-marketplace-shell.restore_package':
          'पैकेज पुनर्स्थापित करें',
        'core-system-marketplace-shell.restore_success':
          '{{name}} को समीक्षा के लिए पुनर्स्थापित किया गया।',
        'core-system-marketplace-shell.history_boundary':
          'पुराना संशोधन पुनर्स्थापित करने पर व्यवस्थापक का नियंत्रण बना रहता है: स्पष्ट रूप से फिर से सक्षम करने तक यह अक्षम रहता है।',
        'core-system-marketplace-shell.table_package': 'पैकेज',
        'core-system-marketplace-shell.table_publisher': 'प्रकाशक',
        'core-system-marketplace-shell.table_summary': 'यह क्या देता है',
        'core-system-marketplace-shell.table_capabilities': 'क्षमताएँ',
        'core-system-marketplace-shell.table_trust': 'विश्वास',
        'core-system-marketplace-shell.table_compatibility': 'अनुकूलता',
        'core-system-marketplace-shell.table_license': 'लाइसेंस',
        'core-system-marketplace-shell.table_status': 'स्थिति',
      }),
      ar: Object.freeze({
        'core-system-marketplace-shell.title': 'السوق',
        'core-system-marketplace-shell.help':
          'بيانات تعريف الحزم المحلية فقط. التمكين لا ينفذ رمز الحزمة مطلقًا. يجوز تفويض عدد قليل من القدرات الثابتة المملوكة للنواة؛ وتظل القدرات المطلوبة مرئية للمراجعة.',
        'core-system-marketplace-shell.search_label': 'ابحث في حزم السوق',
        'core-system-marketplace-shell.search_placeholder':
          'ابحث عن الحزم أو الناشرين أو القدرات',
        'core-system-marketplace-shell.sign_in':
          'سجّل الدخول لعرض حزم السوق المحلية.',
        'core-system-marketplace-shell.discovery_notice':
          'تُكتشف الحزم من دليل السوق المحلي المُعَدّ. لا تُدعم التنزيلات البعيدة أو الخطافات التنفيذية. لا تستطيع الحزمة إلا تفويض قدرة موثقة يملكها النظام الأساسي.',
        'core-system-marketplace-shell.ignored_manifests':
          'بيانات الحزم التي تم تجاهلها: {{detail}}',
        'core-system-marketplace-shell.install_local': 'ثبّت بيانًا محليًا',
        'core-system-marketplace-shell.install_path_hygiene':
          'ثبّت فحص سلامة أسماء المسارات',
        'core-system-marketplace-shell.path_hygiene_installed':
          'ثُبّت فحص سلامة أسماء المسارات للمراجعة. فعّله ثم شغّل فحص القرص.',
        'core-system-marketplace-shell.none_found':
          'لم يُعثر على بيانات حزم محلية صالحة.',
        'core-system-marketplace-shell.no_matches':
          'لا توجد حزم في السوق تطابق هذا البحث.',
        'core-system-marketplace-shell.server_will_verify': 'سيتحقق الخادم',
        'core-system-marketplace-shell.review_required': 'تتطلب مراجعة',
        'core-system-marketplace-shell.enabled': 'مفعلة',
        'core-system-marketplace-shell.not_enabled': 'غير مفعلة',
        'core-system-marketplace-shell.review_guidance': 'راجع الإرشادات',
        'core-system-marketplace-shell.guidance_title': 'إرشادات {{name}}',
        'core-system-marketplace-shell.guidance_boundary':
          'التوصيات إرشادية فقط. لا تستطيع هذه الحزمة تغيير إعدادات التنبيهات أو تشغيل التعليمات البرمجية.',
        'core-system-marketplace-shell.disable': 'عطّل',
        'core-system-marketplace-shell.enable': 'فعّل',
        'core-system-marketplace-shell.toggle_confirmation':
          'هل تريد {{verb}} {{name}}؟\n\nالقدرات: {{capabilities}}',
        'core-system-marketplace-shell.disable_package': 'عطّل الحزمة',
        'core-system-marketplace-shell.enable_package': 'فعّل الحزمة',
        'core-system-marketplace-shell.toggle_success': '{{name}} {{status}}.',
        'core-system-marketplace-shell.history': 'السجل',
        'core-system-marketplace-shell.disabled': 'معطلة',
        'core-system-marketplace-shell.install_dialog':
          'ثبّت بيان السوق المحلي',
        'core-system-marketplace-shell.manifest_json': 'JSON للبيان',
        'core-system-marketplace-shell.manifest_help':
          'يحفظ هذا JSON مُتحققًا منه فقط، حتى 64 KiB، في الفهرس المحلي. لا يمكنه رفع التعليمات البرمجية أو تشغيلها.',
        'core-system-marketplace-shell.manifest_installed':
          'ثُبّت بيان الحزمة المحلية للمراجعة.',
        'core-system-marketplace-shell.install_for_review': 'ثبّت للمراجعة',
        'core-system-marketplace-shell.history_title': 'سجل {{name}}',
        'core-system-marketplace-shell.no_history':
          'لا تتوفر مراجعات محلية سابقة.',
        'core-system-marketplace-shell.saved_revision':
          '{{version}} · حُفظ {{saved}}',
        'core-system-marketplace-shell.restore_for_review': 'استعد للمراجعة',
        'core-system-marketplace-shell.restore_confirmation':
          'هل تريد استعادة {{name}} {{version}}؟ ستبقى الحزمة المستعادة معطلة حتى تُراجع مرة أخرى.',
        'core-system-marketplace-shell.restore_package': 'استعد الحزمة',
        'core-system-marketplace-shell.restore_success':
          'استُعيدت {{name}} للمراجعة.',
        'core-system-marketplace-shell.history_boundary':
          'تُبقي استعادة مراجعة سابقة تحكم المسؤول قائمًا: تبقى الحزمة معطلة حتى تُفعّل صراحةً مرة أخرى.',
        'core-system-marketplace-shell.table_package': 'الحزمة',
        'core-system-marketplace-shell.table_publisher': 'الناشر',
        'core-system-marketplace-shell.table_summary': 'ما تقدمه',
        'core-system-marketplace-shell.table_capabilities': 'القدرات',
        'core-system-marketplace-shell.table_trust': 'الثقة',
        'core-system-marketplace-shell.table_compatibility': 'التوافق',
        'core-system-marketplace-shell.table_license': 'الترخيص',
        'core-system-marketplace-shell.table_status': 'الحالة',
      }),
      zh: Object.freeze({
        'core-system-marketplace-shell.title': '市场',
        'core-system-marketplace-shell.help':
          '仅限本地软件包清单。启用不会执行软件包代码。少数固定的核心自有功能可以获得授权；请求的功能仍会显示以供审查。',
        'core-system-marketplace-shell.search_label': '搜索市场软件包',
        'core-system-marketplace-shell.search_placeholder':
          '搜索软件包、发布者或功能',
        'core-system-marketplace-shell.sign_in': '登录以查看本地市场软件包。',
        'core-system-marketplace-shell.discovery_notice':
          '软件包从已配置的本地市场目录中发现。不支持远程下载和可执行钩子。软件包只能授权有文档说明且由核心拥有的功能。',
        'core-system-marketplace-shell.ignored_manifests':
          '已忽略的清单：{{detail}}',
        'core-system-marketplace-shell.install_local': '安装本地清单',
        'core-system-marketplace-shell.install_path_hygiene':
          '安装路径名称规范检查',
        'core-system-marketplace-shell.path_hygiene_installed':
          '已安装路径名称规范检查，等待审核。请启用它，然后运行磁盘扫描。',
        'core-system-marketplace-shell.none_found':
          '未找到有效的本地软件包清单。',
        'core-system-marketplace-shell.no_matches':
          '没有市场软件包符合该搜索条件。',
        'core-system-marketplace-shell.server_will_verify': '服务器将验证',
        'core-system-marketplace-shell.review_required': '需要审核',
        'core-system-marketplace-shell.enabled': '已启用',
        'core-system-marketplace-shell.not_enabled': '未启用',
        'core-system-marketplace-shell.review_guidance': '查看建议',
        'core-system-marketplace-shell.guidance_title': '{{name}} 建议',
        'core-system-marketplace-shell.guidance_boundary':
          '建议仅供参考。此软件包不能更改告警设置或运行代码。',
        'core-system-marketplace-shell.disable': '禁用',
        'core-system-marketplace-shell.enable': '启用',
        'core-system-marketplace-shell.toggle_confirmation':
          '要{{verb}}{{name}}吗？\n\n功能：{{capabilities}}',
        'core-system-marketplace-shell.disable_package': '禁用软件包',
        'core-system-marketplace-shell.enable_package': '启用软件包',
        'core-system-marketplace-shell.toggle_success':
          '{{name}}已{{status}}。',
        'core-system-marketplace-shell.history': '历史记录',
        'core-system-marketplace-shell.disabled': '已禁用',
        'core-system-marketplace-shell.install_dialog': '安装本地市场清单',
        'core-system-marketplace-shell.manifest_json': '清单 JSON',
        'core-system-marketplace-shell.manifest_help':
          '这只会将经过验证的 JSON（最多 64 KiB）保存到本地目录中；它不能上传或执行代码。',
        'core-system-marketplace-shell.manifest_installed':
          '已安装本地软件包清单，等待审核。',
        'core-system-marketplace-shell.install_for_review': '安装以供审核',
        'core-system-marketplace-shell.history_title': '{{name}} 历史记录',
        'core-system-marketplace-shell.no_history':
          '没有可用的先前本地修订版。',
        'core-system-marketplace-shell.saved_revision':
          '{{version}} · 保存于 {{saved}}',
        'core-system-marketplace-shell.restore_for_review': '恢复以供审核',
        'core-system-marketplace-shell.restore_confirmation':
          '要恢复 {{name}} {{version}} 吗？恢复的软件包会保持禁用状态，直到再次审核。',
        'core-system-marketplace-shell.restore_package': '恢复软件包',
        'core-system-marketplace-shell.restore_success':
          '{{name}} 已恢复以供审核。',
        'core-system-marketplace-shell.history_boundary':
          '恢复先前修订版可保留管理员控制：软件包会保持禁用，直到明确再次启用。',
        'core-system-marketplace-shell.table_package': '软件包',
        'core-system-marketplace-shell.table_publisher': '发布者',
        'core-system-marketplace-shell.table_summary': '提供的内容',
        'core-system-marketplace-shell.table_capabilities': '功能',
        'core-system-marketplace-shell.table_trust': '信任',
        'core-system-marketplace-shell.table_compatibility': '兼容性',
        'core-system-marketplace-shell.table_license': '许可证',
        'core-system-marketplace-shell.table_status': '状态',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-system-marketplace-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-system-marketplace-shell.${key}`],
    );
  },
  render() {},
};
