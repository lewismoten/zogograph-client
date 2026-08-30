/* Core card package: reaction-image search provider connection and quota. */
window.ZogographAiImageSearchCard = {
  manifest: Object.freeze({
    id: 'core-ai-image-search',
    locales: ['core-ai-image-search.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-image-search.service': 'Search service',
        'core-ai-image-search.key': 'Search key',
        'core-ai-image-search.endpoint': 'Endpoint',
        'core-ai-image-search.rate': 'Searches per hour',
      }),
      es: Object.freeze({
        'core-ai-image-search.service': 'Servicio de búsqueda',
        'core-ai-image-search.key': 'Clave de búsqueda',
        'core-ai-image-search.endpoint': 'Punto de conexión',
        'core-ai-image-search.rate': 'Búsquedas por hora',
      }),
      hi: Object.freeze({
        'core-ai-image-search.service': 'खोज सेवा',
        'core-ai-image-search.key': 'खोज कुंजी',
        'core-ai-image-search.endpoint': 'एंडपॉइंट',
        'core-ai-image-search.rate': 'प्रति घंटा खोजें',
      }),
      ar: Object.freeze({
        'core-ai-image-search.service': 'خدمة البحث',
        'core-ai-image-search.key': 'مفتاح البحث',
        'core-ai-image-search.endpoint': 'نقطة النهاية',
        'core-ai-image-search.rate': 'عمليات البحث في الساعة',
      }),
      zh: Object.freeze({
        'core-ai-image-search.service': '搜索服务',
        'core-ai-image-search.key': '搜索密钥',
        'core-ai-image-search.endpoint': '端点',
        'core-ai-image-search.rate': '每小时搜索次数',
      }),
    }),
  }),
  render(input, helpers) {
    const { pictures } = input;
    const { fieldRow, i18n } = helpers;
    const t = (key, vars, fallback) => i18n.t(key, vars, fallback);
    const service = document.createElement('select');
    service.id = 'aiSearchService';
    for (const [key, meta] of Object.entries(pictures.services)) {
      const option = document.createElement('option');
      option.value = key;
      option.textContent = meta.label;
      option.selected = pictures.search_service === key;
      service.appendChild(option);
    }
    const serviceNote = document.createElement('div');
    serviceNote.className = 'muted small';
    const key = document.createElement('input');
    key.type = 'password';
    key.id = 'aiSearchKey';
    key.className = 'wide-field';
    key.autocomplete = 'off';
    key.placeholder = pictures.has_search_key
      ? i18n.t(
          'ai.images_search_key_unchanged',
          { mask: '•'.repeat(20) },
          '{{mask}} (unchanged)',
        )
      : i18n.t(
          'ai.images_search_key_placeholder',
          {},
          'the key for that service',
        );
    const base = document.createElement('input');
    base.type = 'text';
    base.id = 'aiSearchBase';
    base.className = 'wide-field';
    base.spellcheck = false;
    base.value = pictures.search_base || '';
    const rate = document.createElement('input');
    rate.type = 'number';
    rate.id = 'aiSearchRate';
    rate.min = '0';
    rate.max = '100';
    rate.className = 'days';
    rate.value = String(pictures.searches_per_hour);
    const sync = () => {
      const meta = pictures.services[service.value];
      serviceNote.textContent = meta?.note ?? '';
      base.placeholder = meta?.base ?? '';
    };
    service.addEventListener('change', sync);
    sync();
    const rateHelp =
      i18n.t(
        'ai.images_searches_help',
        {},
        "Separate from the picture limit above, because the two cost different things: a search is a free read against somebody else's quota, a picture is a bill. A hundred is what these services publish as their free allowance and is the most this will do — going past it gets the key throttled, which breaks the feature for everyone rather than costing money. Set it lower if you share the key.",
      ) +
      (pictures.searches
        ? ` ${i18n.t('ai.images_searches_used', { count: pictures.searches.used }, '{{count, number}} in the last hour.')}`
        : '');
    return [
      fieldRow(
        t('core-ai-image-search.service', {}, 'Search service'),
        service,
        '',
      ),
      serviceNote,
      fieldRow(
        t('core-ai-image-search.key', {}, 'Search key'),
        key,
        pictures.has_search_key
          ? i18n.t(
              'ai.images_search_key_kept',
              {},
              'A key is set. Leaving this blank keeps it.',
            )
          : i18n.t(
              'ai.images_search_key_required',
              {},
              'Without one, reactions stay off.',
            ),
      ),
      fieldRow(t('core-ai-image-search.endpoint', {}, 'Endpoint'), base, ''),
      fieldRow(
        t('core-ai-image-search.rate', {}, 'Searches per hour'),
        rate,
        rateHelp,
      ),
    ];
  },
};
