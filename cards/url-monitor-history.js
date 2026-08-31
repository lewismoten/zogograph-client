/* Core card package: history and change details for watched URLs. */
window.ZogographUrlMonitorHistoryCard = {
  manifest: Object.freeze({
    id: 'core-url-monitor-history',
    locales: ['core-url-monitor-history.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-url-monitor-history.nothing_recorded_range':
          'Nothing recorded in this range yet.',
        'core-url-monitor-history.history_summary':
          '{{checks}} checks · {{uptime}}% as expected · {{average}} ms typical, {{slowest}} ms slowest',
        'core-url-monitor-history.response_time': 'Response time',
        'core-url-monitor-history.failed': 'Failed',
        'core-url-monitor-history.no_checks': 'No checks in this range yet',
        'core-url-monitor-history.chart_note':
          'Every check in this range. A gap is the cron not running rather than the site being down — the two look identical on a chart and are opposite problems.{{changes}}',
        'core-url-monitor-history.chart_note_changes':
          ' Blue marks are where what this check records changed.',
        'core-url-monitor-history.changes_in_range':
          '{{count}} changes in this range',
        'core-url-monitor-history.reporting_history':
          'What it has been reporting',
        'core-url-monitor-history.when': 'When',
        'core-url-monitor-history.first_seen': 'first seen',
        'core-url-monitor-history.first_seen_tip':
          'The earliest reading in this range, not something that happened — it may have been saying this for far longer.',
        'core-url-monitor-history.changed': 'changed',
        'core-url-monitor-history.was': 'Was',
        'core-url-monitor-history.became': 'Became',
      }),
      es: Object.freeze({
        'core-url-monitor-history.nothing_recorded_range':
          'Aún no hay nada registrado en este intervalo.',
        'core-url-monitor-history.history_summary':
          '{{checks}} comprobaciones · {{uptime}} % según lo esperado · {{average}} ms habitual, {{slowest}} ms el más lento',
        'core-url-monitor-history.response_time': 'Tiempo de respuesta',
        'core-url-monitor-history.failed': 'Fallido',
        'core-url-monitor-history.no_checks':
          'Aún no hay comprobaciones en este intervalo',
        'core-url-monitor-history.chart_note':
          'Cada comprobación de este intervalo. Un hueco significa que el cron no se ejecutó, no que el sitio estuviera caído: ambos se ven iguales en un gráfico y son problemas opuestos.{{changes}}',
        'core-url-monitor-history.chart_note_changes':
          ' Las marcas azules indican dónde cambió lo que registra esta comprobación.',
        'core-url-monitor-history.changes_in_range':
          '{{count}} cambios en este intervalo',
        'core-url-monitor-history.reporting_history':
          'Lo que ha estado informando',
        'core-url-monitor-history.when': 'Cuándo',
        'core-url-monitor-history.first_seen': 'visto por primera vez',
        'core-url-monitor-history.first_seen_tip':
          'La lectura más antigua de este intervalo, no algo que haya ocurrido; puede haber estado indicando esto durante mucho más tiempo.',
        'core-url-monitor-history.changed': 'cambió',
        'core-url-monitor-history.was': 'Era',
        'core-url-monitor-history.became': 'Pasó a ser',
      }),
      hi: Object.freeze({
        'core-url-monitor-history.nothing_recorded_range':
          'इस सीमा में अभी कुछ भी रिकॉर्ड नहीं है।',
        'core-url-monitor-history.history_summary':
          '{{checks}} जांचें · {{uptime}}% अपेक्षा के अनुसार · {{average}} ms सामान्य, {{slowest}} ms सबसे धीमा',
        'core-url-monitor-history.response_time': 'प्रतिक्रिया समय',
        'core-url-monitor-history.failed': 'विफल',
        'core-url-monitor-history.no_checks':
          'इस सीमा में अभी कोई जांच नहीं है',
        'core-url-monitor-history.chart_note':
          'इस सीमा की हर जांच। अंतराल का अर्थ साइट डाउन होने के बजाय क्रॉन का न चलना है — चार्ट पर दोनों समान दिखते हैं और विपरीत समस्याएं हैं।{{changes}}',
        'core-url-monitor-history.chart_note_changes':
          ' नीले निशान वहाँ हैं जहाँ इस जांच द्वारा रिकॉर्ड की जाने वाली चीज़ बदली।',
        'core-url-monitor-history.changes_in_range':
          'इस सीमा में {{count}} बदलाव',
        'core-url-monitor-history.reporting_history':
          'यह क्या रिपोर्ट कर रहा है',
        'core-url-monitor-history.when': 'कब',
        'core-url-monitor-history.first_seen': 'पहली बार देखा गया',
        'core-url-monitor-history.first_seen_tip':
          'इस सीमा का सबसे पुराना रीडिंग, कोई घटना नहीं — यह इससे बहुत अधिक समय से ऐसा कह रहा हो सकता है।',
        'core-url-monitor-history.changed': 'बदला',
        'core-url-monitor-history.was': 'था',
        'core-url-monitor-history.became': 'बन गया',
      }),
      ar: Object.freeze({
        'core-url-monitor-history.nothing_recorded_range':
          'لم يُسجل شيء في هذه المدة بعد.',
        'core-url-monitor-history.history_summary':
          '{{checks}} من الفحوصات · {{uptime}}٪ كما هو متوقع · {{average}} ms عادةً، والأبطأ {{slowest}} ms',
        'core-url-monitor-history.response_time': 'وقت الاستجابة',
        'core-url-monitor-history.failed': 'فشل',
        'core-url-monitor-history.no_checks':
          'لا توجد فحوصات في هذه المدة حتى الآن',
        'core-url-monitor-history.chart_note':
          'كل فحص في هذه المدة. تعني الفجوة أن cron لم يعمل لا أن الموقع متوقف — يبدوان متشابهين على الرسم وهما مشكلتان متعاكستان.{{changes}}',
        'core-url-monitor-history.chart_note_changes':
          ' تشير العلامات الزرقاء إلى مواضع تغيّر ما يسجله هذا الفحص.',
        'core-url-monitor-history.changes_in_range':
          '{{count}} من التغييرات في هذه المدة',
        'core-url-monitor-history.reporting_history': 'ما كان يبلّغ عنه',
        'core-url-monitor-history.when': 'متى',
        'core-url-monitor-history.first_seen': 'شوهد أول مرة',
        'core-url-monitor-history.first_seen_tip':
          'أقدم قراءة في هذه المدة، وليست شيئًا حدث — ربما كان يقول هذا لفترة أطول بكثير.',
        'core-url-monitor-history.changed': 'تغيّر',
        'core-url-monitor-history.was': 'كان',
        'core-url-monitor-history.became': 'أصبح',
      }),
      zh: Object.freeze({
        'core-url-monitor-history.nothing_recorded_range':
          '此范围内尚未记录任何内容。',
        'core-url-monitor-history.history_summary':
          '{{checks}} 次检查 · {{uptime}}% 符合预期 · 通常 {{average}} ms，最慢 {{slowest}} ms',
        'core-url-monitor-history.response_time': '响应时间',
        'core-url-monitor-history.failed': '失败',
        'core-url-monitor-history.no_checks': '此范围内尚无检查',
        'core-url-monitor-history.chart_note':
          '此范围内的每次检查。间隙表示 cron 未运行，而不是网站宕机——两者在图表上看起来相同，却是相反的问题。{{changes}}',
        'core-url-monitor-history.chart_note_changes':
          ' 蓝色标记表示此检查记录的内容发生了变化。',
        'core-url-monitor-history.changes_in_range':
          '此范围内有 {{count}} 项更改',
        'core-url-monitor-history.reporting_history': '它一直在报告的内容',
        'core-url-monitor-history.when': '时间',
        'core-url-monitor-history.first_seen': '首次发现',
        'core-url-monitor-history.first_seen_tip':
          '此范围内最早的读数，并非发生了某件事——它可能已经报告这一结果很久了。',
        'core-url-monitor-history.changed': '已更改',
        'core-url-monitor-history.was': '原为',
        'core-url-monitor-history.became': '变为',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const fallback =
      this.manifest.catalogs.en[`core-url-monitor-history.${key}`];
    return i18n.t(`core-url-monitor-history.${key}`, values, fallback);
  },
};
