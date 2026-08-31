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
      es: Object.freeze({
        'core-storage-history.size': 'Tamaño',
        'core-storage-history.files': 'Archivos (derecha)',
        'core-storage-history.one_scan':
          'Esta ruta solo se ha visto en un análisis hasta ahora',
        'core-storage-history.no_history': 'Sin historial',
        'core-storage-history.title': 'Tamaño a lo largo del tiempo',
        'core-storage-history.recorded_once':
          'Se registró, pero solo una vez hasta ahora; todavía no hay cambios que dibujar.',
        'core-storage-history.not_recorded':
          'Esta ruta no es una de las que se conservan entre análisis. Cada vez solo se registran los archivos y directorios más grandes, y el mayor de cada categoría; todo lo demás se mide y no se almacena.',
        'core-storage-history.unchanged': 'Sin cambios en {{scans}}.',
        'core-storage-history.changed':
          '{{direction}} {{amount}} en {{scans}}, de {{first}} a {{last}}.',
        'core-storage-history.grew': 'Creció',
        'core-storage-history.shrank': 'Se redujo',
      }),
      hi: Object.freeze({
        'core-storage-history.size': 'आकार',
        'core-storage-history.files': 'फ़ाइलें (दायाँ)',
        'core-storage-history.one_scan':
          'यह पथ अब तक केवल एक स्कैन में दिखा है',
        'core-storage-history.no_history': 'कोई इतिहास नहीं',
        'core-storage-history.title': 'समय के साथ आकार',
        'core-storage-history.recorded_once':
          'रिकॉर्ड किया गया, लेकिन अभी तक केवल एक बार — खींचने के लिए अभी कोई बदलाव नहीं है।',
        'core-storage-history.not_recorded':
          'यह पथ स्कैन के बीच रखे जाने वाले पथों में से नहीं है। हर बार केवल सबसे बड़ी फ़ाइलें और निर्देशिकाएँ, और हर श्रेणी की सबसे बड़ी वस्तु रिकॉर्ड की जाती है; बाकी सब मापा जाता है और संग्रहीत नहीं होता।',
        'core-storage-history.unchanged': '{{scans}} में कोई बदलाव नहीं।',
        'core-storage-history.changed':
          '{{direction}} {{amount}}, {{scans}} में, {{first}} से {{last}} तक।',
        'core-storage-history.grew': 'बढ़ा',
        'core-storage-history.shrank': 'घटा',
      }),
      ar: Object.freeze({
        'core-storage-history.size': 'الحجم',
        'core-storage-history.files': 'الملفات (يمين)',
        'core-storage-history.one_scan':
          'ظهر هذا المسار في فحص واحد فقط حتى الآن',
        'core-storage-history.no_history': 'لا سجل',
        'core-storage-history.title': 'الحجم بمرور الوقت',
        'core-storage-history.recorded_once':
          'سُجلت، لكن مرة واحدة فقط حتى الآن — لا تغيير لرسمه بعد.',
        'core-storage-history.not_recorded':
          'هذا المسار ليس من المسارات المحفوظة بين الفحوصات. لا يُسجل في كل مرة إلا أكبر الملفات والأدلة، وأكبر عنصر من كل فئة؛ أما كل شيء آخر فيُقاس ولا يُخزن.',
        'core-storage-history.unchanged': 'لم يتغير عبر {{scans}}.',
        'core-storage-history.changed':
          '{{direction}} بمقدار {{amount}} عبر {{scans}}، من {{first}} إلى {{last}}.',
        'core-storage-history.grew': 'زاد',
        'core-storage-history.shrank': 'انكمش',
      }),
      zh: Object.freeze({
        'core-storage-history.size': '大小',
        'core-storage-history.files': '文件（右侧）',
        'core-storage-history.one_scan': '此路径目前只出现在一次扫描中',
        'core-storage-history.no_history': '没有历史记录',
        'core-storage-history.title': '随时间变化的大小',
        'core-storage-history.recorded_once':
          '已记录，但目前仅记录过一次——还没有可绘制的变化。',
        'core-storage-history.not_recorded':
          '此路径不属于扫描之间保留的路径。每次只记录最大的文件和目录，以及每个类别中最大的项；其他所有内容都会测量但不存储。',
        'core-storage-history.unchanged': '在 {{scans}} 次扫描中未变化。',
        'core-storage-history.changed':
          '{{direction}} {{amount}}，共 {{scans}} 次扫描，从 {{first}} 到 {{last}}。',
        'core-storage-history.grew': '增加了',
        'core-storage-history.shrank': '减少了',
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
