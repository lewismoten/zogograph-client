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
