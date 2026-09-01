window.ZogographScheduleAvailabilityCard = {
  manifest: Object.freeze({
    id: 'core-schedule-availability',
    locales: ['core-schedule-availability.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'The crontab cannot be read here.',
        'core-schedule-availability.times_unknown_zone':
          "{{job}}: {{expression}} in the server's timezone, which could not be determined",
        'core-schedule-availability.times_frequent':
          '{{job}}: {{profile}}, next at {{next}}',
      }),
      es: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'El crontab no se puede leer aquí.',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}: {{expression}} en la zona horaria del servidor, que no se pudo determinar',
        'core-schedule-availability.times_frequent':
          '{{job}}: {{profile}}, próxima vez a las {{next}}',
      }),
      hi: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'यहाँ crontab नहीं पढ़ा जा सकता।',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}: {{expression}} सर्वर के समय क्षेत्र में, जिसे निर्धारित नहीं किया जा सका',
        'core-schedule-availability.times_frequent':
          '{{job}}: {{profile}}, अगला {{next}} पर',
      }),
      ar: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'لا يمكن قراءة crontab هنا.',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}: ‏{{expression}} في المنطقة الزمنية للخادم التي تعذر تحديدها',
        'core-schedule-availability.times_frequent':
          '{{job}}: ‏{{profile}}، التشغيل التالي عند {{next}}',
      }),
      zh: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          '无法在此处读取 crontab。',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}：{{expression}} 使用无法确定的服务器时区',
        'core-schedule-availability.times_frequent':
          '{{job}}：{{profile}}，下次为 {{next}}',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-availability.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
