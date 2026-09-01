/* Core card package: signed-in session and recovery-code security actions. */
window.ZogographAuthSessionSecurityCard = {
  manifest: Object.freeze({
    id: 'core-auth-session-security',
    locales: ['core-auth-session-security.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-auth-session-security.sign_out': 'Sign out',
        'core-auth-session-security.sign_in': 'Sign in',
        'core-auth-session-security.end_sessions_confirm':
          'End every signed-in session? This immediately signs out every browser, including this one. Use it if a device is lost or access may be compromised.',
        'core-auth-session-security.end_sessions': 'End all sessions',
        'core-auth-session-security.sessions_ended':
          'Every signed-in session has been ended.',
        'core-auth-session-security.recovery_codes_create_title':
          'Create recovery codes',
        'core-auth-session-security.current_verification_code':
          'Current verification code',
        'core-auth-session-security.recovery_codes_create_help':
          'This replaces every previous recovery code. Store the new one-time codes offline; Zogograph will show them only once.',
        'core-auth-session-security.recovery_codes_save_title':
          'Save these recovery codes',
        'core-auth-session-security.recovery_codes_save_help':
          'Each code works once in place of the authenticator code. This is the only time Zogograph can display them; store them offline and do not put them in a password manager shared with your sign-in password.',
        'core-auth-session-security.recovery_codes_ready':
          'Eight new one-time recovery codes are ready to save.',
      }),
      es: Object.freeze({
        'core-auth-session-security.sign_out': 'Cerrar sesión',
        'core-auth-session-security.sign_in': 'Iniciar sesión',
        'core-auth-session-security.end_sessions_confirm':
          '¿Finalizar todas las sesiones iniciadas? Esto cierra inmediatamente la sesión de todos los navegadores, incluido este. Úselo si se pierde un dispositivo o puede haberse comprometido el acceso.',
        'core-auth-session-security.end_sessions': 'Cerrar todas las sesiones',
        'core-auth-session-security.sessions_ended':
          'Se han cerrado todas las sesiones iniciadas.',
        'core-auth-session-security.recovery_codes_create_title':
          'Crear códigos de recuperación',
        'core-auth-session-security.current_verification_code':
          'Código de verificación actual',
        'core-auth-session-security.recovery_codes_create_help':
          'Esto reemplaza cada código de recuperación anterior. Guarde los nuevos códigos de un solo uso sin conexión; Zogograph los mostrará una sola vez.',
        'core-auth-session-security.recovery_codes_save_title':
          'Guarde estos códigos de recuperación',
        'core-auth-session-security.recovery_codes_save_help':
          'Cada código funciona una vez en lugar del código del autenticador. Esta es la única vez que Zogograph puede mostrarlos; guárdelos sin conexión y no los ponga en un gestor de contraseñas compartido con su contraseña de inicio de sesión.',
        'core-auth-session-security.recovery_codes_ready':
          'Ocho códigos nuevos de recuperación de un solo uso están listos para guardar.',
      }),
      hi: Object.freeze({
        'core-auth-session-security.sign_out': 'साइन आउट करें',
        'core-auth-session-security.sign_in': 'साइन इन करें',
        'core-auth-session-security.end_sessions_confirm':
          'हर साइन-इन सत्र समाप्त करें? इससे इस सहित हर ब्राउज़र तुरंत साइन आउट हो जाएगा। किसी उपकरण के खो जाने या पहुँच से समझौता होने की आशंका पर इसका उपयोग करें।',
        'core-auth-session-security.end_sessions': 'सभी सत्र समाप्त करें',
        'core-auth-session-security.sessions_ended':
          'सभी साइन-इन सत्र समाप्त कर दिए गए हैं।',
        'core-auth-session-security.recovery_codes_create_title':
          'पुनर्प्राप्ति कोड बनाएँ',
        'core-auth-session-security.current_verification_code':
          'वर्तमान सत्यापन कोड',
        'core-auth-session-security.recovery_codes_create_help':
          'यह हर पिछले पुनर्प्राप्ति कोड को बदल देता है। नए एक-बार उपयोग वाले कोड ऑफ़लाइन रखें; Zogograph उन्हें केवल एक बार दिखाएगा।',
        'core-auth-session-security.recovery_codes_save_title':
          'इन पुनर्प्राप्ति कोड को सहेजें',
        'core-auth-session-security.recovery_codes_save_help':
          'हर कोड प्रमाणक कोड के स्थान पर एक बार काम करता है। यह एकमात्र समय है जब Zogograph इन्हें दिखा सकता है; इन्हें ऑफ़लाइन रखें और अपने साइन-इन पासवर्ड के साथ साझा पासवर्ड प्रबंधक में न रखें।',
        'core-auth-session-security.recovery_codes_ready':
          'आठ नए एक-बार उपयोग वाले पुनर्प्राप्ति कोड सहेजने के लिए तैयार हैं।',
      }),
      ar: Object.freeze({
        'core-auth-session-security.sign_out': 'تسجيل الخروج',
        'core-auth-session-security.sign_in': 'تسجيل الدخول',
        'core-auth-session-security.end_sessions_confirm':
          'إنهاء كل الجلسات المسجَّل دخولها؟ سيؤدي هذا فورًا إلى تسجيل خروج كل متصفح، بما فيه هذا المتصفح. استخدمه عند فقدان جهاز أو احتمال تعرّض الوصول للخطر.',
        'core-auth-session-security.end_sessions': 'إنهاء كل الجلسات',
        'core-auth-session-security.sessions_ended':
          'تم إنهاء كل الجلسات المسجل دخولها.',
        'core-auth-session-security.recovery_codes_create_title':
          'إنشاء رموز الاسترداد',
        'core-auth-session-security.current_verification_code':
          'رمز التحقق الحالي',
        'core-auth-session-security.recovery_codes_create_help':
          'يستبدل هذا كل رمز استرداد سابق. خزّن الرموز الجديدة لمرة واحدة خارج الإنترنت؛ سيعرضها Zogograph مرة واحدة فقط.',
        'core-auth-session-security.recovery_codes_save_title':
          'احفظ رموز الاسترداد هذه',
        'core-auth-session-security.recovery_codes_save_help':
          'يعمل كل رمز مرة واحدة بدلاً من رمز المصادِق. هذه هي المرة الوحيدة التي يستطيع فيها Zogograph عرضها؛ خزّنها خارج الإنترنت ولا تضعها في مدير كلمات مرور مشترك مع كلمة مرور تسجيل الدخول.',
        'core-auth-session-security.recovery_codes_ready':
          'ثمانية رموز استرداد جديدة لمرة واحدة جاهزة للحفظ.',
      }),
      zh: Object.freeze({
        'core-auth-session-security.sign_out': '退出登录',
        'core-auth-session-security.sign_in': '登录',
        'core-auth-session-security.end_sessions_confirm':
          '结束所有已登录会话？这会立即让所有浏览器（包括当前浏览器）退出登录。设备丢失或访问可能泄露时可使用此操作。',
        'core-auth-session-security.end_sessions': '结束所有会话',
        'core-auth-session-security.sessions_ended': '已结束所有已登录会话。',
        'core-auth-session-security.recovery_codes_create_title':
          '创建恢复代码',
        'core-auth-session-security.current_verification_code': '当前验证码',
        'core-auth-session-security.recovery_codes_create_help':
          '这会替换此前的所有恢复代码。请离线保存新的单次使用代码；Zogograph 只会显示一次。',
        'core-auth-session-security.recovery_codes_save_title':
          '保存这些恢复代码',
        'core-auth-session-security.recovery_codes_save_help':
          '每个代码可以代替验证器代码使用一次。这是 Zogograph 唯一能显示它们的机会；请离线保存，且不要将它们放在与登录密码共享的密码管理器中。',
        'core-auth-session-security.recovery_codes_ready':
          '八个新的单次使用恢复代码已可保存。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-auth-session-security.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
