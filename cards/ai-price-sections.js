/* Core card package: grouped available-model price records. */
window.ZogographAiPriceSectionsCard = {
  manifest: Object.freeze({
    id: 'core-ai-price-sections',
    locales: ['core-ai-price-sections.'],
    helpers: ['target', 'note', 'sortRows', 'renderPriceTable', 'sort', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-price-sections.empty':
          'Nothing remembered yet. Set the prices on the Model page and they are kept here against whichever provider and model are selected.',
        'core-ai-price-sections.current': 'Current prices',
        'core-ai-price-sections.upcoming': 'Upcoming prices',
        'core-ai-price-sections.expired': 'Expired prices',
      }),
      es: Object.freeze({
        'core-ai-price-sections.empty':
          'Aún no se recuerda nada. Configure los precios en la página Modelo.',
        'core-ai-price-sections.current': 'Precios actuales',
        'core-ai-price-sections.upcoming': 'Precios próximos',
        'core-ai-price-sections.expired': 'Precios vencidos',
      }),
      hi: Object.freeze({
        'core-ai-price-sections.empty':
          'अभी कुछ याद नहीं है। मॉडल पृष्ठ पर कीमतें सेट करें।',
        'core-ai-price-sections.current': 'वर्तमान कीमतें',
        'core-ai-price-sections.upcoming': 'आगामी कीमतें',
        'core-ai-price-sections.expired': 'समाप्त कीमतें',
      }),
      ar: Object.freeze({
        'core-ai-price-sections.empty':
          'لا توجد أسعار محفوظة بعد. عيّن الأسعار في صفحة النموذج.',
        'core-ai-price-sections.current': 'الأسعار الحالية',
        'core-ai-price-sections.upcoming': 'الأسعار القادمة',
        'core-ai-price-sections.expired': 'الأسعار المنتهية',
      }),
      zh: Object.freeze({
        'core-ai-price-sections.empty': '尚未保存价格。请在模型页面设置价格。',
        'core-ai-price-sections.current': '当前价格',
        'core-ai-price-sections.upcoming': '即将生效的价格',
        'core-ai-price-sections.expired': '已过期价格',
      }),
    }),
  }),
  render(input, helpers) {
    const { target, note, sortRows, renderPriceTable, sort, i18n } = helpers;
    if (!target) return;
    const t = (key, fallback) => i18n.t(key, {}, fallback);
    if (!input.rows.length) {
      target.replaceChildren(
        note(
          t(
            'core-ai-price-sections.empty',
            'Nothing remembered yet. Set the prices on the Model page and they are kept here against whichever provider and model are selected.',
          ),
        ),
      );
      return;
    }
    const parts = [],
      panels = [];
    for (const [id, key, fallback, rows] of [
      [
        'aiPricesCurrent',
        'core-ai-price-sections.current',
        'Current prices',
        input.current,
      ],
      [
        'aiPricesUpcoming',
        'core-ai-price-sections.upcoming',
        'Upcoming prices',
        input.upcoming,
      ],
      [
        'aiPricesExpired',
        'core-ai-price-sections.expired',
        'Expired prices',
        input.expired,
      ],
    ]) {
      if (!rows.length) continue;
      const heading = document.createElement('h3');
      heading.textContent = t(key, fallback);
      const holder = document.createElement('div');
      holder.id = id;
      parts.push(heading, holder);
      panels.push([id, sortRows(rows)]);
    }
    target.replaceChildren(...parts);
    for (const [id, rows] of panels) renderPriceTable(id, rows, sort);
  },
};
