/* Core card package: static shell copy for trusted Zogograph servers. */
window.ZogographSystemPeersShellCard = {
  manifest: Object.freeze({
    id: 'core-system-peers-shell',
    locales: ['core-system-peers-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-system-peers-shell.title': 'Trusted Zogograph servers',
        'core-system-peers-shell.admin_only':
          'Only administrators can manage trusted servers.',
        'core-system-peers-shell.help':
          'A trusted server is explicitly configured here and checked through HTTPS OpenID Connect discovery. Saving it does not share data or sign anyone in; those authorization-code steps are deliberately separate.',
        'core-system-peers-shell.capacity':
          '{{edition}} edition: controller plus {{limit, number}} remote hosts. {{used, number}} configured; {{remaining, number}} remaining.',
        'core-system-peers-shell.add': 'Add trusted server',
        'core-system-peers-shell.capacity_reached':
          'This edition has reached its remote-host limit. Remove or replace a server to free a slot.',
        'core-system-peers-shell.add_help':
          'Add a trusted remote Zogograph server.',
        'core-system-peers-shell.none':
          'No trusted servers are configured yet.',
        'core-system-peers-shell.table_server': 'Server',
        'core-system-peers-shell.table_issuer': 'Issuer',
        'core-system-peers-shell.table_client': 'Client',
        'core-system-peers-shell.table_secret': 'Secret',
        'core-system-peers-shell.table_transport': 'Transport',
        'core-system-peers-shell.not_configured': 'not configured',
        'core-system-peers-shell.configured': 'configured',
        'core-system-peers-shell.not_needed': 'not needed',
        'core-system-peers-shell.not_paired': 'not paired',
        'core-system-peers-shell.awaiting_delivery':
          'awaiting first delivery ({{elapsed}})',
        'core-system-peers-shell.paired': 'paired',
        'core-system-peers-shell.table_last_delivery': 'Last delivery',
        'core-system-peers-shell.none_yet': 'none yet',
        'core-system-peers-shell.table_connected': 'Connected',
        'core-system-peers-shell.not_connected': 'not connected',
        'core-system-peers-shell.test': 'Test',
        'core-system-peers-shell.test_oidc': 'Test OIDC discovery',
        'core-system-peers-shell.delivery_separate':
          'Remote delivery is configured separately from OIDC.',
        'core-system-peers-shell.connected_to': 'Connected to {{issuer}}.',
        'core-system-peers-shell.reconnect': 'Reconnect',
        'core-system-peers-shell.connect': 'Connect',
        'core-system-peers-shell.view_data': 'View data',
        'core-system-peers-shell.overview_title': '{{name}} overview',
        'core-system-peers-shell.collected': 'Collected',
        'core-system-peers-shell.nothing_collected': 'nothing collected',
        'core-system-peers-shell.table_disk_free': 'Disk free',
        'core-system-peers-shell.table_cpu': 'CPU',
        'core-system-peers-shell.table_memory': 'Memory',
        'core-system-peers-shell.load': 'Load',
        'core-system-peers-shell.rotate_delivery_key': 'Rotate delivery key',
        'core-system-peers-shell.rotate_confirmation':
          'Rotate {{name}}’s remote-delivery credential?\n\nThe remote stops delivering until its configuration uses the new key.',
        'core-system-peers-shell.rotated_credential_help':
          'The previous credential is revoked. Copy this complete replacement into the remote Zogograph configuration within 24 hours; it activates on first delivery and is not shown again.',
        'core-system-peers-shell.delivery_credential_rotated':
          'Remote-delivery credential rotated.',
        'core-system-peers-shell.pairing_title': 'Pair {{name}}',
        'core-system-peers-shell.pairing_fragment_help':
          'Copy this complete one-time pairing fragment into the remote Zogograph configuration within 24 hours. It activates on first delivery and is not shown again.',
        'core-system-peers-shell.oidc_peer_saved':
          'Trusted server saved. Test OIDC discovery before using the read connection.',
        'core-system-peers-shell.delivery_peer_saved':
          'Remote delivery peer saved. Copy its one-time pairing fragment to the remote Zogograph.',
        'core-system-peers-shell.field_name': 'Short name',
        'core-system-peers-shell.field_name_help':
          'Lowercase letters, numbers, hyphens, and underscores.',
        'core-system-peers-shell.field_label': 'Label',
        'core-system-peers-shell.field_label_help':
          'What people see in this dashboard.',
        'core-system-peers-shell.field_description': 'Description',
        'core-system-peers-shell.field_description_help':
          'Optional operational context, such as the service it runs.',
        'core-system-peers-shell.field_tags': 'Tags',
        'core-system-peers-shell.field_tags_help':
          'Optional comma-separated fleet tags.',
        'core-system-peers-shell.field_group': 'Group',
        'core-system-peers-shell.field_group_help':
          'Optional fleet group; organization only for now.',
        'core-system-peers-shell.field_environment': 'Environment',
        'core-system-peers-shell.field_environment_help':
          'Optional environment, such as production or staging.',
        'core-system-peers-shell.field_timezone': 'Timezone',
        'core-system-peers-shell.field_timezone_help':
          'Optional IANA timezone, such as America/New_York.',
        'core-system-peers-shell.field_maintenance': 'Maintenance note',
        'core-system-peers-shell.field_maintenance_help':
          'Optional expected-offline or maintenance context.',
        'core-system-peers-shell.field_endpoint':
          'Controller delivery endpoint',
        'core-system-peers-shell.field_endpoint_help':
          'HTTPS peer-push.php address the remote will deliver signed readings to.',
        'core-system-peers-shell.field_dashboard_url': 'Dashboard URL',
        'core-system-peers-shell.field_dashboard_url_help':
          'Optional: HTTPS base address for the OIDC read connection.',
        'core-system-peers-shell.field_redirect': 'Registered callback URL',
        'core-system-peers-shell.field_redirect_help':
          'Optional OIDC callback URL. Configure all OIDC fields together.',
        'core-system-peers-shell.field_issuer': 'OpenID issuer',
        'core-system-peers-shell.field_issuer_help':
          'Optional HTTPS issuer whose discovery document will be checked.',
        'core-system-peers-shell.field_client': 'Client ID',
        'core-system-peers-shell.field_client_help':
          'Optional OIDC client ID; needs every OIDC field.',
        'core-system-peers-shell.field_secret': 'Client secret',
        'core-system-peers-shell.field_secret_existing':
          'Leave blank to keep the existing secret.',
        'core-system-peers-shell.field_secret_help':
          'Optional; kept only in local config.php. Supply it only with every OIDC field.',
        'core-system-peers-shell.edit_trusted_server': 'Edit trusted server',
      }),
      es: Object.freeze({
        'core-system-peers-shell.title': 'Servidores Zogograph de confianza',
        'core-system-peers-shell.admin_only':
          'Solo los administradores pueden administrar servidores de confianza.',
        'core-system-peers-shell.help':
          'Un servidor de confianza se configura explícitamente aquí y se comprueba mediante descubrimiento HTTPS de OpenID Connect. Guardarlo no comparte datos ni inicia sesión a nadie; esos pasos de código de autorización son deliberadamente independientes.',
        'core-system-peers-shell.capacity':
          'Edición {{edition}}: controlador y {{limit, number}} servidores remotos. {{used, number}} configurados; quedan {{remaining, number}}.',
        'core-system-peers-shell.add': 'Añadir servidor de confianza',
        'core-system-peers-shell.capacity_reached':
          'Esta edición alcanzó el límite de servidores remotos. Quite o sustituya un servidor para liberar una plaza.',
        'core-system-peers-shell.add_help':
          'Añada un servidor remoto Zogograph de confianza.',
        'core-system-peers-shell.none':
          'Aún no hay servidores de confianza configurados.',
        'core-system-peers-shell.table_server': 'Servidor',
        'core-system-peers-shell.table_issuer': 'Emisor',
        'core-system-peers-shell.table_client': 'Cliente',
        'core-system-peers-shell.table_secret': 'Secreto',
        'core-system-peers-shell.table_transport': 'Transporte',
        'core-system-peers-shell.not_configured': 'sin configurar',
        'core-system-peers-shell.configured': 'configurado',
        'core-system-peers-shell.not_needed': 'no necesario',
        'core-system-peers-shell.not_paired': 'sin vincular',
        'core-system-peers-shell.awaiting_delivery':
          'esperando la primera entrega ({{elapsed}})',
        'core-system-peers-shell.paired': 'vinculado',
        'core-system-peers-shell.table_last_delivery': 'Última entrega',
        'core-system-peers-shell.none_yet': 'aún no hay',
        'core-system-peers-shell.table_connected': 'Conectado',
        'core-system-peers-shell.not_connected': 'sin conectar',
        'core-system-peers-shell.test': 'Probar',
        'core-system-peers-shell.test_oidc': 'Probar descubrimiento OIDC',
        'core-system-peers-shell.delivery_separate':
          'La entrega remota se configura por separado de OIDC.',
        'core-system-peers-shell.connected_to': 'Conectado a {{issuer}}.',
        'core-system-peers-shell.reconnect': 'Reconectar',
        'core-system-peers-shell.connect': 'Conectar',
        'core-system-peers-shell.view_data': 'Ver datos',
        'core-system-peers-shell.overview_title': 'Resumen de {{name}}',
        'core-system-peers-shell.collected': 'Recogido',
        'core-system-peers-shell.nothing_collected': 'no se recogió nada',
        'core-system-peers-shell.table_disk_free': 'Espacio libre en disco',
        'core-system-peers-shell.table_cpu': 'CPU',
        'core-system-peers-shell.table_memory': 'Memoria',
        'core-system-peers-shell.load': 'Carga',
        'core-system-peers-shell.rotate_delivery_key': 'Rotar clave de entrega',
        'core-system-peers-shell.rotate_confirmation':
          '¿Rotar la credencial de entrega remota de {{name}}?\n\nEl remoto dejará de entregar hasta que su configuración use la nueva clave.',
        'core-system-peers-shell.rotated_credential_help':
          'La credencial anterior se revocó. Copie este reemplazo completo en la configuración remota de Zogograph en 24 horas; se activa en la primera entrega y no volverá a mostrarse.',
        'core-system-peers-shell.delivery_credential_rotated':
          'Credencial de entrega remota rotada.',
        'core-system-peers-shell.pairing_title': 'Emparejar {{name}}',
        'core-system-peers-shell.pairing_fragment_help':
          'Copie este fragmento completo de emparejamiento de un solo uso en la configuración remota de Zogograph en 24 horas. Se activa en la primera entrega y no volverá a mostrarse.',
        'core-system-peers-shell.oidc_peer_saved':
          'Servidor de confianza guardado. Pruebe el descubrimiento OIDC antes de usar la conexión de lectura.',
        'core-system-peers-shell.delivery_peer_saved':
          'Par de entrega remota guardado. Copie su fragmento de emparejamiento de un solo uso en Zogograph remoto.',
        'core-system-peers-shell.field_name': 'Nombre corto',
        'core-system-peers-shell.field_name_help':
          'Letras minúsculas, números, guiones y guiones bajos.',
        'core-system-peers-shell.field_label': 'Etiqueta',
        'core-system-peers-shell.field_label_help':
          'Lo que la gente ve en este panel.',
        'core-system-peers-shell.field_description': 'Descripción',
        'core-system-peers-shell.field_description_help':
          'Contexto operativo opcional, como el servicio que ejecuta.',
        'core-system-peers-shell.field_tags': 'Etiquetas',
        'core-system-peers-shell.field_tags_help':
          'Etiquetas opcionales de flota separadas por comas.',
        'core-system-peers-shell.field_group': 'Grupo',
        'core-system-peers-shell.field_group_help':
          'Grupo opcional de flota; por ahora solo organiza.',
        'core-system-peers-shell.field_environment': 'Entorno',
        'core-system-peers-shell.field_environment_help':
          'Entorno opcional, como producción o preparación.',
        'core-system-peers-shell.field_timezone': 'Zona horaria',
        'core-system-peers-shell.field_timezone_help':
          'Zona horaria IANA opcional, como America/New_York.',
        'core-system-peers-shell.field_maintenance': 'Nota de mantenimiento',
        'core-system-peers-shell.field_maintenance_help':
          'Contexto opcional sobre mantenimiento o indisponibilidad esperada.',
        'core-system-peers-shell.field_endpoint':
          'Extremo de entrega del controlador',
        'core-system-peers-shell.field_endpoint_help':
          'Dirección HTTPS peer-push.php a la que el remoto enviará lecturas firmadas.',
        'core-system-peers-shell.field_dashboard_url': 'URL del panel',
        'core-system-peers-shell.field_dashboard_url_help':
          'Opcional: dirección base HTTPS para la conexión de lectura OIDC.',
        'core-system-peers-shell.field_redirect':
          'URL de devolución registrada',
        'core-system-peers-shell.field_redirect_help':
          'URL de devolución OIDC opcional. Configure todos los campos OIDC juntos.',
        'core-system-peers-shell.field_issuer': 'Emisor OpenID',
        'core-system-peers-shell.field_issuer_help':
          'Emisor HTTPS opcional cuyo documento de descubrimiento se comprobará.',
        'core-system-peers-shell.field_client': 'ID de cliente',
        'core-system-peers-shell.field_client_help':
          'ID de cliente OIDC opcional; requiere todos los campos OIDC.',
        'core-system-peers-shell.field_secret': 'Secreto de cliente',
        'core-system-peers-shell.field_secret_existing':
          'Déjelo vacío para conservar el secreto existente.',
        'core-system-peers-shell.field_secret_help':
          'Opcional; se conserva solo en config.php local. Proporciónelo únicamente con todos los campos OIDC.',
        'core-system-peers-shell.edit_trusted_server':
          'Editar servidor de confianza',
      }),
      hi: Object.freeze({
        'core-system-peers-shell.title': 'विश्वसनीय Zogograph सर्वर',
        'core-system-peers-shell.admin_only':
          'केवल व्यवस्थापक विश्वसनीय सर्वरों का प्रबंधन कर सकते हैं।',
        'core-system-peers-shell.help':
          'विश्वसनीय सर्वर यहाँ स्पष्ट रूप से कॉन्फ़िगर होता है और HTTPS OpenID Connect डिस्कवरी से जाँचा जाता है। इसे सहेजने से डेटा साझा नहीं होता और न किसी का साइन-इन होता है; वे authorization-code चरण जानबूझकर अलग हैं।',
        'core-system-peers-shell.capacity':
          '{{edition}} संस्करण: नियंत्रक और {{limit, number}} दूरस्थ होस्ट। {{used, number}} कॉन्फ़िगर हैं; {{remaining, number}} शेष हैं।',
        'core-system-peers-shell.add': 'विश्वसनीय सर्वर जोड़ें',
        'core-system-peers-shell.capacity_reached':
          'यह संस्करण दूरस्थ-होस्ट सीमा तक पहुँच गया है। स्थान खाली करने के लिए सर्वर हटाएँ या बदलें।',
        'core-system-peers-shell.add_help':
          'विश्वसनीय दूरस्थ Zogograph सर्वर जोड़ें।',
        'core-system-peers-shell.none':
          'अभी कोई विश्वसनीय सर्वर कॉन्फ़िगर नहीं है।',
        'core-system-peers-shell.table_server': 'सर्वर',
        'core-system-peers-shell.table_issuer': 'जारीकर्ता',
        'core-system-peers-shell.table_client': 'क्लाइंट',
        'core-system-peers-shell.table_secret': 'गुप्त',
        'core-system-peers-shell.table_transport': 'परिवहन',
        'core-system-peers-shell.not_configured': 'कॉन्फ़िगर नहीं',
        'core-system-peers-shell.configured': 'कॉन्फ़िगर',
        'core-system-peers-shell.not_needed': 'आवश्यक नहीं',
        'core-system-peers-shell.not_paired': 'युग्मित नहीं',
        'core-system-peers-shell.awaiting_delivery':
          'पहली डिलीवरी की प्रतीक्षा ({{elapsed}})',
        'core-system-peers-shell.paired': 'युग्मित',
        'core-system-peers-shell.table_last_delivery': 'पिछली डिलीवरी',
        'core-system-peers-shell.none_yet': 'अभी तक नहीं',
        'core-system-peers-shell.table_connected': 'कनेक्टेड',
        'core-system-peers-shell.not_connected': 'कनेक्ट नहीं',
        'core-system-peers-shell.test': 'जाँचें',
        'core-system-peers-shell.test_oidc': 'OIDC खोज जाँचें',
        'core-system-peers-shell.delivery_separate':
          'दूरस्थ डिलीवरी OIDC से अलग कॉन्फ़िगर की जाती है।',
        'core-system-peers-shell.connected_to': '{{issuer}} से कनेक्ट है।',
        'core-system-peers-shell.reconnect': 'फिर कनेक्ट करें',
        'core-system-peers-shell.connect': 'कनेक्ट करें',
        'core-system-peers-shell.view_data': 'डेटा देखें',
        'core-system-peers-shell.overview_title': '{{name}} अवलोकन',
        'core-system-peers-shell.collected': 'संग्रहित',
        'core-system-peers-shell.nothing_collected': 'कुछ भी एकत्र नहीं हुआ',
        'core-system-peers-shell.table_disk_free': 'खाली डिस्क',
        'core-system-peers-shell.table_cpu': 'CPU',
        'core-system-peers-shell.table_memory': 'मेमोरी',
        'core-system-peers-shell.load': 'लोड',
        'core-system-peers-shell.rotate_delivery_key': 'डिलीवरी कुंजी बदलें',
        'core-system-peers-shell.rotate_confirmation':
          '{{name}} का रिमोट-डिलीवरी क्रेडेंशियल बदलें?\n\nनया कुंजी कॉन्फ़िगरेशन में उपयोग होने तक रिमोट डिलीवरी बंद कर देगा।',
        'core-system-peers-shell.rotated_credential_help':
          'पिछला क्रेडेंशियल रद्द कर दिया गया है। इस पूरे प्रतिस्थापन को 24 घंटे के भीतर दूरस्थ Zogograph कॉन्फ़िगरेशन में कॉपी करें; यह पहली डिलीवरी पर सक्रिय होता है और फिर नहीं दिखाया जाता।',
        'core-system-peers-shell.delivery_credential_rotated':
          'रिमोट-डिलीवरी क्रेडेंशियल बदला गया।',
        'core-system-peers-shell.pairing_title': '{{name}} को पेयर करें',
        'core-system-peers-shell.pairing_fragment_help':
          'इस पूरे एक-बार के पेयरिंग अंश को 24 घंटे के भीतर दूरस्थ Zogograph कॉन्फ़िगरेशन में कॉपी करें। यह पहली डिलीवरी पर सक्रिय होता है और फिर नहीं दिखाया जाता।',
        'core-system-peers-shell.oidc_peer_saved':
          'विश्वसनीय सर्वर सहेजा गया। रीड कनेक्शन उपयोग करने से पहले OIDC डिस्कवरी जाँचें।',
        'core-system-peers-shell.delivery_peer_saved':
          'रिमोट डिलीवरी पीयर सहेजा गया। इसके एक-बार के पेयरिंग अंश को दूरस्थ Zogograph में कॉपी करें।',
        'core-system-peers-shell.field_name': 'संक्षिप्त नाम',
        'core-system-peers-shell.field_name_help':
          'छोटे अक्षर, संख्याएँ, हाइफ़न और अंडरस्कोर।',
        'core-system-peers-shell.field_label': 'लेबल',
        'core-system-peers-shell.field_label_help':
          'लोग इस डैशबोर्ड में इसे देखते हैं।',
        'core-system-peers-shell.field_description': 'विवरण',
        'core-system-peers-shell.field_description_help':
          'वैकल्पिक संचालन संदर्भ, जैसे इसके द्वारा चलाया जाने वाला सेवा।',
        'core-system-peers-shell.field_tags': 'टैग',
        'core-system-peers-shell.field_tags_help':
          'वैकल्पिक अल्पविराम-विभाजित फ्लीट टैग।',
        'core-system-peers-shell.field_group': 'समूह',
        'core-system-peers-shell.field_group_help':
          'वैकल्पिक फ्लीट समूह; अभी केवल संगठन के लिए।',
        'core-system-peers-shell.field_environment': 'परिवेश',
        'core-system-peers-shell.field_environment_help':
          'वैकल्पिक परिवेश, जैसे उत्पादन या स्टेजिंग।',
        'core-system-peers-shell.field_timezone': 'समय क्षेत्र',
        'core-system-peers-shell.field_timezone_help':
          'वैकल्पिक IANA समय क्षेत्र, जैसे America/New_York।',
        'core-system-peers-shell.field_maintenance': 'रखरखाव नोट',
        'core-system-peers-shell.field_maintenance_help':
          'अपेक्षित ऑफ़लाइन या रखरखाव का वैकल्पिक संदर्भ।',
        'core-system-peers-shell.field_endpoint': 'नियंत्रक डिलीवरी एंडपॉइंट',
        'core-system-peers-shell.field_endpoint_help':
          'HTTPS peer-push.php पता जिस पर रिमोट हस्ताक्षरित रीडिंग भेजेगा।',
        'core-system-peers-shell.field_dashboard_url': 'डैशबोर्ड URL',
        'core-system-peers-shell.field_dashboard_url_help':
          'वैकल्पिक: OIDC रीड कनेक्शन के लिए HTTPS आधार पता।',
        'core-system-peers-shell.field_redirect': 'पंजीकृत कॉलबैक URL',
        'core-system-peers-shell.field_redirect_help':
          'वैकल्पिक OIDC कॉलबैक URL। सभी OIDC फ़ील्ड साथ कॉन्फ़िगर करें।',
        'core-system-peers-shell.field_issuer': 'OpenID जारीकर्ता',
        'core-system-peers-shell.field_issuer_help':
          'वैकल्पिक HTTPS जारीकर्ता जिसके डिस्कवरी दस्तावेज़ की जाँच होगी।',
        'core-system-peers-shell.field_client': 'क्लाइंट ID',
        'core-system-peers-shell.field_client_help':
          'वैकल्पिक OIDC क्लाइंट ID; हर OIDC फ़ील्ड आवश्यक है।',
        'core-system-peers-shell.field_secret': 'क्लाइंट सीक्रेट',
        'core-system-peers-shell.field_secret_existing':
          'मौजूदा गुप्त रखने के लिए खाली छोड़ें।',
        'core-system-peers-shell.field_secret_help':
          'वैकल्पिक; केवल स्थानीय config.php में रखा जाता है। इसे सभी OIDC फ़ील्ड के साथ ही दें।',
        'core-system-peers-shell.edit_trusted_server':
          'विश्वसनीय सर्वर संपादित करें',
      }),
      ar: Object.freeze({
        'core-system-peers-shell.title': 'خوادم Zogograph الموثوقة',
        'core-system-peers-shell.admin_only':
          'لا يمكن إدارة الخوادم الموثوقة إلا للمسؤولين.',
        'core-system-peers-shell.help':
          'يُضبط خادم موثوق صراحة هنا ويُفحص عبر اكتشاف HTTPS OpenID Connect. حفظه لا يشارك بيانات ولا يسجل دخول أحد؛ خطوات رمز التفويض تلك منفصلة عمدًا.',
        'core-system-peers-shell.capacity':
          'إصدار {{edition}}: وحدة التحكم و{{limit, number}} مضيفًا بعيدًا. تم إعداد {{used, number}}؛ المتبقي {{remaining, number}}.',
        'core-system-peers-shell.add': 'أضف خادمًا موثوقًا',
        'core-system-peers-shell.capacity_reached':
          'بلغ هذا الإصدار حد المضيفين البعيدين. أزل خادمًا أو استبدله لتحرير مكان.',
        'core-system-peers-shell.add_help':
          'أضف خادم Zogograph بعيدًا موثوقًا.',
        'core-system-peers-shell.none': 'لا توجد خوادم موثوقة مُعَدّة بعد.',
        'core-system-peers-shell.table_server': 'الخادم',
        'core-system-peers-shell.table_issuer': 'المُصدِر',
        'core-system-peers-shell.table_client': 'العميل',
        'core-system-peers-shell.table_secret': 'السر',
        'core-system-peers-shell.table_transport': 'النقل',
        'core-system-peers-shell.not_configured': 'غير مُعَدّ',
        'core-system-peers-shell.configured': 'مُعَدّ',
        'core-system-peers-shell.not_needed': 'غير مطلوب',
        'core-system-peers-shell.not_paired': 'غير مقترن',
        'core-system-peers-shell.awaiting_delivery':
          'بانتظار أول تسليم ({{elapsed}})',
        'core-system-peers-shell.paired': 'مقترن',
        'core-system-peers-shell.table_last_delivery': 'آخر تسليم',
        'core-system-peers-shell.none_yet': 'لا شيء بعد',
        'core-system-peers-shell.table_connected': 'متصل',
        'core-system-peers-shell.not_connected': 'غير متصل',
        'core-system-peers-shell.test': 'اختبر',
        'core-system-peers-shell.test_oidc': 'اختبر اكتشاف OIDC',
        'core-system-peers-shell.delivery_separate':
          'يُضبط التسليم البعيد بشكل منفصل عن OIDC.',
        'core-system-peers-shell.connected_to': 'متصل بـ {{issuer}}.',
        'core-system-peers-shell.reconnect': 'أعد الاتصال',
        'core-system-peers-shell.connect': 'اتصل',
        'core-system-peers-shell.view_data': 'اعرض البيانات',
        'core-system-peers-shell.overview_title': 'نظرة عامة على {{name}}',
        'core-system-peers-shell.collected': 'جُمعت',
        'core-system-peers-shell.nothing_collected': 'لم يُجمع شيء',
        'core-system-peers-shell.table_disk_free': 'مساحة القرص الحرة',
        'core-system-peers-shell.table_cpu': 'المعالج',
        'core-system-peers-shell.table_memory': 'الذاكرة',
        'core-system-peers-shell.load': 'الحمل',
        'core-system-peers-shell.rotate_delivery_key': 'دوّر مفتاح التسليم',
        'core-system-peers-shell.rotate_confirmation':
          'هل تريد مداورة اعتماد التسليم البعيد لـ {{name}}؟\n\nسيتوقف البعيد عن التسليم إلى أن يستخدم إعداده المفتاح الجديد.',
        'core-system-peers-shell.rotated_credential_help':
          'أُلغي الاعتماد السابق. انسخ هذا البديل الكامل إلى إعداد Zogograph البعيد خلال 24 ساعة؛ يُفعّل عند أول تسليم ولا يظهر مرة أخرى.',
        'core-system-peers-shell.delivery_credential_rotated':
          'جرت مداورة اعتماد التسليم البعيد.',
        'core-system-peers-shell.pairing_title': 'اقرن {{name}}',
        'core-system-peers-shell.pairing_fragment_help':
          'انسخ جزء الاقتران الكامل هذا ذي الاستخدام الواحد إلى إعداد Zogograph البعيد خلال 24 ساعة. يُفعّل عند أول تسليم ولا يظهر مرة أخرى.',
        'core-system-peers-shell.oidc_peer_saved':
          'حُفظ الخادم الموثوق. اختبر اكتشاف OIDC قبل استخدام اتصال القراءة.',
        'core-system-peers-shell.delivery_peer_saved':
          'حُفظ ند التسليم البعيد. انسخ جزء الاقتران ذي الاستخدام الواحد إلى Zogograph البعيد.',
        'core-system-peers-shell.field_name': 'اسم مختصر',
        'core-system-peers-shell.field_name_help':
          'أحرف صغيرة وأرقام وشرطات وشرطات سفلية.',
        'core-system-peers-shell.field_label': 'التسمية',
        'core-system-peers-shell.field_label_help':
          'ما يراه الأشخاص في لوحة المعلومات هذه.',
        'core-system-peers-shell.field_description': 'الوصف',
        'core-system-peers-shell.field_description_help':
          'سياق تشغيلي اختياري، مثل الخدمة التي يشغلها.',
        'core-system-peers-shell.field_tags': 'الوسوم',
        'core-system-peers-shell.field_tags_help':
          'وسوم أسطول اختيارية مفصولة بفواصل.',
        'core-system-peers-shell.field_group': 'المجموعة',
        'core-system-peers-shell.field_group_help':
          'مجموعة أسطول اختيارية؛ للتنظيم فقط حاليًا.',
        'core-system-peers-shell.field_environment': 'البيئة',
        'core-system-peers-shell.field_environment_help':
          'بيئة اختيارية، مثل الإنتاج أو التجهيز.',
        'core-system-peers-shell.field_timezone': 'المنطقة الزمنية',
        'core-system-peers-shell.field_timezone_help':
          'منطقة زمنية اختيارية وفق IANA، مثل America/New_York.',
        'core-system-peers-shell.field_maintenance': 'ملاحظة صيانة',
        'core-system-peers-shell.field_maintenance_help':
          'سياق اختياري لتوقف متوقع أو صيانة.',
        'core-system-peers-shell.field_endpoint': 'نقطة تسليم وحدة التحكم',
        'core-system-peers-shell.field_endpoint_help':
          'عنوان HTTPS لـ peer-push.php الذي سترسل إليه العقدة البعيدة قراءات موقعة.',
        'core-system-peers-shell.field_dashboard_url': 'عنوان لوحة المعلومات',
        'core-system-peers-shell.field_dashboard_url_help':
          'اختياري: عنوان HTTPS الأساسي لاتصال قراءة OIDC.',
        'core-system-peers-shell.field_redirect': 'عنوان رد الاتصال المسجل',
        'core-system-peers-shell.field_redirect_help':
          'عنوان رد اتصال OIDC اختياري. اضبط كل حقول OIDC معًا.',
        'core-system-peers-shell.field_issuer': 'مُصدِر OpenID',
        'core-system-peers-shell.field_issuer_help':
          'مُصدِر HTTPS اختياري سيُفحص مستند اكتشافه.',
        'core-system-peers-shell.field_client': 'معرّف العميل',
        'core-system-peers-shell.field_client_help':
          'معرّف عميل OIDC اختياري؛ يحتاج كل حقول OIDC.',
        'core-system-peers-shell.field_secret': 'سر العميل',
        'core-system-peers-shell.field_secret_existing':
          'اتركه فارغًا للاحتفاظ بالسر الحالي.',
        'core-system-peers-shell.field_secret_help':
          'اختياري؛ يُحفظ في config.php المحلي فقط. قدّمه فقط مع كل حقول OIDC.',
        'core-system-peers-shell.edit_trusted_server': 'حرّر الخادم الموثوق',
      }),
      zh: Object.freeze({
        'core-system-peers-shell.title': '受信任的 Zogograph 服务器',
        'core-system-peers-shell.admin_only':
          '只有管理员可以管理受信任的服务器。',
        'core-system-peers-shell.help':
          '受信任服务器会在此明确配置，并通过 HTTPS OpenID Connect 发现进行检查。保存它不会共享数据或让任何人登录；这些授权代码步骤有意保持独立。',
        'core-system-peers-shell.capacity':
          '{{edition}} 版本：控制器加 {{limit, number}} 个远程主机。已配置 {{used, number}} 个；还可配置 {{remaining, number}} 个。',
        'core-system-peers-shell.add': '添加受信任服务器',
        'core-system-peers-shell.capacity_reached':
          '此版本已达到远程主机数量限制。请删除或更换服务器以释放名额。',
        'core-system-peers-shell.add_help':
          '添加受信任的远程 Zogograph 服务器。',
        'core-system-peers-shell.none': '尚未配置受信任的服务器。',
        'core-system-peers-shell.table_server': '服务器',
        'core-system-peers-shell.table_issuer': '颁发者',
        'core-system-peers-shell.table_client': '客户端',
        'core-system-peers-shell.table_secret': '密钥',
        'core-system-peers-shell.table_transport': '传输',
        'core-system-peers-shell.not_configured': '未配置',
        'core-system-peers-shell.configured': '已配置',
        'core-system-peers-shell.not_needed': '不需要',
        'core-system-peers-shell.not_paired': '未配对',
        'core-system-peers-shell.awaiting_delivery':
          '等待首次传送（{{elapsed}}）',
        'core-system-peers-shell.paired': '已配对',
        'core-system-peers-shell.table_last_delivery': '上次传送',
        'core-system-peers-shell.none_yet': '尚无',
        'core-system-peers-shell.table_connected': '已连接',
        'core-system-peers-shell.not_connected': '未连接',
        'core-system-peers-shell.test': '测试',
        'core-system-peers-shell.test_oidc': '测试 OIDC 发现',
        'core-system-peers-shell.delivery_separate':
          '远程传送与 OIDC 分开配置。',
        'core-system-peers-shell.connected_to': '已连接到 {{issuer}}。',
        'core-system-peers-shell.reconnect': '重新连接',
        'core-system-peers-shell.connect': '连接',
        'core-system-peers-shell.view_data': '查看数据',
        'core-system-peers-shell.overview_title': '{{name}} 概览',
        'core-system-peers-shell.collected': '已收集',
        'core-system-peers-shell.nothing_collected': '未收集任何内容',
        'core-system-peers-shell.table_disk_free': '可用磁盘空间',
        'core-system-peers-shell.table_cpu': 'CPU',
        'core-system-peers-shell.table_memory': '内存',
        'core-system-peers-shell.load': '负载',
        'core-system-peers-shell.rotate_delivery_key': '轮换传递密钥',
        'core-system-peers-shell.rotate_confirmation':
          '要轮换 {{name}} 的远程传递凭据吗？\n\n在远程配置使用新密钥前，远程端将停止传递。',
        'core-system-peers-shell.rotated_credential_help':
          '先前的凭据已撤销。请在 24 小时内将完整替换凭据复制到远程 Zogograph 配置；它会在首次传递时启用，之后不再显示。',
        'core-system-peers-shell.delivery_credential_rotated':
          '远程传递凭据已轮换。',
        'core-system-peers-shell.pairing_title': '配对 {{name}}',
        'core-system-peers-shell.pairing_fragment_help':
          '请在 24 小时内将完整的一次性配对片段复制到远程 Zogograph 配置。它会在首次传递时启用，之后不再显示。',
        'core-system-peers-shell.oidc_peer_saved':
          '已保存受信任服务器。请在使用读取连接前测试 OIDC 发现。',
        'core-system-peers-shell.delivery_peer_saved':
          '远程传递对端已保存。请将其一次性配对片段复制到远程 Zogograph。',
        'core-system-peers-shell.field_name': '简称',
        'core-system-peers-shell.field_name_help':
          '小写字母、数字、连字符和下划线。',
        'core-system-peers-shell.field_label': '标签',
        'core-system-peers-shell.field_label_help':
          '人们在此仪表板中看到的名称。',
        'core-system-peers-shell.field_description': '描述',
        'core-system-peers-shell.field_description_help':
          '可选的运行上下文，例如它运行的服务。',
        'core-system-peers-shell.field_tags': '标记',
        'core-system-peers-shell.field_tags_help':
          '可选的、以逗号分隔的服务器群组标记。',
        'core-system-peers-shell.field_group': '组',
        'core-system-peers-shell.field_group_help':
          '可选的服务器群组；目前仅用于组织。',
        'core-system-peers-shell.field_environment': '环境',
        'core-system-peers-shell.field_environment_help':
          '可选环境，例如生产或预发布。',
        'core-system-peers-shell.field_timezone': '时区',
        'core-system-peers-shell.field_timezone_help':
          '可选 IANA 时区，例如 America/New_York。',
        'core-system-peers-shell.field_maintenance': '维护说明',
        'core-system-peers-shell.field_maintenance_help':
          '有关预期离线或维护的可选上下文。',
        'core-system-peers-shell.field_endpoint': '控制器传送端点',
        'core-system-peers-shell.field_endpoint_help':
          '远程节点将向其传送签名读数的 HTTPS peer-push.php 地址。',
        'core-system-peers-shell.field_dashboard_url': '仪表板 URL',
        'core-system-peers-shell.field_dashboard_url_help':
          '可选：用于 OIDC 读取连接的 HTTPS 基础地址。',
        'core-system-peers-shell.field_redirect': '已注册回调 URL',
        'core-system-peers-shell.field_redirect_help':
          '可选的 OIDC 回调 URL。请一同配置所有 OIDC 字段。',
        'core-system-peers-shell.field_issuer': 'OpenID 颁发者',
        'core-system-peers-shell.field_issuer_help':
          '将检查其发现文档的可选 HTTPS 颁发者。',
        'core-system-peers-shell.field_client': '客户端 ID',
        'core-system-peers-shell.field_client_help':
          '可选的 OIDC 客户端 ID；需要填写所有 OIDC 字段。',
        'core-system-peers-shell.field_secret': '客户端密钥',
        'core-system-peers-shell.field_secret_existing': '留空以保留现有密钥。',
        'core-system-peers-shell.field_secret_help':
          '可选；只保存在本地 config.php 中。仅在填写所有 OIDC 字段时提供。',
        'core-system-peers-shell.edit_trusted_server': '编辑受信任服务器',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-system-peers-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-system-peers-shell.${key}`],
    );
  },
  render() {},
};
