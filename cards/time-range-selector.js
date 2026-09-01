/* Core card package: selectable time windows shared by dashboard readings. */
window.ZogographTimeRangeSelectorCard = {
  manifest: Object.freeze({
    id: 'core-time-range-selector',
    locales: ['core-time-range-selector.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-time-range-selector.label': 'Range',
        'core-time-range-selector.10m': 'Last 10 minutes',
        'core-time-range-selector.1h': 'Last hour',
        'core-time-range-selector.6h': 'Last 6 hours',
        'core-time-range-selector.1d': 'Last day',
        'core-time-range-selector.1w': 'Last week',
        'core-time-range-selector.30d': 'Last 30 days',
        'core-time-range-selector.1y': 'Last year',
        'core-time-range-selector.link_copied':
          'Link copied. It opens this view over this exact window.',
      }),
      es: Object.freeze({
        'core-time-range-selector.label': 'Periodo',
        'core-time-range-selector.10m': 'Últimos 10 minutos',
        'core-time-range-selector.1h': 'Última hora',
        'core-time-range-selector.6h': 'Últimas 6 horas',
        'core-time-range-selector.1d': 'Último día',
        'core-time-range-selector.1w': 'Última semana',
        'core-time-range-selector.30d': 'Últimos 30 días',
        'core-time-range-selector.1y': 'Último año',
        'core-time-range-selector.link_copied':
          'Enlace copiado. Abre esta vista sobre esta ventana exacta.',
      }),
      hi: Object.freeze({
        'core-time-range-selector.label': 'अवधि',
        'core-time-range-selector.10m': 'पिछले 10 मिनट',
        'core-time-range-selector.1h': 'पिछला घंटा',
        'core-time-range-selector.6h': 'पिछले 6 घंटे',
        'core-time-range-selector.1d': 'पिछला दिन',
        'core-time-range-selector.1w': 'पिछला सप्ताह',
        'core-time-range-selector.30d': 'पिछले 30 दिन',
        'core-time-range-selector.1y': 'पिछला वर्ष',
        'core-time-range-selector.link_copied':
          'लिंक कॉपी हो गया। यह दृश्य को इसी सटीक समय-खंड पर खोलता है।',
      }),
      ar: Object.freeze({
        'core-time-range-selector.label': 'النطاق الزمني',
        'core-time-range-selector.10m': 'آخر 10 دقائق',
        'core-time-range-selector.1h': 'الساعة الماضية',
        'core-time-range-selector.6h': 'آخر 6 ساعات',
        'core-time-range-selector.1d': 'اليوم الماضي',
        'core-time-range-selector.1w': 'الأسبوع الماضي',
        'core-time-range-selector.30d': 'آخر 30 يومًا',
        'core-time-range-selector.1y': 'السنة الماضية',
        'core-time-range-selector.link_copied':
          'تم نسخ الرابط. يفتح هذا العرض على هذه النافذة الزمنية نفسها.',
      }),
      zh: Object.freeze({
        'core-time-range-selector.label': '时间范围',
        'core-time-range-selector.10m': '最近 10 分钟',
        'core-time-range-selector.1h': '最近 1 小时',
        'core-time-range-selector.6h': '最近 6 小时',
        'core-time-range-selector.1d': '最近一天',
        'core-time-range-selector.1w': '最近一周',
        'core-time-range-selector.30d': '最近 30 天',
        'core-time-range-selector.1y': '最近一年',
        'core-time-range-selector.link_copied':
          '链接已复制。它会在这个确切时间窗口中打开此视图。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-time-range-selector.${key}`,
      {},
      this.manifest.catalogs.en[`core-time-range-selector.${key}`],
    );
  },
  render(_input, helpers) {
    const t = (key, fallback) =>
      helpers.i18n.t(`core-time-range-selector.${key}`, {}, fallback);
    return {
      label: t('label', 'Range'),
      '10m': t('10m', 'Last 10 minutes'),
      '1h': t('1h', 'Last hour'),
      '6h': t('6h', 'Last 6 hours'),
      '1d': t('1d', 'Last day'),
      '1w': t('1w', 'Last week'),
      '30d': t('30d', 'Last 30 days'),
      '1y': t('1y', 'Last year'),
    };
  },
};
