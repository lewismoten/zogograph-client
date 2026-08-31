/* Core card package: persistent application-shell labels and controls. */
window.ZogographApplicationShellControlsCard = {
  manifest: Object.freeze({
    id: 'core-application-shell-controls',
    locales: ['core-application-shell-controls.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-application-shell-controls.tagline': 'clear server monitoring',
        'core-application-shell-controls.ready': 'Ready',
        'core-application-shell-controls.help': 'Help for this page',
        'core-application-shell-controls.display': 'Display',
        'core-application-shell-controls.display_title':
          'Change text size and motion',
      }),
      es: Object.freeze({
        'core-application-shell-controls.tagline':
          'monitorización clara del servidor',
        'core-application-shell-controls.ready': 'Listo',
        'core-application-shell-controls.help': 'Ayuda para esta página',
        'core-application-shell-controls.display': 'Pantalla',
        'core-application-shell-controls.display_title':
          'Cambiar el tamaño del texto y el movimiento',
      }),
      hi: Object.freeze({
        'core-application-shell-controls.tagline': 'स्पष्ट सर्वर निगरानी',
        'core-application-shell-controls.ready': 'तैयार',
        'core-application-shell-controls.help': 'इस पृष्ठ के लिए सहायता',
        'core-application-shell-controls.display': 'प्रदर्शन',
        'core-application-shell-controls.display_title':
          'पाठ आकार और गति बदलें',
      }),
      ar: Object.freeze({
        'core-application-shell-controls.tagline': 'مراقبة خوادم واضحة',
        'core-application-shell-controls.ready': 'جاهز',
        'core-application-shell-controls.help': 'مساعدة لهذه الصفحة',
        'core-application-shell-controls.display': 'العرض',
        'core-application-shell-controls.display_title':
          'تغيير حجم النص والحركة',
      }),
      zh: Object.freeze({
        'core-application-shell-controls.tagline': '清晰的服务器监控',
        'core-application-shell-controls.ready': '就绪',
        'core-application-shell-controls.help': '本页帮助',
        'core-application-shell-controls.display': '显示',
        'core-application-shell-controls.display_title': '更改文字大小和动效',
      }),
    }),
  }),
  render(_input, helpers) {
    const t = (key, fallback) =>
      helpers.i18n.t(`core-application-shell-controls.${key}`, {}, fallback);
    return {
      tagline: t('tagline', 'clear server monitoring'),
      ready: t('ready', 'Ready'),
      help: t('help', 'Help for this page'),
      display: t('display', 'Display'),
      displayTitle: t('display_title', 'Change text size and motion'),
    };
  },
};
