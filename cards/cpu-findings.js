/* Core card package: current CPU findings from the host collector. */
window.ZogographCpuFindingsCard = {
  manifest: Object.freeze({
    id: 'core-cpu-findings',
    locales: ['core-cpu-findings.'],
    helpers: ['target', 'findingRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-cpu-findings.title': 'What is true right now',
        'core-cpu-findings.help':
          'Every line below is a measurement taken from this machine, with a link to the page it came from. Nothing here infers a cause it cannot see, and ‘nothing found’ is said in those words rather than as a clean bill of health.',
        'core-cpu-findings.nothing_to_report': 'Nothing to report.',
      }),
      es: Object.freeze({
        'core-cpu-findings.title': 'Qué es cierto ahora mismo',
        'core-cpu-findings.help':
          'Cada línea de abajo es una medición tomada de esta máquina, con un enlace a la página de la que procede. Nada aquí deduce una causa que no puede ver, y «no se encontró nada» se dice así, en lugar de dar un certificado de buena salud.',
        'core-cpu-findings.nothing_to_report': 'Nada que informar.',
      }),
      hi: Object.freeze({
        'core-cpu-findings.title': 'अभी क्या सच है',
        'core-cpu-findings.help':
          'नीचे की हर पंक्ति इस मशीन से लिया गया माप है और उसके स्रोत पृष्ठ का लिंक है। यहाँ कोई ऐसी वजह नहीं निकाली जाती जिसे देखा नहीं जा सकता, और ‘कुछ नहीं मिला’ को साफ़ स्वास्थ्य प्रमाणपत्र के बजाय उन्हीं शब्दों में कहा जाता है।',
        'core-cpu-findings.nothing_to_report': 'रिपोर्ट करने के लिए कुछ नहीं।',
      }),
      ar: Object.freeze({
        'core-cpu-findings.title': 'ما هو صحيح الآن',
        'core-cpu-findings.help':
          'كل سطر أدناه قياس مأخوذ من هذه الآلة مع رابط إلى الصفحة التي أتى منها. لا يستنتج شيء هنا سببًا لا يراه، وتُقال «لم يُعثر على شيء» بهذه الكلمات بدل اعتبارها شهادة سلامة.',
        'core-cpu-findings.nothing_to_report': 'لا شيء للإبلاغ عنه.',
      }),
      zh: Object.freeze({
        'core-cpu-findings.title': '此刻确定的事实',
        'core-cpu-findings.help':
          '以下每一行都是从此机器取得的测量结果，并附有来源页面链接。这里不会推断看不到的原因；“未发现任何内容”会直接这样说，而不会给出健康无虞的结论。',
        'core-cpu-findings.nothing_to_report': '没有需要报告的内容。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-cpu-findings.${key}`,
      {},
      this.manifest.catalogs.en[`core-cpu-findings.${key}`],
    );
  },
  render(findings, helpers) {
    const { target, findingRow, i18n } = helpers;
    if (!target) return;
    if (!findings.length) {
      target.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent: i18n.t(
            'core-cpu-findings.nothing_to_report',
            {},
            'Nothing to report.',
          ),
        }),
      );
      return;
    }
    target.replaceChildren(...findings.map(findingRow));
  },
};
