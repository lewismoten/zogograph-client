/* Core card package: display-unit choices for dashboard measurements. */
window.ZogographUnitSelectorCard = {
  manifest: Object.freeze({
    id: 'core-unit-selector',
    locales: ['core-unit-selector.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-unit-selector.absolute': 'Absolute',
        'core-unit-selector.percent_free': 'Percent free',
        'core-unit-selector.change': 'Change per reading',
      }),
      es: Object.freeze({
        'core-unit-selector.absolute': 'Absoluto',
        'core-unit-selector.percent_free': 'Porcentaje libre',
        'core-unit-selector.change': 'Cambio por lectura',
      }),
      hi: Object.freeze({
        'core-unit-selector.absolute': 'निरपेक्ष',
        'core-unit-selector.percent_free': 'खाली प्रतिशत',
        'core-unit-selector.change': 'प्रति रीडिंग बदलाव',
      }),
      ar: Object.freeze({
        'core-unit-selector.absolute': 'مطلق',
        'core-unit-selector.percent_free': 'النسبة المتاحة',
        'core-unit-selector.change': 'التغير لكل قراءة',
      }),
      zh: Object.freeze({
        'core-unit-selector.absolute': '绝对值',
        'core-unit-selector.percent_free': '可用百分比',
        'core-unit-selector.change': '每次读数的变化',
      }),
    }),
  }),
  render(_input, helpers) {
    const t = (key, fallback) =>
      helpers.i18n.t(`core-unit-selector.${key}`, {}, fallback);
    return {
      absolute: t('absolute', 'Absolute'),
      percent_free: t('percent_free', 'Percent free'),
      change: t('change', 'Change per reading'),
    };
  },
};
