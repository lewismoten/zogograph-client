/* Core card package: the legend for treemap colours and node kinds. */
window.ZogographTreemapLegendCard = {
  manifest: Object.freeze({
    id: 'core-treemap-legend',
    locales: ['core-treemap-legend.'],
    helpers: ['target', 'typeColor', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-treemap-legend.other': 'other',
        'core-treemap-legend.directory': 'directory',
        'core-treemap-legend.today': 'touched today',
        'core-treemap-legend.week': 'this week',
        'core-treemap-legend.quarter': 'this quarter',
        'core-treemap-legend.older': 'older',
        'core-treemap-legend.symlink': 'symlink',
        'core-treemap-legend.pooled': 'pooled',
      }),
      es: Object.freeze({
        'core-treemap-legend.other': 'otro',
        'core-treemap-legend.directory': 'directorio',
        'core-treemap-legend.today': 'modificado hoy',
        'core-treemap-legend.week': 'esta semana',
        'core-treemap-legend.quarter': 'este trimestre',
        'core-treemap-legend.older': 'anterior',
        'core-treemap-legend.symlink': 'enlace simbólico',
        'core-treemap-legend.pooled': 'agrupado',
      }),
      hi: Object.freeze({
        'core-treemap-legend.other': 'अन्य',
        'core-treemap-legend.directory': 'डायरेक्टरी',
        'core-treemap-legend.today': 'आज बदला',
        'core-treemap-legend.week': 'इस सप्ताह',
        'core-treemap-legend.quarter': 'इस तिमाही',
        'core-treemap-legend.older': 'पुराना',
        'core-treemap-legend.symlink': 'सिमलिंक',
        'core-treemap-legend.pooled': 'समूहित',
      }),
      ar: Object.freeze({
        'core-treemap-legend.other': 'أخرى',
        'core-treemap-legend.directory': 'دليل',
        'core-treemap-legend.today': 'عُدّل اليوم',
        'core-treemap-legend.week': 'هذا الأسبوع',
        'core-treemap-legend.quarter': 'هذا الربع',
        'core-treemap-legend.older': 'أقدم',
        'core-treemap-legend.symlink': 'رابط رمزي',
        'core-treemap-legend.pooled': 'مجمّع',
      }),
      zh: Object.freeze({
        'core-treemap-legend.other': '其他',
        'core-treemap-legend.directory': '目录',
        'core-treemap-legend.today': '今天修改',
        'core-treemap-legend.week': '本周',
        'core-treemap-legend.quarter': '本季度',
        'core-treemap-legend.older': '更早',
        'core-treemap-legend.symlink': '符号链接',
        'core-treemap-legend.pooled': '汇总',
      }),
    }),
  }),
  render(input, helpers) {
    const { target, typeColor, i18n } = helpers;
    if (!target) return;
    const t = (key, fallback) => i18n.t(key, {}, fallback);
    const entries =
      input.colourBy === 'type'
        ? [
            ...input.types.map((type) => [type, typeColor(type)]),
            [t('core-treemap-legend.other', 'other'), typeColor('other')],
            [t('core-treemap-legend.directory', 'directory'), '#2f6f8f'],
          ]
        : [
            [t('core-treemap-legend.today', 'touched today'), '#c9603f'],
            [t('core-treemap-legend.week', 'this week'), '#b08040'],
            [t('core-treemap-legend.quarter', 'this quarter'), '#6b7f3a'],
            [t('core-treemap-legend.older', 'older'), '#41603f'],
            [t('core-treemap-legend.directory', 'directory'), '#2f6f8f'],
          ];
    const fragment = document.createDocumentFragment();
    for (const [label, colour] of [
      ...entries,
      [t('core-treemap-legend.symlink', 'symlink'), '#4a4a4a'],
      [t('core-treemap-legend.pooled', 'pooled'), '#3a3a3a'],
    ]) {
      const span = document.createElement('span');
      span.style.setProperty('--dot', colour);
      span.textContent = label;
      fragment.appendChild(span);
    }
    target.replaceChildren(fragment);
  },
};
