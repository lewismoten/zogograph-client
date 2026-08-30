window.ZogographEmailWriteAccessCard = {
  manifest: Object.freeze({
    id: 'core-email-write-access',
    locales: ['core-email-write-access.'],
    helpers: ['note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-email-write-access.readonly':
          'lib/config.php is not writable by the web server, so this page can show the settings but not change them. Use setup.php, or edit the file.',
      }),
      es: Object.freeze({
        'core-email-write-access.readonly':
          'lib/config.php no permite escritura al servidor web, por lo que esta página puede mostrar los ajustes pero no cambiarlos. Use setup.php o edite el archivo.',
      }),
      hi: Object.freeze({
        'core-email-write-access.readonly':
          'lib/config.php वेब सर्वर द्वारा लिखने योग्य नहीं है, इसलिए यह पृष्ठ सेटिंग दिखा सकता है लेकिन बदल नहीं सकता। setup.php उपयोग करें या फ़ाइल संपादित करें।',
      }),
      ar: Object.freeze({
        'core-email-write-access.readonly':
          'لا يمكن لخادم الويب الكتابة إلى lib/config.php، لذا يمكن لهذه الصفحة عرض الإعدادات دون تغييرها. استخدم setup.php أو حرر الملف.',
      }),
      zh: Object.freeze({
        'core-email-write-access.readonly':
          'Web 服务器无法写入 lib/config.php，因此此页面可以显示设置但无法更改它们。请使用 setup.php 或编辑该文件。',
      }),
    }),
  }),
  render(_data, helpers) {
    return helpers.note(
      helpers.i18n.t(
        'core-email-write-access.readonly',
        {},
        'lib/config.php is not writable by the web server, so this page can show the settings but not change them. Use setup.php, or edit the file.',
      ),
    );
  },
};
