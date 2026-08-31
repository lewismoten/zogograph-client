/* Core card package: unattended assistant response to alerts. */
window.ZogographAiAlertModeCard = {
  manifest: Object.freeze({
    id: 'core-ai-alert-mode',
    locales: ['core-ai-alert-mode.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-alert-mode.label': 'When an alert fires',
        'core-ai-alert-mode.off':
          'nothing — alerts behave exactly as they always have',
        'core-ai-alert-mode.comment': 'read each alert and write a note on it',
        'core-ai-alert-mode.look':
          '…and look things up first, so the note is informed',
        'core-ai-alert-mode.decide':
          '…and may conclude one did not warrant sending',
        'core-ai-alert-mode.help':
          'This runs from cron with nobody watching, so it only ever gets the read-only tools that need no permission. Anything it cannot do — reach the model, parse an answer — leaves the alert exactly as it would have been, because a thing standing between a machine noticing a problem and you being told has one acceptable failure mode.',
      }),
      es: Object.freeze({
        'core-ai-alert-mode.label': 'Cuando se activa una alerta',
        'core-ai-alert-mode.off': 'nada; las alertas siguen como siempre',
        'core-ai-alert-mode.comment': 'leer cada alerta y escribir una nota',
        'core-ai-alert-mode.look':
          '…y buscar primero para que la nota esté informada',
        'core-ai-alert-mode.decide':
          '…y puede concluir que no justificaba enviarse',
        'core-ai-alert-mode.help':
          'Se ejecuta desde cron sin nadie mirando, por lo que solo usa herramientas de lectura. Si no puede hacer algo, deja la alerta como estaba.',
      }),
      hi: Object.freeze({
        'core-ai-alert-mode.label': 'जब कोई चेतावनी आती है',
        'core-ai-alert-mode.off': 'कुछ नहीं — चेतावनियाँ हमेशा की तरह चलती हैं',
        'core-ai-alert-mode.comment': 'हर चेतावनी पढ़ें और उस पर टिप्पणी लिखें',
        'core-ai-alert-mode.look':
          '…और पहले जानकारी देखें ताकि टिप्पणी सूचित हो',
        'core-ai-alert-mode.decide':
          '…और निष्कर्ष निकाले कि इसे भेजना जरूरी नहीं था',
        'core-ai-alert-mode.help':
          'यह cron से बिना किसी के देखने के चलता है, इसलिए केवल पढ़ने वाले उपकरण पाता है। जो यह नहीं कर सकता, उसके लिए चेतावनी वैसी ही रहती है।',
      }),
      ar: Object.freeze({
        'core-ai-alert-mode.label': 'عند إطلاق تنبيه',
        'core-ai-alert-mode.off': 'لا شيء — تستمر التنبيهات كما هي دائمًا',
        'core-ai-alert-mode.comment': 'اقرأ كل تنبيه واكتب ملاحظة عليه',
        'core-ai-alert-mode.look': '…وابحث أولًا لتكون الملاحظة مستنيرة',
        'core-ai-alert-mode.decide': '…وقد يقرر أن أحدها لا يستحق الإرسال',
        'core-ai-alert-mode.help':
          'يعمل هذا من cron بلا مراقبة، لذلك لا يحصل إلا على أدوات القراءة. ما لا يستطيع فعله يترك التنبيه كما كان.',
      }),
      zh: Object.freeze({
        'core-ai-alert-mode.label': '触发告警时',
        'core-ai-alert-mode.off': '不做任何事——告警保持原样',
        'core-ai-alert-mode.comment': '阅读每个告警并写下说明',
        'core-ai-alert-mode.look': '……先查询信息，使说明更有依据',
        'core-ai-alert-mode.decide': '……并可判断某个告警不值得发送',
        'core-ai-alert-mode.help':
          '它由 cron 在无人看管时运行，因此只能使用只读工具。任何做不到的事都会让告警保持原样。',
      }),
    }),
  }),
  render(data, helpers) {
    const t = (key, fallback) =>
      helpers.i18n.t(`core-ai-alert-mode.${key}`, {}, fallback);
    const select = document.createElement('select');
    select.id = 'aiAlertMode';
    for (const [value, key, fallback] of [
      ['off', 'off', 'nothing — alerts behave exactly as they always have'],
      ['comment', 'comment', 'read each alert and write a note on it'],
      ['look', 'look', '…and look things up first, so the note is informed'],
      ['decide', 'decide', '…and may conclude one did not warrant sending'],
    ]) {
      const option = document.createElement('option');
      option.value = value;
      option.textContent = t(key, fallback);
      option.selected = (data.alerts?.mode ?? 'off') === value;
      select.appendChild(option);
    }
    return helpers.fieldRow(
      t('label', 'When an alert fires'),
      select,
      t(
        'help',
        'This runs from cron with nobody watching, so it only ever gets the read-only tools that need no permission. Anything it cannot do — reach the model, parse an answer — leaves the alert exactly as it would have been, because a thing standing between a machine noticing a problem and you being told has one acceptable failure mode.',
      ),
    );
  },
};
