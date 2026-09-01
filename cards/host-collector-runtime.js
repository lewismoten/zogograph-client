window.ZogographHostCollectorRuntimeCard = {
  manifest: Object.freeze({
    id: 'core-host-collector-runtime',
    locales: ['core-host-collector-runtime.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-collector-runtime.loading': 'Reading the host collector…',
        'core-host-collector-runtime.no_readings': 'No host readings.',
        'core-host-collector-runtime.request_queued':
          "Asked. It runs on the host collector's next pass.",
        'core-host-collector-runtime.open_setup': 'Open Host setup',
      }),
      es: Object.freeze({
        'core-host-collector-runtime.loading':
          'Leyendo el recopilador del host…',
        'core-host-collector-runtime.no_readings': 'No hay lecturas del host.',
        'core-host-collector-runtime.request_queued':
          'Solicitado. Se ejecutará en la siguiente pasada del recopilador del host.',
        'core-host-collector-runtime.open_setup':
          'Abrir configuración del host',
      }),
      hi: Object.freeze({
        'core-host-collector-runtime.loading': 'होस्ट कलेक्टर पढ़ा जा रहा है…',
        'core-host-collector-runtime.no_readings':
          'होस्ट की कोई रीडिंग नहीं है।',
        'core-host-collector-runtime.request_queued':
          'अनुरोध किया गया। यह होस्ट कलेक्टर के अगले रन में चलेगा।',
        'core-host-collector-runtime.open_setup': 'होस्ट सेटअप खोलें',
      }),
      ar: Object.freeze({
        'core-host-collector-runtime.loading': 'تجري قراءة جامع المضيف…',
        'core-host-collector-runtime.no_readings': 'لا توجد قراءات للمضيف.',
        'core-host-collector-runtime.request_queued':
          'طُلِب. سيعمل في الدورة التالية لجامع المضيف.',
        'core-host-collector-runtime.open_setup': 'افتح إعداد المضيف',
      }),
      zh: Object.freeze({
        'core-host-collector-runtime.loading': '正在读取主机采集器…',
        'core-host-collector-runtime.no_readings': '没有主机读数。',
        'core-host-collector-runtime.request_queued':
          '已请求。它将在主机采集器的下一次运行中执行。',
        'core-host-collector-runtime.open_setup': '打开主机设置',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-host-collector-runtime.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
