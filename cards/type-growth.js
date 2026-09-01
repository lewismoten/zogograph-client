/* Core card package: chart/data helpers are supplied by the shell. */
window.ZogographTypeGrowthCard = {
  manifest: Object.freeze({
    id: 'core-type-growth',
    locales: ['core-type-growth.'],
    helpers: [
      'canvas',
      'mode',
      'drawLineChart',
      'bytesAxis',
      'typeColor',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-type-growth.title': 'How each type has grown',
        'core-type-growth.help':
          'One line per type across every completed scan in this range. A type with no line had nothing of that kind at the time — the line starts where the first file did, rather than running along the axis from the beginning.',
        'core-type-growth.no_scans_in_range_yet':
          'No completed scans in this range yet',
        'core-type-growth.two_scans_for_change':
          'Two completed scans are needed before there is a change to draw',
        'core-type-growth.total_size': 'total size',
        'core-type-growth.change_since_scan': 'change since the previous scan',
      }),
      es: Object.freeze({
        'core-type-growth.title': 'Cómo ha crecido cada tipo',
        'core-type-growth.help':
          'Una línea por tipo en cada análisis completado de este intervalo. Un tipo sin línea no tenía nada de ese tipo entonces: la línea empieza donde apareció el primer archivo, en vez de seguir el eje desde el inicio.',
        'core-type-growth.no_scans_in_range_yet':
          'Aún no hay análisis completados en este intervalo',
        'core-type-growth.two_scans_for_change':
          'Se necesitan dos análisis completados antes de dibujar un cambio',
        'core-type-growth.total_size': 'tamaño total',
        'core-type-growth.change_since_scan':
          'cambio desde el análisis anterior',
      }),
      hi: Object.freeze({
        'core-type-growth.title': 'हर प्रकार कैसे बढ़ा है',
        'core-type-growth.help':
          'इस सीमा में प्रत्येक पूर्ण स्कैन पर हर प्रकार की एक लाइन। जिस प्रकार की लाइन नहीं है, उस समय उस प्रकार की कोई फ़ाइल नहीं थी — लाइन पहली फ़ाइल के स्थान से शुरू होती है।',
        'core-type-growth.no_scans_in_range_yet':
          'इस दायरे में अभी कोई पूरा स्कैन नहीं है',
        'core-type-growth.two_scans_for_change':
          'बदलाव दिखाने से पहले दो पूरे स्कैन चाहिए',
        'core-type-growth.total_size': 'कुल आकार',
        'core-type-growth.change_since_scan': 'पिछले स्कैन के बाद बदलाव',
      }),
      ar: Object.freeze({
        'core-type-growth.title': 'كيف نما كل نوع',
        'core-type-growth.help':
          'سطر واحد لكل نوع عبر كل فحص مكتمل ضمن هذا النطاق. النوع الذي لا يظهر له سطر لم يكن منه شيء حينها — يبدأ السطر حيث ظهر أول ملف.',
        'core-type-growth.no_scans_in_range_yet':
          'لا توجد فحوصات مكتملة في هذا النطاق بعد',
        'core-type-growth.two_scans_for_change':
          'يلزم فحصان مكتملان قبل رسم تغيير',
        'core-type-growth.total_size': 'الحجم الكلي',
        'core-type-growth.change_since_scan': 'التغير منذ الفحص السابق',
      }),
      zh: Object.freeze({
        'core-type-growth.title': '各类型如何增长',
        'core-type-growth.help':
          '此范围内每次完成扫描中每个类型一条线。没有线的类型当时没有此类内容——线从第一个文件出现的位置开始。',
        'core-type-growth.no_scans_in_range_yet': '此范围内尚无完成的扫描',
        'core-type-growth.two_scans_for_change':
          '至少需要两次完成的扫描才能绘制变化',
        'core-type-growth.total_size': '总大小',
        'core-type-growth.change_since_scan': '自上次扫描后的变化',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-type-growth.${key}`,
      {},
      this.manifest.catalogs.en[`core-type-growth.${key}`],
    );
  },
  render(scans, helpers) {
    const { canvas, mode, drawLineChart, bytesAxis, typeColor, i18n } = helpers;
    const history = scans.file_type_history ?? [];
    const empty = i18n.t(
      'core-type-growth.no_scans_in_range_yet',
      {},
      'No completed scans in this range yet',
    );
    if (!history.length) {
      drawLineChart(canvas, [], [], { empty });
      return;
    }
    const byScan = new Map(),
      seen = new Set();
    for (const entry of history) {
      const at = Number(entry.started_at);
      if (!byScan.has(at)) byScan.set(at, { started_at: at });
      byScan.get(at)[entry.type] = Number(entry.bytes);
      seen.add(entry.type);
    }
    let rows = [...byScan.values()].sort((a, b) => a.started_at - b.started_at);
    const types = [...seen].sort();
    if (mode === 'change') {
      const changes = [];
      for (let i = 1; i < rows.length; i++) {
        const row = { started_at: rows[i].started_at };
        for (const type of types)
          row[type] =
            rows[i - 1][type] == null || rows[i][type] == null
              ? null
              : rows[i][type] - rows[i - 1][type];
        changes.push(row);
      }
      rows = changes;
    }
    drawLineChart(
      canvas,
      rows,
      types.map((type) => ({ key: type, label: type, color: typeColor(type) })),
      {
        xKey: 'started_at',
        axis: bytesAxis,
        zeroBased: mode !== 'change',
        empty:
          mode === 'change'
            ? i18n.t(
                'core-type-growth.two_scans_for_change',
                {},
                'Two completed scans are needed before there is a change to draw',
              )
            : empty,
      },
    );
  },
};
