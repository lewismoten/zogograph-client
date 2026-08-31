/* Core card package: persistent navigation accessibility controls. */
window.ZogographNavigationControlsCard = {
  manifest: Object.freeze({
    id: 'core-navigation-controls',
    locales: ['core-navigation-controls.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-navigation-controls.skip_content': 'Skip to content',
        'core-navigation-controls.menu': 'Menu',
      }),
      es: Object.freeze({
        'core-navigation-controls.skip_content': 'Saltar al contenido',
        'core-navigation-controls.menu': 'Menú',
      }),
      hi: Object.freeze({
        'core-navigation-controls.skip_content': 'सामग्री पर जाएँ',
        'core-navigation-controls.menu': 'मेनू',
      }),
      ar: Object.freeze({
        'core-navigation-controls.skip_content': 'انتقل إلى المحتوى',
        'core-navigation-controls.menu': 'القائمة',
      }),
      zh: Object.freeze({
        'core-navigation-controls.skip_content': '跳至内容',
        'core-navigation-controls.menu': '菜单',
      }),
    }),
  }),
  render(_input, helpers) {
    return {
      skip: helpers.i18n.t(
        'core-navigation-controls.skip_content',
        {},
        'Skip to content',
      ),
      menu: helpers.i18n.t('core-navigation-controls.menu', {}, 'Menu'),
    };
  },
};
