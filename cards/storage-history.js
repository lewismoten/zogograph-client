/* Core card package: filesystem path history chart. */
window.ZogographStorageHistoryCard = {
  manifest: Object.freeze({
    id: 'core-storage-history',
    locales: ['core-storage-history.'],
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
      }),
      es: Object.freeze({
        'core-storage-history.size': 'Tamaño',
        'core-storage-history.files': 'Archivos (derecha)',
        'core-storage-history.one_scan':
          'Esta ruta solo se ha visto en un análisis hasta ahora',
        'core-storage-history.no_history': 'Sin historial',
      }),
      hi: Object.freeze({
        'core-storage-history.size': 'आकार',
        'core-storage-history.files': 'फ़ाइलें (दायाँ)',
        'core-storage-history.one_scan':
          'यह पथ अब तक केवल एक स्कैन में दिखा है',
        'core-storage-history.no_history': 'कोई इतिहास नहीं',
      }),
      ar: Object.freeze({
        'core-storage-history.size': 'الحجم',
        'core-storage-history.files': 'الملفات (يمين)',
        'core-storage-history.one_scan':
          'ظهر هذا المسار في فحص واحد فقط حتى الآن',
        'core-storage-history.no_history': 'لا سجل',
      }),
      zh: Object.freeze({
        'core-storage-history.size': '大小',
        'core-storage-history.files': '文件（右侧）',
        'core-storage-history.one_scan': '此路径目前只出现在一次扫描中',
        'core-storage-history.no_history': '没有历史记录',
      }),
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
