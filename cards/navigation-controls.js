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
        'core-navigation-controls.sections': 'Sections',
        'core-navigation-controls.search': 'Search sections',
      }),
      es: Object.freeze({
        'core-navigation-controls.skip_content': 'Saltar al contenido',
        'core-navigation-controls.menu': 'Menú',
        'core-navigation-controls.sections': 'Secciones',
        'core-navigation-controls.search': 'Buscar secciones',
      }),
      hi: Object.freeze({
        'core-navigation-controls.skip_content': 'सामग्री पर जाएँ',
        'core-navigation-controls.menu': 'मेनू',
        'core-navigation-controls.sections': 'अनुभाग',
        'core-navigation-controls.search': 'अनुभाग खोजें',
      }),
      ar: Object.freeze({
        'core-navigation-controls.skip_content': 'انتقل إلى المحتوى',
        'core-navigation-controls.menu': 'القائمة',
        'core-navigation-controls.sections': 'الأقسام',
        'core-navigation-controls.search': 'البحث في الأقسام',
      }),
      zh: Object.freeze({
        'core-navigation-controls.skip_content': '跳至内容',
        'core-navigation-controls.menu': '菜单',
        'core-navigation-controls.sections': '栏目',
        'core-navigation-controls.search': '搜索页面',
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
      sections: helpers.i18n.t(
        'core-navigation-controls.sections',
        {},
        'Sections',
      ),
      search: helpers.i18n.t(
        'core-navigation-controls.search',
        {},
        'Search sections',
      ),
    };
  },
};
