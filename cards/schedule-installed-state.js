/* Core card package: installed crontab state. */
window.ZogographScheduleInstalledStateCard = {
  manifest: Object.freeze({
    id: 'core-schedule-installed-state',
    locales: ['core-schedule-installed-state.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-schedule-installed-state.only': 'Installed now:',
        'core-schedule-installed-state.with_other':
          'Installed now, alongside {{count, number}} other line(s) this dashboard does not touch:',
        'core-schedule-installed-state.none':
          'Nothing is scheduled. The dashboard will only show whatever you collect by hand.',
      }),
      es: Object.freeze({
        'core-schedule-installed-state.only': 'Instalado ahora:',
        'core-schedule-installed-state.with_other':
          'Instalado ahora, junto con {{count, number}} otras líneas que este panel no modifica:',
        'core-schedule-installed-state.none':
          'No hay nada programado. El panel solo mostrará lo que recopile manualmente.',
      }),
      hi: Object.freeze({
        'core-schedule-installed-state.only': 'अभी स्थापित:',
        'core-schedule-installed-state.with_other':
          'अभी स्थापित, साथ में {{count, number}} अन्य पंक्तियाँ जिन्हें यह डैशबोर्ड नहीं बदलता:',
        'core-schedule-installed-state.none':
          'कुछ भी निर्धारित नहीं है। डैशबोर्ड केवल वही दिखाएगा जिसे आप हाथ से एकत्र करते हैं।',
      }),
      ar: Object.freeze({
        'core-schedule-installed-state.only': 'المثبّت الآن:',
        'core-schedule-installed-state.with_other':
          'المثبّت الآن، إلى جانب {{count, number}} سطور أخرى لا تعدلها هذه اللوحة:',
        'core-schedule-installed-state.none':
          'لا توجد مهام مجدولة. لن تعرض اللوحة إلا ما تجمعه يدويًا.',
      }),
      zh: Object.freeze({
        'core-schedule-installed-state.only': '当前已安装：',
        'core-schedule-installed-state.with_other':
          '当前已安装；另有 {{count, number}} 条本仪表板不会改动的其他行：',
        'core-schedule-installed-state.none':
          '没有已安排的任务。仪表板只会显示您手动收集的数据。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-installed-state.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
