/* Core card package: threshold for summarising a long conversation. */
window.ZogographAiContextCompactionThresholdCard = {
  manifest: Object.freeze({
    id: 'core-ai-context-compaction-threshold',
    locales: ['core-ai-context-compaction-threshold.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-context-compaction-threshold.label': 'Summarise at (%)',
        'core-ai-context-compaction-threshold.help':
          'How full it gets before the older part of a conversation is replaced with a summary. Deliberately not near 100: summarising means sending the part being summarised, so a limit that fires when the window is already full is a limit that cannot act.',
      }),
      es: Object.freeze({
        'core-ai-context-compaction-threshold.label': 'Resumir al (%)',
        'core-ai-context-compaction-threshold.help':
          'Qué tan llena está antes de sustituir la parte antigua por un resumen. No se acerca deliberadamente a 100: resumir requiere enviar la parte resumida.',
      }),
      hi: Object.freeze({
        'core-ai-context-compaction-threshold.label':
          'इस प्रतिशत पर सारांश बनाएँ',
        'core-ai-context-compaction-threshold.help':
          'पुराने हिस्से को सारांश से बदलने से पहले यह कितना भरता है। इसे जानबूझकर 100 के पास नहीं रखा गया है, क्योंकि सारांश के लिए उसी हिस्से को भेजना पड़ता है।',
      }),
      ar: Object.freeze({
        'core-ai-context-compaction-threshold.label': 'لخّص عند (%)',
        'core-ai-context-compaction-threshold.help':
          'مقدار الامتلاء قبل استبدال الجزء الأقدم بملخص. لا يقترب عمدًا من 100 لأن التلخيص يتطلب إرسال الجزء الذي سيُلخّص.',
      }),
      zh: Object.freeze({
        'core-ai-context-compaction-threshold.label': '在此百分比时总结',
        'core-ai-context-compaction-threshold.help':
          '旧内容被摘要替换前的填充程度。刻意不接近 100，因为摘要需要发送将被摘要的那部分内容。',
      }),
    }),
  }),
  render(data, helpers) {
    const input = document.createElement('input');
    input.type = 'number';
    input.id = 'aiCompactAt';
    input.min = '30';
    input.max = '85';
    input.className = 'days';
    input.value = String(data.compact_at ?? 70);
    return helpers.fieldRow(
      helpers.i18n.t(
        'core-ai-context-compaction-threshold.label',
        {},
        'Summarise at (%)',
      ),
      input,
      helpers.i18n.t(
        'core-ai-context-compaction-threshold.help',
        {},
        'How full it gets before the older part of a conversation is replaced with a summary. Deliberately not near 100: summarising means sending the part being summarised, so a limit that fires when the window is already full is a limit that cannot act.',
      ),
    );
  },
};
