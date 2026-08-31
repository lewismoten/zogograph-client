/* Core card package: collector-wait progress and outcome messages in chat. */
window.ZogographChatWaitingCard = {
  manifest: Object.freeze({
    id: 'core-chat-waiting',
    locales: ['core-chat-waiting.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-waiting.collector':
          'Waiting for the {{collector}} collector…',
        'core-chat-waiting.collector_for':
          '{{assistant}} is waiting for the {{collector}} collector — up to {{duration}} more.',
        'core-chat-waiting.unavailable':
          'Collector waiting is unavailable while the System health feature is disabled.',
        'core-chat-waiting.arrived':
          'New readings have landed. Anything you read before this is from before them — call the tools again rather than reasoning from the old answers.',
        'core-chat-waiting.timed_out':
          'Nothing new arrived from the {{collector}} collector in that time. That is a finding: it is not running as often as it should, or at all. Say so rather than waiting again.',
      }),
      es: Object.freeze({
        'core-chat-waiting.collector': 'Esperando al recolector {{collector}}…',
        'core-chat-waiting.collector_for':
          '{{assistant}} está esperando al recolector {{collector}} — hasta {{duration}} más.',
        'core-chat-waiting.unavailable':
          'La espera del recolector no está disponible mientras la función de salud del sistema esté deshabilitada.',
        'core-chat-waiting.arrived':
          'Han llegado nuevas lecturas. Todo lo leído antes de esto es anterior a ellas; vuelva a llamar las herramientas en vez de razonar a partir de las respuestas antiguas.',
        'core-chat-waiting.timed_out':
          'No llegó nada nuevo del recolector {{collector}} en ese tiempo. Es un hallazgo: no se ejecuta con la frecuencia debida, o no se ejecuta en absoluto. Indíquelo en lugar de esperar otra vez.',
      }),
      hi: Object.freeze({
        'core-chat-waiting.collector': '{{collector}} कलेक्टर की प्रतीक्षा है…',
        'core-chat-waiting.collector_for':
          '{{assistant}} {{collector}} कलेक्टर की प्रतीक्षा कर रहा है — अधिकतम {{duration}} और।',
        'core-chat-waiting.unavailable':
          'जब सिस्टम स्वास्थ्य सुविधा अक्षम है तब कलेक्टर की प्रतीक्षा उपलब्ध नहीं है।',
        'core-chat-waiting.arrived':
          'नई रीडिंग आ गई हैं। इससे पहले जो कुछ पढ़ा गया वह उनसे पहले का है — पुराने उत्तरों से तर्क करने के बजाय टूल फिर चलाएँ।',
        'core-chat-waiting.timed_out':
          'उस समय में {{collector}} कलेक्टर से कुछ नया नहीं आया। यह एक निष्कर्ष है: यह जितनी बार चलना चाहिए उतनी बार नहीं चल रहा है, या बिल्कुल नहीं चल रहा है। फिर प्रतीक्षा करने के बजाय ऐसा कहें।',
      }),
      ar: Object.freeze({
        'core-chat-waiting.collector': 'بانتظار جامع {{collector}}…',
        'core-chat-waiting.collector_for':
          'ينتظر {{assistant}} جامع {{collector}} — حتى {{duration}} إضافية.',
        'core-chat-waiting.unavailable':
          'انتظار الجامع غير متاح بينما تكون ميزة صحة النظام معطلة.',
        'core-chat-waiting.arrived':
          'وصلت قراءات جديدة. أي شيء قرأته قبل ذلك سابق لها — استدعِ الأدوات مرة أخرى بدلاً من الاستدلال من الإجابات القديمة.',
        'core-chat-waiting.timed_out':
          'لم يصل شيء جديد من جامع {{collector}} في ذلك الوقت. هذه نتيجة: لا يعمل بالقدر الذي ينبغي، أو لا يعمل إطلاقًا. قل ذلك بدلاً من الانتظار مرة أخرى.',
      }),
      zh: Object.freeze({
        'core-chat-waiting.collector': '正在等待 {{collector}} 收集器…',
        'core-chat-waiting.collector_for':
          '{{assistant}} 正在等待 {{collector}} 收集器，最多再等 {{duration}}。',
        'core-chat-waiting.unavailable':
          '系统健康功能被禁用时，无法等待收集器。',
        'core-chat-waiting.arrived':
          '已有新读数到达。在此之前读取的任何内容都早于这些读数——请再次调用工具，不要依据旧答案推理。',
        'core-chat-waiting.timed_out':
          '在这段时间内，{{collector}} 收集器没有任何新内容到达。这是一个发现：它没有按应有频率运行，或者根本没有运行。请说明这一点，而不要再次等待。',
      }),
    }),
  }),
  text(key, variables, helpers) {
    return helpers.i18n.t(
      `core-chat-waiting.${key}`,
      variables,
      this.manifest.catalogs.en[`core-chat-waiting.${key}`],
    );
  },
  render(_input, helpers) {
    return this.text('collector', { collector: '' }, helpers);
  },
};
