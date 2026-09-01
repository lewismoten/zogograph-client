window.ZogographScheduleAvailabilityCard = {
  manifest: Object.freeze({
    id: 'core-schedule-availability',
    locales: ['core-schedule-availability.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'The crontab cannot be read here.',
      }),
      es: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'El crontab no se puede leer aquí.',
      }),
      hi: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'यहाँ crontab नहीं पढ़ा जा सकता।',
      }),
      ar: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          'لا يمكن قراءة crontab هنا.',
      }),
      zh: Object.freeze({
        'core-schedule-availability.crontab_unreadable':
          '无法在此处读取 crontab。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-availability.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
