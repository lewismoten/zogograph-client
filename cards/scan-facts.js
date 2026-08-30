/* Core card package: receives presentation helpers explicitly from the shell. */
window.ZogographScanFactCards = {
  manifest: Object.freeze({
    id: 'core-scan-facts',
    locales: ['core-scan-facts.'],
    helpers: [
      'facts',
      'note',
      'humanTime',
      'humanDuration',
      'humanBytes',
      'humanCount',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-scan-facts.first_scan_running': 'The first scan is running now.',
        'core-scan-facts.no_completed_scan': 'No completed scan yet.',
        'core-scan-facts.last_scan_label': 'Last scan',
        'core-scan-facts.scan_took': 'took {{duration}}',
        'core-scan-facts.seen_label': 'Seen',
        'core-scan-facts.files_in_directories':
          '{{files}} files in {{directories}} directories',
        'core-scan-facts.scans_in_range': 'Scans in this range',
        'core-scan-facts.each_chart_point': 'each point on the charts below',
        'core-scan-facts.not_enough_trend': 'not enough to draw a trend',
        'core-scan-facts.last_scan_reported':
          'The last scan reported: {{error}}',
      }),
      es: Object.freeze({
        'core-scan-facts.first_scan_running':
          'El primer análisis se está ejecutando ahora.',
        'core-scan-facts.no_completed_scan':
          'Aún no hay ningún análisis completado.',
        'core-scan-facts.last_scan_label': 'Último análisis',
        'core-scan-facts.scan_took': 'duró {{duration}}',
        'core-scan-facts.seen_label': 'Visto',
        'core-scan-facts.files_in_directories':
          '{{files}} archivos en {{directories}} directorios',
        'core-scan-facts.scans_in_range': 'Análisis en este intervalo',
        'core-scan-facts.each_chart_point':
          'cada punto de los gráficos de abajo',
        'core-scan-facts.not_enough_trend':
          'no hay suficiente para dibujar una tendencia',
        'core-scan-facts.last_scan_reported':
          'El último análisis informó: {{error}}',
      }),
      hi: Object.freeze({
        'core-scan-facts.first_scan_running': 'पहला स्कैन अभी चल रहा है।',
        'core-scan-facts.no_completed_scan':
          'अभी तक कोई स्कैन पूरा नहीं हुआ है।',
        'core-scan-facts.last_scan_label': 'पिछला स्कैन',
        'core-scan-facts.scan_took': 'लगा {{duration}}',
        'core-scan-facts.seen_label': 'देखा गया',
        'core-scan-facts.files_in_directories':
          '{{directories}} निर्देशिकाओं में {{files}} फ़ाइलें',
        'core-scan-facts.scans_in_range': 'इस दायरे में स्कैन',
        'core-scan-facts.each_chart_point': 'नीचे के चार्ट में हर बिंदु',
        'core-scan-facts.not_enough_trend': 'रुझान बनाने के लिए पर्याप्त नहीं',
        'core-scan-facts.last_scan_reported': 'पिछले स्कैन ने बताया: {{error}}',
      }),
      ar: Object.freeze({
        'core-scan-facts.first_scan_running': 'يجري الفحص الأول الآن.',
        'core-scan-facts.no_completed_scan': 'لا يوجد فحص مكتمل بعد.',
        'core-scan-facts.last_scan_label': 'آخر فحص',
        'core-scan-facts.scan_took': 'استغرق {{duration}}',
        'core-scan-facts.seen_label': 'شوهد',
        'core-scan-facts.files_in_directories':
          '{{files}} ملفًا في {{directories}} دليلًا',
        'core-scan-facts.scans_in_range': 'الفحوصات في هذا النطاق',
        'core-scan-facts.each_chart_point': 'كل نقطة في المخططات أدناه',
        'core-scan-facts.not_enough_trend': 'لا تكفي البيانات لرسم اتجاه',
        'core-scan-facts.last_scan_reported': 'أفاد آخر فحص: {{error}}',
      }),
      zh: Object.freeze({
        'core-scan-facts.first_scan_running': '第一次扫描正在运行。',
        'core-scan-facts.no_completed_scan': '尚无已完成的扫描。',
        'core-scan-facts.last_scan_label': '上次扫描',
        'core-scan-facts.scan_took': '耗时 {{duration}}',
        'core-scan-facts.seen_label': '已发现',
        'core-scan-facts.files_in_directories':
          '{{directories}} 个目录中有 {{files}} 个文件',
        'core-scan-facts.scans_in_range': '此范围内的扫描',
        'core-scan-facts.each_chart_point': '下方图表中的每个点',
        'core-scan-facts.not_enough_trend': '没有足够的数据绘制趋势',
        'core-scan-facts.last_scan_reported': '上次扫描报告：{{error}}',
      }),
    }),
  }),
  render(scans, helpers) {
    const {
      facts,
      note,
      humanTime,
      humanDuration,
      humanBytes,
      humanCount,
      i18n,
    } = helpers;
    const t = (key, values, fallback) =>
      i18n.t(`core-scan-facts.${key}`, values, fallback);
    const last = scans.last_scan;
    if (!last) {
      facts.replaceChildren();
      note.textContent = scans.scan_running
        ? t('first_scan_running', {}, 'The first scan is running now.')
        : t('no_completed_scan', {}, 'No completed scan yet.');
      return;
    }
    const cards = [
      [
        t('last_scan_label', {}, 'Last scan'),
        humanTime(last.finished_at || last.started_at),
        last.duration_ms
          ? t(
              'scan_took',
              { duration: humanDuration(last.duration_ms / 1000) },
              `took ${humanDuration(last.duration_ms / 1000)}`,
            )
          : '',
      ],
      [
        t('seen_label', {}, 'Seen'),
        humanBytes(last.bytes_seen),
        t(
          'files_in_directories',
          {
            files: humanCount(last.files_seen),
            directories: humanCount(last.dirs_seen),
          },
          `${humanCount(last.files_seen)} files in ${humanCount(last.dirs_seen)} directories`,
        ),
      ],
      [
        t('scans_in_range', {}, 'Scans in this range'),
        String(scans.rows.length),
        scans.rows.length > 1
          ? t('each_chart_point', {}, 'each point on the charts below')
          : t('not_enough_trend', {}, 'not enough to draw a trend'),
      ],
    ];
    const frag = document.createDocumentFragment();
    for (const [label, value, sub] of cards) {
      const card = document.createElement('div');
      card.className = 'card';
      for (const [cls, text] of [
        ['label', label],
        ['value', value],
        ['sub muted', sub],
      ]) {
        const div = document.createElement('div');
        div.className = cls;
        div.textContent = text;
        card.appendChild(div);
      }
      frag.appendChild(card);
    }
    facts.replaceChildren(frag);
    note.textContent = last.error
      ? t(
          'last_scan_reported',
          { error: last.error },
          `The last scan reported: ${last.error}`,
        )
      : '';
  },
};
