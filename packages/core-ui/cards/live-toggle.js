const LiveToggleCard = {
  manifest: Object.freeze({
    id: 'core-live-toggle',
    locales: ['core-live-toggle.'],
    owner: 'core-ui',
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-live-toggle.label': 'Live' }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-ui/locales/es/live-toggle.json',
      hi: 'packages/core-ui/locales/hi/live-toggle.json',
      ar: 'packages/core-ui/locales/ar/live-toggle.json',
      zh: 'packages/core-ui/locales/zh/live-toggle.json',
    }),
  }),
  render(_input, { i18n }) {
    return i18n.t('core-live-toggle.label', {}, 'Live');
  },
};
Zogograph.cards.register(LiveToggleCard);
