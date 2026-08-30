window.ZogographFetchTabsCard = {
  manifest: Object.freeze({
    id: 'core-fetch-tabs',
    locales: ['core-fetch-tabs.'],
    helpers: ['selectView', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-fetch-tabs.headers': 'Headers',
        'core-fetch-tabs.preview': 'Preview',
        'core-fetch-tabs.metadata': 'Title & meta',
        'core-fetch-tabs.structured_data': 'Structured data',
        'core-fetch-tabs.raw': 'Raw body',
        'core-fetch-tabs.visit': 'Visit it ↗',
        'core-fetch-tabs.visit_title': 'Open this address in a new window',
      }),
      es: Object.freeze({
        'core-fetch-tabs.headers': 'Cabeceras',
        'core-fetch-tabs.preview': 'Vista previa',
        'core-fetch-tabs.metadata': 'Título y metadatos',
        'core-fetch-tabs.structured_data': 'Datos estructurados',
        'core-fetch-tabs.raw': 'Cuerpo sin procesar',
        'core-fetch-tabs.visit': 'Visitar ↗',
        'core-fetch-tabs.visit_title':
          'Abrir esta dirección en una ventana nueva',
      }),
      hi: Object.freeze({
        'core-fetch-tabs.headers': 'हेडर',
        'core-fetch-tabs.preview': 'पूर्वावलोकन',
        'core-fetch-tabs.metadata': 'शीर्षक और मेटा',
        'core-fetch-tabs.structured_data': 'संरचित डेटा',
        'core-fetch-tabs.raw': 'कच्चा मुख्य भाग',
        'core-fetch-tabs.visit': 'इसे देखें ↗',
        'core-fetch-tabs.visit_title': 'इस पते को नई विंडो में खोलें',
      }),
      ar: Object.freeze({
        'core-fetch-tabs.headers': 'الترويسات',
        'core-fetch-tabs.preview': 'معاينة',
        'core-fetch-tabs.metadata': 'العنوان وmeta',
        'core-fetch-tabs.structured_data': 'بيانات منظَّمة',
        'core-fetch-tabs.raw': 'المتن الخام',
        'core-fetch-tabs.visit': 'زُره ↗',
        'core-fetch-tabs.visit_title': 'افتح هذا العنوان في نافذة جديدة',
      }),
      zh: Object.freeze({
        'core-fetch-tabs.headers': '标头',
        'core-fetch-tabs.preview': '预览',
        'core-fetch-tabs.metadata': '标题和元数据',
        'core-fetch-tabs.structured_data': '结构化数据',
        'core-fetch-tabs.raw': '原始正文',
        'core-fetch-tabs.visit': '访问 ↗',
        'core-fetch-tabs.visit_title': '在新窗口中打开此地址',
      }),
    }),
  }),
  render(data, helpers) {
    const tabs = document.createElement('div');
    tabs.className = 'panel-tools';
    const views = [
      [
        'headers',
        helpers.i18n.t('core-fetch-tabs.headers', {}, 'Headers'),
        true,
      ],
      [
        'preview',
        helpers.i18n.t('core-fetch-tabs.preview', {}, 'Preview'),
        data.previewable,
      ],
      [
        'meta',
        helpers.i18n.t('core-fetch-tabs.metadata', {}, 'Title & meta'),
        data.looks_html,
      ],
      [
        'ld',
        helpers.i18n.t(
          'core-fetch-tabs.structured_data',
          {},
          'Structured data',
        ),
        data.looks_html,
      ],
      ['json', 'JSON', data.looks_json],
      ['raw', helpers.i18n.t('core-fetch-tabs.raw', {}, 'Raw body'), true],
    ].filter(([, , show]) => show);
    for (const [key, label] of views) {
      const button = document.createElement('button');
      button.className = 'link';
      button.textContent = label;
      button.disabled = data.showing === key;
      button.addEventListener('click', () => helpers.selectView(key));
      tabs.appendChild(button);
    }
    const visit = document.createElement('a');
    visit.className = 'link';
    visit.href = data.landed || data.url;
    visit.target = '_blank';
    visit.rel = 'noopener noreferrer';
    visit.textContent = helpers.i18n.t(
      'core-fetch-tabs.visit',
      {},
      'Visit it ↗',
    );
    visit.title = helpers.i18n.t(
      'core-fetch-tabs.visit_title',
      {},
      'Open this address in a new window',
    );
    tabs.appendChild(visit);
    return tabs;
  },
};
