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
        'core-schedule-availability.update_policy_readonly':
          'You can view this policy but need write access to change it.',
      }),
      es: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'El crontab no se puede leer aquí.',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}: {{expression}} en la zona horaria del servidor, que no se pudo determinar',
        'core-schedule-availability.times_frequent':
          '{{job}}: {{profile}}, próxima vez a las {{next}}',
        'core-schedule-availability.update_policy_readonly':
          'Puede ver esta política, pero necesita acceso de escritura para cambiarla.',
      }),
      hi: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'यहाँ crontab नहीं पढ़ा जा सकता।',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}: {{expression}} सर्वर के समय क्षेत्र में, जिसे निर्धारित नहीं किया जा सका',
        'core-schedule-availability.times_frequent':
          '{{job}}: {{profile}}, अगला {{next}} पर',
        'core-schedule-availability.update_policy_readonly':
          'आप इस नीति को देख सकते हैं, लेकिन इसे बदलने के लिए लिखने की अनुमति चाहिए।',
      }),
      ar: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'لا يمكن قراءة crontab هنا.',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}: ‏{{expression}} في المنطقة الزمنية للخادم التي تعذر تحديدها',
        'core-schedule-availability.times_frequent':
          '{{job}}: ‏{{profile}}، التشغيل التالي عند {{next}}',
        'core-schedule-availability.update_policy_readonly':
          'يمكنك عرض هذه السياسة، لكنك تحتاج إلى صلاحية كتابة لتغييرها.',
      }),
      zh: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          '无法在此处读取 crontab。',
        'core-schedule-availability.times_unknown_zone':
          '{{job}}：{{expression}} 使用无法确定的服务器时区',
        'core-schedule-availability.times_frequent':
          '{{job}}：{{profile}}，下次为 {{next}}',
        'core-schedule-availability.update_policy_readonly':
          '您可以查看此策略，但需要写入权限才能更改它。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-availability.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
