/* Core card package: the shell supplies all rendering and formatting helpers. */
window.ZogographOtherExtensionsCard = {
  manifest: Object.freeze({
    id: 'core-other-extensions',
    locales: ['core-other-extensions.'],
    helpers: ['target', 'renderTable', 'humanBytes', 'humanCount', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-other-extensions.title': 'What is inside {{other}}',
        'core-other-extensions.help':
          'The extensions that matched no group, heaviest first. This is where a backup convention nobody documented turns up — {{bkup}}, {{old}}, a date-stamped copy of a database. {{none}} is files with no extension at all.',
        'core-other-extensions.empty_before': 'Nothing landed in ',
        'core-other-extensions.other': 'Other',
        'core-other-extensions.empty_after':
          ', or the last scan ran before this was measured.',
        'core-other-extensions.extension': 'Extension',
        'core-other-extensions.no_extension': '(no extension)',
        'core-other-extensions.size': 'Size',
        'core-other-extensions.of_other': 'Of other',
      }),
      es: Object.freeze({
        'core-other-extensions.title': 'Qué hay dentro de {{other}}',
        'core-other-extensions.help':
          'Las extensiones que no coincidieron con ningún grupo, primero las más pesadas. Aquí aparece una convención de copias de seguridad que nadie documentó: {{bkup}}, {{old}} o una copia de base de datos con fecha. {{none}} son archivos sin extensión.',
        'core-other-extensions.empty_before': 'No cayó nada en ',
        'core-other-extensions.other': 'Otros',
        'core-other-extensions.empty_after':
          ', o el último análisis se ejecutó antes de medir esto.',
        'core-other-extensions.extension': 'Extensión',
        'core-other-extensions.no_extension': '(sin extensión)',
        'core-other-extensions.size': 'Tamaño',
        'core-other-extensions.of_other': 'De otros',
      }),
      hi: Object.freeze({
        'core-other-extensions.title': '{{other}} के अंदर क्या है',
        'core-other-extensions.help':
          'किसी समूह से न मिलने वाले एक्सटेंशन, सबसे भारी पहले। यहीं बिना लिखे बैकअप तरीके मिलते हैं — {{bkup}}, {{old}}, या डेट-स्टैम्प वाली डेटाबेस कॉपी। {{none}} बिल्कुल बिना एक्सटेंशन वाली फ़ाइलें हैं।',
        'core-other-extensions.empty_before': 'इसमें कुछ नहीं आया: ',
        'core-other-extensions.other': 'अन्य',
        'core-other-extensions.empty_after':
          ', या पिछला स्कैन इसे मापने से पहले चला था।',
        'core-other-extensions.extension': 'एक्सटेंशन',
        'core-other-extensions.no_extension': '(कोई एक्सटेंशन नहीं)',
        'core-other-extensions.size': 'आकार',
        'core-other-extensions.of_other': 'अन्य का',
      }),
      ar: Object.freeze({
        'core-other-extensions.title': 'ما داخل {{other}}',
        'core-other-extensions.help':
          'الامتدادات التي لم تطابق أي مجموعة، والأثقل أولًا. هنا يظهر أسلوب نسخ احتياطي لم يوثقه أحد — {{bkup}} و{{old}} أو نسخة قاعدة بيانات مؤرخة. {{none}} هي ملفات بلا امتداد إطلاقًا.',
        'core-other-extensions.empty_before': 'لم يقع شيء ضمن ',
        'core-other-extensions.other': 'أخرى',
        'core-other-extensions.empty_after':
          '، أو أن الفحص الأخير جرى قبل قياس هذا.',
        'core-other-extensions.extension': 'الامتداد',
        'core-other-extensions.no_extension': '(بلا امتداد)',
        'core-other-extensions.size': 'الحجم',
        'core-other-extensions.of_other': 'من الآخر',
      }),
      zh: Object.freeze({
        'core-other-extensions.title': '{{other}} 中有什么',
        'core-other-extensions.help':
          '不属于任何分组的扩展名，先列出占用最多的。这里会发现无人记录的备份惯例——{{bkup}}、{{old}} 或带日期的数据库副本。{{none}} 是完全没有扩展名的文件。',
        'core-other-extensions.empty_before': '没有内容归入 ',
        'core-other-extensions.other': '其他',
        'core-other-extensions.empty_after':
          '，或者上次扫描在测量此项之前运行。',
        'core-other-extensions.extension': '扩展名',
        'core-other-extensions.no_extension': '（无扩展名）',
        'core-other-extensions.size': '大小',
        'core-other-extensions.of_other': '其他部分',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-other-extensions.${key}`,
      {},
      this.manifest.catalogs.en[`core-other-extensions.${key}`],
    );
  },
  render(rows, helpers) {
    const { target, renderTable, humanBytes, humanCount, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!rows.length) {
      const message = document.createElement('p');
      message.className = 'muted';
      message.append(
        t('core-other-extensions.empty_before', {}, 'Nothing landed in '),
      );
      const other = document.createElement('code');
      other.textContent = t('core-other-extensions.other', {}, 'Other');
      message.append(
        other,
        t(
          'core-other-extensions.empty_after',
          {},
          ', or the last scan ran before this was measured.',
        ),
      );
      target.replaceChildren(message);
      return;
    }
    const total = rows.reduce((sum, row) => sum + Number(row.bytes || 0), 0);
    renderTable(target.id, rows, [
      {
        label: t('core-other-extensions.extension', {}, 'Extension'),
        className: 'name',
        render: (row) =>
          row.extension === '(none)'
            ? t('core-other-extensions.no_extension', {}, '(no extension)')
            : `.${row.extension}`,
      },
      {
        label: t('core-other-extensions.size', {}, 'Size'),
        className: 'size',
        render: (row) => humanBytes(row.bytes),
      },
      {
        label: t('storage.files', {}, 'Files'),
        className: 'size',
        render: (row) => humanCount(row.files),
      },
      {
        label: t('core-other-extensions.of_other', {}, 'Of other'),
        className: 'size',
        render: (row) =>
          total ? `${((Number(row.bytes) / total) * 100).toFixed(1)}%` : '—',
      },
    ]);
  },
};
