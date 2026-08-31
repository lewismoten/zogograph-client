/* Core card package: storage composition pie. */
window.ZogographTypePieCard = {
  manifest: Object.freeze({
    id: 'core-type-pie',
    locales: ['core-type-pie.'],
    helpers: [
      'withCategories',
      'canvas',
      'noteTarget',
      'typeColor',
      'categoryColors',
      'drawPieChart',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-type-pie.composition_title': 'Storage composition',
        'core-type-pie.title': 'What the space is made of',
        'core-type-pie.not_measured_yet': 'Not measured yet',
        'core-type-pie.watched_category': '{{name}} (watched)',
        'core-type-pie.types_categories_help':
          'Outer ring: every file by type, each counted once, so it is the whole account. Inner ring: the categories the alert rules watch, against that same total — the dark remainder is everything they do not cover. The two rings measure some of the same bytes twice, since a log file is in both, so read each ring against the whole rather than against the other.',
        'core-type-pie.types_help':
          'Every file counted once, so the slices are the whole account.',
      }),
      es: Object.freeze({
        'core-type-pie.composition_title': 'Composición del almacenamiento',
        'core-type-pie.title': 'De qué está hecho el espacio',
        'core-type-pie.not_measured_yet': 'Aún no se ha medido',
        'core-type-pie.watched_category': '{{name}} (vigilado)',
        'core-type-pie.types_categories_help':
          'Anillo exterior: cada archivo por tipo, contado una vez, por lo que representa toda la cuenta. Anillo interior: las categorías que vigilan las reglas de alerta, frente al mismo total; el resto oscuro es lo que no cubren. Ambos anillos miden algunos de los mismos bytes dos veces, pues un archivo de registro está en ambos; lea cada anillo frente al total, no frente al otro.',
        'core-type-pie.types_help':
          'Cada archivo se cuenta una vez, por lo que las porciones representan toda la cuenta.',
      }),
      hi: Object.freeze({
        'core-type-pie.composition_title': 'भंडारण संरचना',
        'core-type-pie.title': 'जगह किससे बनी है',
        'core-type-pie.not_measured_yet': 'अभी मापा नहीं गया',
        'core-type-pie.watched_category': '{{name}} (निगरानी में)',
        'core-type-pie.types_categories_help':
          'बाहरी वलय: प्रकार के अनुसार हर फ़ाइल एक बार गिनी जाती है, इसलिए यह पूरा खाता है। भीतरी वलय: अलर्ट नियम जिन श्रेणियों को देखते हैं, उसी कुल के विरुद्ध; गहरा शेष वे चीज़ें हैं जिन्हें वे नहीं कवर करतीं। दोनों वलय कुछ बाइट दो बार मापते हैं, क्योंकि लॉग फ़ाइल दोनों में है; हर वलय को पूरे के विरुद्ध पढ़ें, दूसरे के विरुद्ध नहीं।',
        'core-type-pie.types_help':
          'हर फ़ाइल एक बार गिनी जाती है, इसलिए स्लाइस पूरा खाता हैं।',
      }),
      ar: Object.freeze({
        'core-type-pie.composition_title': 'تركيب التخزين',
        'core-type-pie.title': 'ممّ تتكون المساحة',
        'core-type-pie.not_measured_yet': 'لم يُقَس بعد',
        'core-type-pie.watched_category': '{{name}} (مراقَب)',
        'core-type-pie.types_categories_help':
          'الحلقة الخارجية: كل ملف حسب النوع يُحسب مرة واحدة، ولذلك تمثل الحساب كله. الحلقة الداخلية: الفئات التي تراقبها قواعد التنبيه مقابل المجموع نفسه؛ والباقي الداكن ما لا تغطيه. تقيس الحلقتان بعض البايتات نفسها مرتين لأن ملف السجل يقع في كلتيهما؛ فاقرأ كل حلقة نسبة إلى المجموع لا إلى الأخرى.',
        'core-type-pie.types_help':
          'يُحسب كل ملف مرة واحدة، ولذلك تمثل الشرائح الحساب كله.',
      }),
      zh: Object.freeze({
        'core-type-pie.composition_title': '存储构成',
        'core-type-pie.title': '空间由什么组成',
        'core-type-pie.not_measured_yet': '尚未测量',
        'core-type-pie.watched_category': '{{name}}（已监视）',
        'core-type-pie.types_categories_help':
          '外环：每种类型的每个文件只计算一次，因此代表整个账户。内环：警报规则监视的类别，以同一总数为基准；深色余量是它们未覆盖的内容。由于日志文件同时属于两者，两个环会重复计算部分字节；请分别相对于总数阅读每个环，而不是相互比较。',
        'core-type-pie.types_help':
          '每个文件只计算一次，因此各扇区代表整个账户。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-type-pie.${key}`,
      {},
      this.manifest.catalogs.en[`core-type-pie.${key}`],
    );
  },
  render(scans, helpers) {
    const {
      withCategories,
      canvas,
      noteTarget,
      typeColor,
      categoryColors,
      drawPieChart,
      i18n,
    } = helpers;
    if (!canvas || !noteTarget) return;
    const t = (key, values, fallback) =>
      i18n.t(`core-type-pie.${key}`, values, fallback);
    const slices = (scans.file_types ?? []).map((row) => ({
      label: row.type,
      value: Number(row.bytes || 0),
      color: typeColor(row.type),
    }));
    const latest =
      (scans.rows ?? []).find((row) => row.id === scans.file_types_scan_id) ||
      (scans.rows ?? [])[scans.rows.length - 1];
    const categories = [];
    if (withCategories && latest)
      for (const name of scans.categories ?? []) {
        const bytes = Number(latest[`${name}_bytes`] || 0);
        if (bytes > 0) categories.push({ name, bytes });
      }
    if (categories.length) {
      drawPieChart(canvas, slices, {
        empty: t('not_measured_yet', {}, 'Not measured yet'),
        inner: categories.map((category) => ({
          label: t(
            'watched_category',
            { name: category.name },
            `${category.name} (watched)`,
          ),
          value: category.bytes,
          color: categoryColors[category.name] || '#8899aa',
        })),
      });
      noteTarget.textContent = t(
        'types_categories_help',
        {},
        'Outer ring: every file by type, each counted once, so it is the whole account. Inner ring: the categories the alert rules watch, against that same total — the dark remainder is everything they do not cover. The two rings measure some of the same bytes twice, since a log file is in both, so read each ring against the whole rather than against the other.',
      );
      return;
    }
    drawPieChart(canvas, slices, {
      empty: t('not_measured_yet', {}, 'Not measured yet'),
    });
    noteTarget.textContent = t(
      'types_help',
      {},
      'Every file counted once, so the slices are the whole account.',
    );
  },
};
