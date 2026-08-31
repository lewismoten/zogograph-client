/* Core card package: accessible identity text for a named chart. */
window.ZogographChartIdentityCard = {
  manifest: Object.freeze({
    id: 'core-chart-identity',
    locales: ['core-chart-identity.'],
    helpers: ['humanSpan', 'humanTime', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chart-identity.id': 'ID: {{id}}',
        'core-chart-identity.range':
          'Showing: {{from}} to {{to}} ({{duration}})',
        'core-chart-identity.units': 'Units: {{units}}',
        'core-chart-identity.pinned': 'Pinned to a moment',
        'core-chart-identity.now': 'Up to now',
        'core-chart-identity.hint':
          'Click to copy the ID. You can tell {{assistant}} which chart you mean by name.',
      }),
      es: Object.freeze({
        'core-chart-identity.id': 'ID: {{id}}',
        'core-chart-identity.range':
          'Mostrando: de {{from}} a {{to}} ({{duration}})',
        'core-chart-identity.units': 'Unidades: {{units}}',
        'core-chart-identity.pinned': 'Fijado a un momento',
        'core-chart-identity.now': 'Hasta ahora',
        'core-chart-identity.hint':
          'Haz clic para copiar el ID. Puedes decirle a {{assistant}} qué gráfico quieres decir por su nombre.',
      }),
      hi: Object.freeze({
        'core-chart-identity.id': 'आईडी: {{id}}',
        'core-chart-identity.range':
          'दिखाया जा रहा है: {{from}} से {{to}} तक ({{duration}})',
        'core-chart-identity.units': 'इकाइयाँ: {{units}}',
        'core-chart-identity.pinned': 'एक क्षण पर स्थिर',
        'core-chart-identity.now': 'अभी तक',
        'core-chart-identity.hint':
          'आईडी कॉपी करने के लिए क्लिक करें। आप {{assistant}} को नाम से बता सकते हैं कि आपका मतलब कौन-सा चार्ट है।',
      }),
      ar: Object.freeze({
        'core-chart-identity.id': 'المعرّف: {{id}}',
        'core-chart-identity.range':
          'المعروض: من {{from}} إلى {{to}} ({{duration}})',
        'core-chart-identity.units': 'الوحدات: {{units}}',
        'core-chart-identity.pinned': 'مثبّت عند لحظة',
        'core-chart-identity.now': 'حتى الآن',
        'core-chart-identity.hint':
          'انقر لنسخ المعرّف. يمكنك إخبار {{assistant}} باسم المخطط الذي تقصده.',
      }),
      zh: Object.freeze({
        'core-chart-identity.id': 'ID：{{id}}',
        'core-chart-identity.range':
          '显示范围：{{from}} 至 {{to}}（{{duration}}）',
        'core-chart-identity.units': '单位：{{units}}',
        'core-chart-identity.pinned': '固定在某一时刻',
        'core-chart-identity.now': '截至现在',
        'core-chart-identity.hint':
          '点击复制 ID。您可以通过名称告诉 {{assistant}} 您指的是哪个图表。',
      }),
    }),
  }),

  render(input, helpers) {
    const { chart, id, pinned, assistant } = input;
    const { humanSpan, humanTime, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    return [
      chart.name,
      t('core-chart-identity.id', { id }, 'ID: {{id}}'),
      t(
        'core-chart-identity.range',
        {
          from: humanTime(input.from),
          to: humanTime(input.to),
          duration: humanSpan(input.range, true),
        },
        'Showing: {{from}} to {{to}} ({{duration}})',
      ),
      t(
        'core-chart-identity.units',
        { units: chart.units },
        'Units: {{units}}',
      ),
      t(
        pinned ? 'core-chart-identity.pinned' : 'core-chart-identity.now',
        {},
        pinned ? 'Pinned to a moment' : 'Up to now',
      ),
      '',
      chart.what,
      '',
      t(
        'core-chart-identity.hint',
        { assistant },
        'Click to copy the ID. You can tell {{assistant}} which chart you mean by name.',
      ),
    ].join('\n');
  },
};
