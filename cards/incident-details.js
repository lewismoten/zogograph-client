/* Core card package: incident-detail header and evidence availability copy. */
window.ZogographIncidentDetailsCard = {
  manifest: Object.freeze({
    id: 'core-incident-details',
    locales: ['core-incident-details.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-incident-details.alerts_fired_together':
          '{{count, number}} alerts fired together',
        'core-incident-details.captured_at':
          'Captured {{time}} — the machine as it stood at that moment.',
        'core-incident-details.unreadable':
          'This record could not be read back.',
        'core-incident-details.what_fired': 'What fired',
      }),
      es: Object.freeze({
        'core-incident-details.alerts_fired_together':
          'Se activaron {{count, number}} alertas a la vez',
        'core-incident-details.captured_at':
          'Capturado {{time}}: el estado de la máquina en ese momento.',
        'core-incident-details.unreadable':
          'No se pudo volver a leer este registro.',
        'core-incident-details.what_fired': 'Qué se activó',
      }),
      hi: Object.freeze({
        'core-incident-details.alerts_fired_together':
          '{{count, number}} अलर्ट एक साथ सक्रिय हुए',
        'core-incident-details.captured_at':
          '{{time}} पर कैप्चर किया गया — उस क्षण मशीन जैसी थी।',
        'core-incident-details.unreadable':
          'यह रिकॉर्ड फिर से पढ़ा नहीं जा सका।',
        'core-incident-details.what_fired': 'क्या सक्रिय हुआ',
      }),
      ar: Object.freeze({
        'core-incident-details.alerts_fired_together':
          'تم تشغيل {{count, number}} من التنبيهات معًا',
        'core-incident-details.captured_at':
          'التُقطت في {{time}} — حالة الجهاز في تلك اللحظة.',
        'core-incident-details.unreadable': 'تعذرت قراءة هذا السجل مرة أخرى.',
        'core-incident-details.what_fired': 'ما الذي تم تشغيله',
      }),
      zh: Object.freeze({
        'core-incident-details.alerts_fired_together':
          '{{count, number}} 个警报同时触发',
        'core-incident-details.captured_at':
          '捕获于 {{time}}——机器当时的状态。',
        'core-incident-details.unreadable': '无法重新读取此记录。',
        'core-incident-details.what_fired': '触发了什么',
      }),
    }),
  }),
  text(key, values, helpers) {
    const id = `core-incident-details.${key}`;
    return helpers.i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
  render() {},
};
