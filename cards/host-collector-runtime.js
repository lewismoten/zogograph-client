window.ZogographHostCollectorRuntimeCard = {
  manifest: Object.freeze({
    id: 'core-host-collector-runtime',
    locales: ['core-host-collector-runtime.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-collector-runtime.loading': 'Reading the host collector…',
        'core-host-collector-runtime.no_readings': 'No host readings.',
      }),
      es: Object.freeze({
        'core-host-collector-runtime.loading':
          'Leyendo el recopilador del host…',
        'core-host-collector-runtime.no_readings': 'No hay lecturas del host.',
      }),
      hi: Object.freeze({
        'core-host-collector-runtime.loading': 'होस्ट कलेक्टर पढ़ा जा रहा है…',
        'core-host-collector-runtime.no_readings':
          'होस्ट की कोई रीडिंग नहीं है।',
      }),
      ar: Object.freeze({
        'core-host-collector-runtime.loading': 'تجري قراءة جامع المضيف…',
        'core-host-collector-runtime.no_readings': 'لا توجد قراءات للمضيف.',
      }),
      zh: Object.freeze({
        'core-host-collector-runtime.loading': '正在读取主机采集器…',
        'core-host-collector-runtime.no_readings': '没有主机读数。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-host-collector-runtime.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
