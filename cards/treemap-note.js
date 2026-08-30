/* Core card package: explanatory totals and caveats below the treemap. */
window.ZogographTreemapNoteCard = {
  manifest: Object.freeze({
    id: 'core-treemap-note',
    locales: ['core-treemap-note.'],
    helpers: ['target', 'humanBytes', 'count', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-treemap-note.summary': '{{bytes}} in {{files}}{{entries}}.',
        'core-treemap-note.entries': ' across {{entries}}',
        'core-treemap-note.incomplete':
          'Some directories were still being measured when the time budget ran out — those are marked and their sizes are at least what is shown, not exactly.',
        'core-treemap-note.pooled':
          '{{entries}} are pooled into one rectangle; its area is still their true total.',
        'core-treemap-note.measured': 'Measured in {{seconds}}s.',
        'core-treemap-note.filtered':
          'Showing only {{type}}: {{shown}} of the {{total}} here. Directories are sized by how much {{type}} is inside them, not by their whole contents.',
      }),
      es: Object.freeze({
        'core-treemap-note.summary': '{{bytes}} en {{files}}{{entries}}.',
        'core-treemap-note.entries': ' en {{entries}}',
        'core-treemap-note.incomplete':
          'Algunos directorios seguían midiéndose cuando se agotó el tiempo; están marcados y sus tamaños son al menos los mostrados.',
        'core-treemap-note.pooled':
          '{{entries}} se agrupan en un rectángulo; su área sigue siendo el total real.',
        'core-treemap-note.measured': 'Medido en {{seconds}} s.',
        'core-treemap-note.filtered':
          'Se muestra solo {{type}}: {{shown}} de {{total}} aquí. Los directorios se dimensionan por lo que contienen.',
      }),
      hi: Object.freeze({
        'core-treemap-note.summary': '{{bytes}} में {{files}}{{entries}}।',
        'core-treemap-note.entries': ' {{entries}} में',
        'core-treemap-note.incomplete':
          'समय सीमा समाप्त होने पर कुछ डायरेक्टरी अभी भी मापी जा रही थीं; उनके आकार कम से कम दिखाए गए आकार हैं।',
        'core-treemap-note.pooled':
          '{{entries}} एक आयत में समूहित हैं; उसका क्षेत्रफल उनका वास्तविक कुल है।',
        'core-treemap-note.measured': '{{seconds}} से. में मापा गया।',
        'core-treemap-note.filtered':
          'केवल {{type}} दिख रहा है: यहाँ {{total}} में से {{shown}}।',
      }),
      ar: Object.freeze({
        'core-treemap-note.summary': '{{bytes}} في {{files}}{{entries}}.',
        'core-treemap-note.entries': ' عبر {{entries}}',
        'core-treemap-note.incomplete':
          'كانت بعض الأدلة لا تزال قيد القياس عند انتهاء الوقت؛ أحجامها المعروضة حد أدنى.',
        'core-treemap-note.pooled':
          'جُمعت {{entries}} في مستطيل واحد؛ ومساحته مجموعها الحقيقي.',
        'core-treemap-note.measured': 'قيس في {{seconds}} ث.',
        'core-treemap-note.filtered':
          'يُعرض {{type}} فقط: {{shown}} من {{total}} هنا.',
      }),
      zh: Object.freeze({
        'core-treemap-note.summary': '{{bytes}}，共 {{files}}{{entries}}。',
        'core-treemap-note.entries': '，跨 {{entries}}',
        'core-treemap-note.incomplete':
          '时间用尽时部分目录仍在测量；标记的大小至少为所示值。',
        'core-treemap-note.pooled':
          '{{entries}} 被汇总为一个矩形；其面积仍为真实总量。',
        'core-treemap-note.measured': '测量耗时 {{seconds}} 秒。',
        'core-treemap-note.filtered':
          '仅显示 {{type}}：此处 {{total}} 中的 {{shown}}。',
      }),
    }),
  }),
  render(input, helpers) {
    const { target, humanBytes, count, i18n } = helpers;
    const { level, typeFilter, shown } = input;
    if (!target || !level) return;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const entries = level.children.length
      ? t(
          'core-treemap-note.entries',
          { entries: count(level.children.length, 'entry', 'entries') },
          ' across {{entries}}',
        )
      : '';
    const parts = [
      t(
        'core-treemap-note.summary',
        {
          bytes: humanBytes(level.bytes),
          files: count(level.files, 'file'),
          entries,
        },
        '{{bytes}} in {{files}}{{entries}}.',
      ),
    ];
    if (level.reason) parts.push(level.reason);
    if (!level.complete && !level.reason)
      parts.push(
        t(
          'core-treemap-note.incomplete',
          {},
          'Some directories were still being measured when the time budget ran out — those are marked and their sizes are at least what is shown, not exactly.',
        ),
      );
    if (level.pooled)
      parts.push(
        t(
          'core-treemap-note.pooled',
          { entries: count(level.pooled, 'small entry', 'small entries') },
          '{{entries}} are pooled into one rectangle; its area is still their true total.',
        ),
      );
    if (level.seconds)
      parts.push(
        t(
          'core-treemap-note.measured',
          { seconds: level.seconds },
          'Measured in {{seconds}}s.',
        ),
      );
    if (typeFilter)
      parts.push(
        t(
          'core-treemap-note.filtered',
          {
            type: typeFilter,
            shown: humanBytes(shown),
            total: humanBytes(level.bytes),
          },
          'Showing only {{type}}: {{shown}} of the {{total}} here. Directories are sized by how much {{type}} is inside them, not by their whole contents.',
        ),
      );
    target.textContent = parts.join(' ');
  },
};
