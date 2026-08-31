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
        'core-system-encryption-shell.recovery_drill_unavailable':
          'Create verified encrypted copies before running a recovery drill.',
        'core-system-encryption-shell.recovery_drill_confirm':
          'Decrypt and validate the latest verified copy of every selected store now? Live data is not changed and decrypted temporary files are removed.',
        'core-system-encryption-shell.copy_pruned':
          ' Pruned {{count, number}} expired copy.',
        'core-system-encryption-shell.copies_pruned':
          ' Pruned {{count, number}} expired copies.',
        'core-system-encryption-shell.run_recovery_drill':
          'Run non-destructive recovery drill',
        'core-system-encryption-shell.add_remote_envelope':
          'Add optional remote custody envelope',
        'core-system-encryption-shell.run_remote_recovery_drill':
          'Run remote custody recovery drill',
        'core-system-encryption-shell.mode_unavailable_notice':
          'The selected protection mode is not available in this Zogograph release. Keep the current data and recovery key safe while choosing a supported package or local mode.',
        'core-system-encryption-shell.protection_disabled_notice':
          "Protection is off. Live SQLite databases and ordinary copies remain unencrypted unless your host protects their storage separately. Start with the setup wizard's Encryption section; Zogograph will not generate, upload, or store a private key from this page.",
        'core-system-encryption-shell.volume_policy_notice':
          'Zogograph records your host/VM protection policy but cannot verify it. The displayed evidence is your declaration, not a verified encrypted copy. Confirm that every selected database, SQLite WAL/SHM companion, configuration file, log, export, and backup destination is covered.',
        'core-system-encryption-shell.sealed_copies_notice':
          'Verified encrypted copies protect backup artifacts, not the running SQLite database. Run `php cron.php --seal` and then `php cron.php --recovery-drill` from the server after setup. The drill decrypts and validates current copies without touching live data; schedule seals separately. Recovery requires the private key outside the web root and an explicit CLI restore.',
        'core-system-encryption-shell.bootstrap_recovery_notice':
          'Bootstrap configuration recovery is deliberately CLI-only: `scripts/restore-config.php` authenticates and parses the sealed PHP without executing it, atomically replaces `lib/config.php`, and leaves the prior configuration as a sealed rollback artifact. Test the recovered settings before deleting that artifact.',
        'core-system-encryption-shell.superseded_copies_notice':
          'Superseded copies remain encrypted with an earlier local key after rotation. Keep that key until you have completed a replacement-key recovery drill; they are not deleted automatically.',
        'core-system-encryption-shell.custody_notice':
          'Optional remote key custody adds a separately authorized recovery envelope; it is not live-database encryption and does not upload a database or ciphertext. Before using it, configure the signed custody package, verify its endpoint and enrolled credential, review its dedicated sandbox launcher, and keep local recovery enabled. Ciphertext replica storage remains a separate future capability. Never enter a custody or relay management token, billing credential, plaintext database, or recovery key into this client.',
        'core-system-encryption-shell.recovery_drill_verified_one':
          'Recovery drill verified {{count, number}} selected store.',
        'core-system-encryption-shell.recovery_drill_verified_many':
          'Recovery drill verified {{count, number}} selected stores.',
        'core-system-encryption-shell.create_copies_confirm':
          'Create a fresh encrypted copy of every selected available store now? This does not encrypt the running database. Retention pruning may remove only expired, hash-verified copies and never the newest verified copy for a store.',
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
        'core-system-encryption-shell.recovery_drill_unavailable':
          'Cree copias cifradas verificadas antes de ejecutar una prueba de recuperación.',
        'core-system-encryption-shell.recovery_drill_confirm':
          '¿Descifrar y validar ahora la última copia verificada de cada almacén seleccionado? Los datos activos no cambian y los archivos temporales descifrados se eliminan.',
        'core-system-encryption-shell.copy_pruned':
          ' Se eliminó {{count, number}} copia vencida.',
        'core-system-encryption-shell.copies_pruned':
          ' Se eliminaron {{count, number}} copias vencidas.',
        'core-system-encryption-shell.run_recovery_drill':
          'Ejecutar prueba de recuperación no destructiva',
        'core-system-encryption-shell.add_remote_envelope':
          'Añadir sobre de custodia remota opcional',
        'core-system-encryption-shell.run_remote_recovery_drill':
          'Ejecutar prueba de recuperación de custodia remota',
        'core-system-encryption-shell.mode_unavailable_notice':
          'El modo de protección seleccionado no está disponible en esta versión de Zogograph. Mantenga seguros los datos actuales y la clave de recuperación mientras elige un paquete o modo local compatible.',
        'core-system-encryption-shell.protection_disabled_notice':
          'La protección está desactivada. Las bases de datos SQLite activas y las copias normales permanecen sin cifrar salvo que el host proteja su almacenamiento por separado. Comience en la sección Cifrado del asistente de configuración; Zogograph no generará, cargará ni almacenará una clave privada desde esta página.',
        'core-system-encryption-shell.volume_policy_notice':
          'Zogograph registra su política de protección de host/VM, pero no puede verificarla. La evidencia mostrada es su declaración, no una copia cifrada verificada. Confirme que estén cubiertos cada base de datos seleccionada, acompañante SQLite WAL/SHM, archivo de configuración, registro, exportación y destino de copia de seguridad.',
        'core-system-encryption-shell.sealed_copies_notice':
          'Las copias cifradas verificadas protegen los artefactos de respaldo, no la base de datos SQLite en ejecución. Ejecute `php cron.php --seal` y luego `php cron.php --recovery-drill` en el servidor después de la configuración. La prueba descifra y valida las copias actuales sin tocar datos activos; programe los sellados por separado. La recuperación requiere la clave privada fuera de la raíz web y una restauración explícita por CLI.',
        'core-system-encryption-shell.bootstrap_recovery_notice':
          'La recuperación de la configuración de arranque es deliberadamente solo por CLI: `scripts/restore-config.php` autentica y analiza el PHP sellado sin ejecutarlo, reemplaza atómicamente `lib/config.php` y deja la configuración anterior como artefacto de reversión sellado. Pruebe los ajustes recuperados antes de eliminar ese artefacto.',
        'core-system-encryption-shell.superseded_copies_notice':
          'Las copias sustituidas siguen cifradas con una clave local anterior tras la rotación. Conserve esa clave hasta completar una prueba de recuperación con clave de reemplazo; no se eliminan automáticamente.',
        'core-system-encryption-shell.custody_notice':
          'La custodia remota opcional de claves añade un sobre de recuperación autorizado por separado; no cifra la base de datos activa ni carga una base de datos o texto cifrado. Antes de usarla, configure el paquete de custodia firmado, verifique su punto de conexión y credencial inscrita, revise su iniciador de zona aislada y mantenga activada la recuperación local. El almacenamiento de réplicas de texto cifrado sigue siendo una capacidad independiente. Nunca introduzca en este cliente un token de administración de custodia o retransmisión, una credencial de facturación, una base de datos en texto sin cifrar ni una clave de recuperación.',
        'core-system-encryption-shell.recovery_drill_verified_one':
          'La prueba de recuperación verificó {{count, number}} almacén seleccionado.',
        'core-system-encryption-shell.recovery_drill_verified_many':
          'La prueba de recuperación verificó {{count, number}} almacenes seleccionados.',
        'core-system-encryption-shell.create_copies_confirm':
          '¿Crear ahora una copia cifrada nueva de cada almacén disponible seleccionado? Esto no cifra la base de datos en ejecución. La depuración de retención solo puede eliminar copias vencidas y verificadas por hash, y nunca la copia verificada más reciente de un almacén.',
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
        'core-system-encryption-shell.recovery_drill_unavailable':
          'रिकवरी जाँच चलाने से पहले सत्यापित एन्क्रिप्टेड प्रतियां बनाएँ।',
        'core-system-encryption-shell.recovery_drill_confirm':
          'क्या हर चुने गए स्टोर की नवीनतम सत्यापित प्रति को अभी डिक्रिप्ट और सत्यापित करें? लाइव डेटा नहीं बदलेगा और डिक्रिप्ट की गई अस्थायी फ़ाइलें हटा दी जाती हैं।',
        'core-system-encryption-shell.copy_pruned':
          ' {{count, number}} समाप्त प्रति हटाई गई।',
        'core-system-encryption-shell.copies_pruned':
          ' {{count, number}} समाप्त प्रतियां हटाई गईं।',
        'core-system-encryption-shell.run_recovery_drill':
          'बिना नुकसान वाली पुनर्प्राप्ति जाँच चलाएँ',
        'core-system-encryption-shell.add_remote_envelope':
          'वैकल्पिक दूरस्थ कस्टडी लिफ़ाफ़ा जोड़ें',
        'core-system-encryption-shell.run_remote_recovery_drill':
          'दूरस्थ कस्टडी पुनर्प्राप्ति जाँच चलाएँ',
        'core-system-encryption-shell.mode_unavailable_notice':
          'चुना गया सुरक्षा मोड इस Zogograph रिलीज़ में उपलब्ध नहीं है। समर्थित पैकेज या स्थानीय मोड चुनते समय वर्तमान डेटा और रिकवरी कुंजी सुरक्षित रखें।',
        'core-system-encryption-shell.protection_disabled_notice':
          'सुरक्षा बंद है। सक्रिय SQLite डेटाबेस और साधारण प्रतियां तब तक एन्क्रिप्ट नहीं रहतीं जब तक आपका होस्ट उनके संग्रहण को अलग से सुरक्षित न करे। सेटअप विज़ार्ड के एन्क्रिप्शन अनुभाग से शुरू करें; Zogograph इस पृष्ठ से निजी कुंजी न बनाएगा, न अपलोड करेगा, न संग्रहित करेगा।',
        'core-system-encryption-shell.volume_policy_notice':
          'Zogograph आपकी होस्ट/VM सुरक्षा नीति दर्ज करता है लेकिन उसे सत्यापित नहीं कर सकता। दिखाई गई जानकारी आपकी घोषणा है, सत्यापित एन्क्रिप्टेड प्रति नहीं। पुष्टि करें कि प्रत्येक चुना डेटाबेस, SQLite WAL/SHM सहयोगी, कॉन्फ़िगरेशन फ़ाइल, लॉग, निर्यात और बैकअप गंतव्य कवर है।',
        'core-system-encryption-shell.sealed_copies_notice':
          'सत्यापित एन्क्रिप्टेड प्रतियाँ बैकअप आर्टिफैक्ट की सुरक्षा करती हैं, चल रहे SQLite डेटाबेस की नहीं। सेटअप के बाद सर्वर से `php cron.php --seal` और फिर `php cron.php --recovery-drill` चलाएँ। ड्रिल लाइव डेटा को छुए बिना वर्तमान प्रतियों को डिक्रिप्ट और सत्यापित करती है; सील को अलग से शेड्यूल करें। पुनर्प्राप्ति के लिए वेब रूट के बाहर निजी कुंजी और स्पष्ट CLI रिस्टोर आवश्यक है।',
        'core-system-encryption-shell.bootstrap_recovery_notice':
          'बूटस्ट्रैप कॉन्फ़िगरेशन पुनर्प्राप्ति जानबूझकर केवल CLI है: `scripts/restore-config.php` सील किए गए PHP को निष्पादित किए बिना प्रमाणित और पार्स करता है, `lib/config.php` को परमाणु रूप से बदलता है और पिछली कॉन्फ़िगरेशन को सील किए रोलबैक आर्टिफैक्ट के रूप में रखता है। उस आर्टिफैक्ट को हटाने से पहले पुनर्प्राप्त सेटिंग्स जाँचें।',
        'core-system-encryption-shell.superseded_copies_notice':
          'रोटेशन के बाद बदली गई प्रतियाँ पुराने स्थानीय कुंजी से एन्क्रिप्टेड रहती हैं। प्रतिस्थापन-कुंजी पुनर्प्राप्ति ड्रिल पूरी होने तक उस कुंजी को रखें; वे अपने-आप नहीं हटतीं।',
        'core-system-encryption-shell.custody_notice':
          'वैकल्पिक रिमोट कुंजी अभिरक्षा अलग से अधिकृत पुनर्प्राप्ति आवरण जोड़ती है; यह लाइव डेटाबेस एन्क्रिप्शन नहीं है और डेटाबेस या सिफरटेक्स्ट अपलोड नहीं करती। उपयोग से पहले हस्ताक्षरित अभिरक्षा पैकेज कॉन्फ़िगर करें, उसके एंडपॉइंट और नामांकित क्रेडेंशियल सत्यापित करें, उसके समर्पित सैंडबॉक्स लॉन्चर की समीक्षा करें और स्थानीय पुनर्प्राप्ति चालू रखें। सिफरटेक्स्ट प्रतिकृति भंडारण एक अलग क्षमता है। इस क्लाइंट में अभिरक्षा या रिले प्रबंधन टोकन, बिलिंग क्रेडेंशियल, सादा डेटाबेस या पुनर्प्राप्ति कुंजी कभी दर्ज न करें।',
        'core-system-encryption-shell.recovery_drill_verified_one':
          'रिकवरी जाँच ने {{count, number}} चुने गए स्टोर को सत्यापित किया।',
        'core-system-encryption-shell.recovery_drill_verified_many':
          'रिकवरी जाँच ने {{count, number}} चुने गए स्टोरों को सत्यापित किया।',
        'core-system-encryption-shell.create_copies_confirm':
          'क्या हर चुने गए उपलब्ध स्टोर की नई एन्क्रिप्टेड प्रति अभी बनाई जाए? इससे चल रहा डेटाबेस एन्क्रिप्ट नहीं होता। रिटेंशन छँटाई केवल समाप्त और हैश-सत्यापित प्रतियों को हटा सकती है, और किसी स्टोर की नवीनतम सत्यापित प्रति को कभी नहीं हटाती।',
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
        'core-system-encryption-shell.recovery_drill_unavailable':
          'أنشئ نسخاً مشفرة متحققاً منها قبل تشغيل تمرين استرداد.',
        'core-system-encryption-shell.recovery_drill_confirm':
          'هل تريد فك تشفير أحدث نسخة متحققة من كل مخزن محدد والتحقق منها الآن؟ لا تتغير البيانات الحية وتُزال الملفات المؤقتة المفكوكة.',
        'core-system-encryption-shell.copy_pruned':
          ' أزيلت {{count, number}} نسخة منتهية.',
        'core-system-encryption-shell.copies_pruned':
          ' أزيلت {{count, number}} نسخ منتهية.',
        'core-system-encryption-shell.run_recovery_drill':
          'شغّل تمرين استرداد غير هدّام',
        'core-system-encryption-shell.add_remote_envelope':
          'أضف غلاف حفظ بعيد اختياري',
        'core-system-encryption-shell.run_remote_recovery_drill':
          'شغّل تمرين استرداد الحفظ البعيد',
        'core-system-encryption-shell.mode_unavailable_notice':
          'وضع الحماية المحدد غير متاح في إصدار Zogograph هذا. حافظ على أمان البيانات الحالية ومفتاح الاسترداد أثناء اختيار حزمة أو وضع محلي مدعوم.',
        'core-system-encryption-shell.protection_disabled_notice':
          'الحماية متوقفة. تبقى قواعد بيانات SQLite النشطة والنسخ العادية غير مشفرة ما لم يحمِ المضيف تخزينها بشكل منفصل. ابدأ من قسم التشفير في معالج الإعداد؛ لن ينشئ Zogograph مفتاحاً خاصاً أو يرفعه أو يخزنه من هذه الصفحة.',
        'core-system-encryption-shell.volume_policy_notice':
          'يسجل Zogograph سياسة حماية المضيف/VM الخاصة بك لكنه لا يستطيع التحقق منها. الدليل المعروض هو إقرارك وليس نسخة مشفرة متحققة. أكد أن كل قاعدة بيانات محددة، وملف SQLite WAL/SHM المرافق، وملف التكوين، والسجل، والتصدير، ووجهة النسخ الاحتياطي مشمولة.',
        'core-system-encryption-shell.sealed_copies_notice':
          'تحمي النسخ المشفرة المتحقق منها عناصر النسخ الاحتياطي، لا قاعدة بيانات SQLite العاملة. شغّل `php cron.php --seal` ثم `php cron.php --recovery-drill` من الخادم بعد الإعداد. يفك التمرين تشفير النسخ الحالية ويتحقق منها دون لمس البيانات الحية؛ جدولة الختم منفصلة. يتطلب الاسترداد المفتاح الخاص خارج جذر الويب واستعادة CLI صريحة.',
        'core-system-encryption-shell.bootstrap_recovery_notice':
          'استرداد إعدادات التمهيد مقصور عمدًا على CLI: يتحقق `scripts/restore-config.php` من PHP المختوم ويحلله دون تنفيذه، ويستبدل `lib/config.php` ذريًا، ويترك الإعداد السابق كعنصر تراجع مختوم. اختبر الإعدادات المستردة قبل حذف ذلك العنصر.',
        'core-system-encryption-shell.superseded_copies_notice':
          'تبقى النسخ المستبدلة مشفرة بمفتاح محلي أقدم بعد التدوير. احتفظ بذلك المفتاح حتى تكمل تمرين استرداد بمفتاح بديل؛ لا تُحذف تلقائيًا.',
        'core-system-encryption-shell.custody_notice':
          'تضيف حضانة المفاتيح البعيدة الاختيارية غلاف استرداد مخولًا بشكل منفصل؛ وليست تشفيرًا لقاعدة البيانات العاملة ولا ترفع قاعدة بيانات أو نصًا مشفرًا. قبل استخدامها، اضبط حزمة الحضانة الموقعة وتحقق من نقطة نهايتها وبيانات اعتمادها المسجلة وراجع مشغل صندوق الرمل المخصص لها وأبق الاسترداد المحلي مفعلاً. يظل تخزين نسخة النص المشفر قدرة منفصلة. لا تدخل أبدًا في هذا العميل رمز إدارة الحضانة أو الترحيل أو بيانات اعتماد الفوترة أو قاعدة بيانات بنص عادي أو مفتاح استرداد.',
        'core-system-encryption-shell.recovery_drill_verified_one':
          'تحقق تمرين الاسترداد من {{count, number}} مخزن محدد.',
        'core-system-encryption-shell.recovery_drill_verified_many':
          'تحقق تمرين الاسترداد من {{count, number}} مخازن محددة.',
        'core-system-encryption-shell.create_copies_confirm':
          'هل تريد إنشاء نسخة مشفرة جديدة الآن من كل مخزن متاح محدد؟ لا يؤدي ذلك إلى تشفير قاعدة البيانات العاملة. لا يجوز لتنقية الاحتفاظ إزالة سوى النسخ المنتهية والمتحقق من تجزئتها، ولن تزيل أحدث نسخة متحققة لأي مخزن.',
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
        'core-system-encryption-shell.recovery_drill_unavailable':
          '请先创建经过验证的加密副本，再运行恢复演练。',
        'core-system-encryption-shell.recovery_drill_confirm':
          '现在要解密并验证每个选定存储的最新已验证副本吗？不会更改实时数据，并且会移除已解密的临时文件。',
        'core-system-encryption-shell.copy_pruned':
          '已清理 {{count, number}} 个过期副本。',
        'core-system-encryption-shell.copies_pruned':
          '已清理 {{count, number}} 个过期副本。',
        'core-system-encryption-shell.run_recovery_drill':
          '运行非破坏性恢复演练',
        'core-system-encryption-shell.add_remote_envelope':
          '添加可选远程托管信封',
        'core-system-encryption-shell.run_remote_recovery_drill':
          '运行远程托管恢复演练',
        'core-system-encryption-shell.mode_unavailable_notice':
          '此 Zogograph 版本中无法使用所选保护模式。选择受支持的软件包或本地模式时，请妥善保管当前数据和恢复密钥。',
        'core-system-encryption-shell.protection_disabled_notice':
          '保护已关闭。除非主机单独保护其存储，否则活动 SQLite 数据库和普通副本仍未加密。请从设置向导的“加密”部分开始；Zogograph 不会在此页面生成、上传或存储私钥。',
        'core-system-encryption-shell.volume_policy_notice':
          'Zogograph 会记录您的主机/虚拟机保护策略，但无法验证它。显示的证据是您的声明，而不是经过验证的加密副本。请确认每个选定数据库、SQLite WAL/SHM 配套文件、配置文件、日志、导出文件和备份目标均已覆盖。',
        'core-system-encryption-shell.sealed_copies_notice':
          '已验证的加密副本保护的是备份工件，而不是正在运行的 SQLite 数据库。完成设置后，请在服务器上运行 `php cron.php --seal`，再运行 `php cron.php --recovery-drill`。演练会解密并验证当前副本而不触及实时数据；请单独安排封存任务。恢复需要位于 Web 根目录之外的私钥，以及明确的 CLI 还原操作。',
        'core-system-encryption-shell.bootstrap_recovery_notice':
          '引导配置恢复被刻意限制为仅 CLI：`scripts/restore-config.php` 会对封存的 PHP 进行验证和解析而不执行它，以原子方式替换 `lib/config.php`，并将原配置保留为封存的回滚工件。删除该工件前请测试恢复后的设置。',
        'core-system-encryption-shell.superseded_copies_notice':
          '轮换后，被取代的副本仍使用较早的本地密钥加密。完成替换密钥恢复演练前请保留该密钥；它们不会自动删除。',
        'core-system-encryption-shell.custody_notice':
          '可选的远程密钥托管会增加一个单独授权的恢复信封；它不是活动数据库加密，也不会上传数据库或密文。使用前，请配置已签名的托管软件包，验证其端点和注册凭据，审查专用沙盒启动器，并保留本地恢复功能。密文副本存储仍是一项独立能力。切勿在此客户端输入托管或中继管理令牌、计费凭据、数据库明文或恢复密钥。',
        'core-system-encryption-shell.recovery_drill_verified_one':
          '恢复演练验证了 {{count, number}} 个选定存储。',
        'core-system-encryption-shell.recovery_drill_verified_many':
          '恢复演练验证了 {{count, number}} 个选定存储。',
        'core-system-encryption-shell.create_copies_confirm':
          '现在为每个选定的可用存储创建新的加密副本吗？这不会加密正在运行的数据库。保留期清理只能删除已过期且通过哈希验证的副本，绝不会删除某个存储的最新已验证副本。',
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
