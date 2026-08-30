/* Core card package: writable-state and readiness notices for AI settings. */
window.ZogographAiSettingsStatusCard = {
  manifest: Object.freeze({
    id: 'core-ai-settings-status',
    locales: ['core-ai-settings-status.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-settings-status.config_read_only':
          'lib/config.php is not writable by the web server, so this page can show the settings but not change them.',
        'core-ai-settings-status.role_read_only':
          'Your signed-in role can use the assistant but cannot change its configuration. Sign in with an administrator account to save provider credentials or model settings.',
        'core-ai-settings-status.not_ready': 'Not ready: {{reason}}',
      }),
      es: Object.freeze({
        'core-ai-settings-status.config_read_only':
          'El servidor web no puede escribir lib/config.php; esta página puede mostrar la configuración, pero no cambiarla.',
        'core-ai-settings-status.role_read_only':
          'Tu rol puede usar el asistente, pero no cambiar su configuración. Inicia sesión como administrador para guardar credenciales o modelos.',
        'core-ai-settings-status.not_ready': 'No está listo: {{reason}}',
      }),
      hi: Object.freeze({
        'core-ai-settings-status.config_read_only':
          'वेब सर्वर lib/config.php में नहीं लिख सकता, इसलिए यह पृष्ठ सेटिंग दिखा सकता है पर बदल नहीं सकता।',
        'core-ai-settings-status.role_read_only':
          'आपकी भूमिका सहायक का उपयोग कर सकती है, लेकिन उसका विन्यास नहीं बदल सकती। क्रेडेंशियल या मॉडल सहेजने के लिए प्रशासक के रूप में साइन इन करें।',
        'core-ai-settings-status.not_ready': 'तैयार नहीं: {{reason}}',
      }),
      ar: Object.freeze({
        'core-ai-settings-status.config_read_only':
          'لا يستطيع خادم الويب الكتابة في lib/config.php، لذا يمكن لهذه الصفحة عرض الإعدادات لا تغييرها.',
        'core-ai-settings-status.role_read_only':
          'يمكن لدورك استخدام المساعد، لكنه لا يستطيع تغيير إعداداته. سجّل الدخول كمسؤول لحفظ بيانات المزود أو النموذج.',
        'core-ai-settings-status.not_ready': 'غير جاهز: {{reason}}',
      }),
      zh: Object.freeze({
        'core-ai-settings-status.config_read_only':
          'Web 服务器无法写入 lib/config.php，因此此页面可以显示设置，但不能更改设置。',
        'core-ai-settings-status.role_read_only':
          '您当前的登录角色可以使用助手，但不能更改其配置。请以管理员身份登录以保存提供商凭据或模型设置。',
        'core-ai-settings-status.not_ready': '尚未就绪：{{reason}}',
      }),
    }),
  }),
  render(input, helpers) {
    const { note, i18n } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const notices = [];
    if (!input.writable) {
      notices.push(
        note(
          t(
            'core-ai-settings-status.config_read_only',
            {},
            'lib/config.php is not writable by the web server, so this page can show the settings but not change them.',
          ),
        ),
      );
    } else if (!input.can_change) {
      notices.push(
        note(
          t(
            'core-ai-settings-status.role_read_only',
            {},
            'Your signed-in role can use the assistant but cannot change its configuration. Sign in with an administrator account to save provider credentials or model settings.',
          ),
        ),
      );
    }
    if (input.enabled && !input.ready) {
      notices.push(
        note(
          t(
            'core-ai-settings-status.not_ready',
            { reason: input.why },
            'Not ready: {{reason}}',
          ),
        ),
      );
    }
    return notices;
  },
};
