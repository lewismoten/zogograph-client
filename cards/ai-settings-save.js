/* Core card package: shared save action for every AI settings page. */
window.ZogographAiSettingsSaveCard = {
  manifest: Object.freeze({
    id: 'core-ai-settings-save',
    locales: ['core-ai-settings-save.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-settings-save.action': 'Save these settings',
        'core-ai-settings-save.all_pages':
          'Saves everything on all of these pages.',
        'core-ai-settings-save.config_read_only':
          'lib/config.php is not writable by the web server, so this page can show the settings but not change them.',
        'core-ai-settings-save.role_read_only':
          'Your signed-in role can use the assistant but cannot change its configuration. Sign in with an administrator account to save provider credentials or model settings.',
      }),
      es: Object.freeze({
        'core-ai-settings-save.action': 'Guardar estos ajustes',
        'core-ai-settings-save.all_pages':
          'Guarda todo lo incluido en estas páginas.',
        'core-ai-settings-save.config_read_only':
          'El servidor web no puede escribir lib/config.php; esta página puede mostrar la configuración, pero no cambiarla.',
        'core-ai-settings-save.role_read_only':
          'Tu rol puede usar el asistente, pero no cambiar su configuración. Inicia sesión como administrador para guardar credenciales o modelos.',
      }),
      hi: Object.freeze({
        'core-ai-settings-save.action': 'ये सेटिंग सहेजें',
        'core-ai-settings-save.all_pages':
          'इन सभी पृष्ठों की हर चीज़ सहेजता है।',
        'core-ai-settings-save.config_read_only':
          'वेब सर्वर lib/config.php में नहीं लिख सकता, इसलिए यह पृष्ठ सेटिंग्स दिखा सकता है लेकिन बदल नहीं सकता।',
        'core-ai-settings-save.role_read_only':
          'आपकी साइन-इन भूमिका सहायक का उपयोग कर सकती है, लेकिन उसका कॉन्फ़िगरेशन नहीं बदल सकती। प्रदाता क्रेडेंशियल या मॉडल सेटिंग्स सहेजने के लिए व्यवस्थापक खाते से साइन इन करें।',
      }),
      ar: Object.freeze({
        'core-ai-settings-save.action': 'احفظ هذه الإعدادات',
        'core-ai-settings-save.all_pages': 'يحفظ كل شيء في هذه الصفحات.',
        'core-ai-settings-save.config_read_only':
          'لا يستطيع خادم الويب الكتابة في lib/config.php، لذا يمكن لهذه الصفحة عرض الإعدادات لا تغييرها.',
        'core-ai-settings-save.role_read_only':
          'يمكن لدورك استخدام المساعد، لكنه لا يستطيع تغيير إعداداته. سجّل الدخول كمسؤول لحفظ بيانات المزود أو النموذج.',
      }),
      zh: Object.freeze({
        'core-ai-settings-save.action': '保存这些设置',
        'core-ai-settings-save.all_pages': '保存这些页面上的所有内容。',
        'core-ai-settings-save.config_read_only':
          'Web 服务器无法写入 lib/config.php，因此此页面可以显示设置，但不能更改设置。',
        'core-ai-settings-save.role_read_only':
          '您当前的登录角色可以使用助手，但不能更改其配置。请以管理员身份登录以保存提供商凭据或模型设置。',
      }),
    }),
  }),
  render(input, helpers) {
    const t = (key, values, fallback) => helpers.i18n.t(key, values, fallback);
    const actions = document.createElement('div');
    actions.className = 'inspect-actions';
    const save = document.createElement('button');
    save.type = 'submit';
    save.textContent = t(
      'core-ai-settings-save.action',
      {},
      'Save these settings',
    );
    save.disabled = !input.writable || !input.canChange;
    save.title = !input.writable
      ? t(
          'core-ai-settings-save.config_read_only',
          {},
          'lib/config.php is not writable by the web server, so this page can show the settings but not change them.',
        )
      : !input.canChange
        ? t(
            'core-ai-settings-save.role_read_only',
            {},
            'Your signed-in role can use the assistant but cannot change its configuration. Sign in with an administrator account to save provider credentials or model settings.',
          )
        : input.providerHint || '';
    const said = document.createElement('span');
    said.className = 'muted small';
    said.textContent = input.providerMessage
      ? input.providerMessage
      : t(
          'core-ai-settings-save.all_pages',
          {},
          'Saves everything on all of these pages.',
        );
    actions.append(save, said);
    return actions;
  },
};
