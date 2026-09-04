/* Built-in storage package: filesystem path history chart. */
const StorageHistoryCard = {
  manifest: Object.freeze({
    id: 'core-storage-history',
    locales: ['core-storage-history.'],
    owner: 'core-storage',
    helpers: [
      'panel',
      'title',
      'chart',
      'drawLineChart',
      'bytesAxis',
      'countAxis',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-storage-history.size': 'Size',
        'core-storage-history.files': 'Files (right)',
        'core-storage-history.one_scan':
          'This path has only been seen in one scan so far',
        'core-storage-history.no_history': 'No history',
        'core-storage-history.title': 'Size over time',
        'core-storage-history.recorded_once':
          'Recorded, but only once so far — there is no change to draw yet.',
        'core-storage-history.not_recorded':
          'This path is not one of the ones kept between scans. Only the biggest files and directories, and the biggest of each category, are recorded each time; everything else is measured and not stored.',
        'core-storage-history.unchanged': 'Unchanged across {{scans}}.',
        'core-storage-history.changed':
          '{{direction}} by {{amount}} across {{scans}}, from {{first}} to {{last}}.',
        'core-storage-history.grew': 'Grew',
        'core-storage-history.shrank': 'Shrank',
      }),
    }),
    localeFiles: Object.freeze({
      es: 'packages/core-storage/locales/es/history.json',
      hi: 'packages/core-storage/locales/hi/history.json',
      ar: 'packages/core-storage/locales/ar/history.json',
      zh: 'packages/core-storage/locales/zh/history.json',
    }),
  }),
  render(data, helpers) {
    const { panel, title, chart, drawLineChart, bytesAxis, countAxis, i18n } =
      helpers;
    if (!panel || !title || !chart) return;
    panel.hidden = false;
    title.textContent = data.path.display;
    const t = (key, fallback) => i18n.t(key, {}, fallback);
    drawLineChart(
      chart,
      data.rows,
      [
        { key: 'bytes', label: t('core-storage-history.size', 'Size') },
        {
          key: 'files',
          label: t('core-storage-history.files', 'Files (right)'),
          axis: 'right',
        },
      ],
      {
        xKey: 'started_at',
        axis: bytesAxis,
        rightAxis: countAxis,
        zeroBased: true,
        empty: t(
          'core-storage-history.one_scan',
          'This path has only been seen in one scan so far',
        ),
      },
    );
  },
};
Zogograph.cards.register(StorageHistoryCard);
