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
        'core-host-collector-runtime.add_root_cron':
          "As root — SSH in or use WHM's Terminal — add this to root's crontab with crontab -e:",
      }),
      es: Object.freeze({
        'core-host-collector-runtime.loading':
          'Leyendo el recopilador del host…',
        'core-host-collector-runtime.no_readings': 'No hay lecturas del host.',
        'core-host-collector-runtime.request_queued':
          'Solicitado. Se ejecutará en la siguiente pasada del recopilador del host.',
        'core-host-collector-runtime.open_setup':
          'Abrir configuración del host',
        'core-host-collector-runtime.add_root_cron':
          'Como root — conéctese por SSH o use la Terminal de WHM — añada esto al crontab de root con crontab -e:',
      }),
      hi: Object.freeze({
        'core-host-collector-runtime.loading': 'होस्ट कलेक्टर पढ़ा जा रहा है…',
        'core-host-collector-runtime.no_readings':
          'होस्ट की कोई रीडिंग नहीं है।',
        'core-host-collector-runtime.request_queued':
          'अनुरोध किया गया। यह होस्ट कलेक्टर के अगले रन में चलेगा।',
        'core-host-collector-runtime.open_setup': 'होस्ट सेटअप खोलें',
        'core-host-collector-runtime.add_root_cron':
          'root के रूप में — SSH से साइन इन करें या WHM का Terminal इस्तेमाल करें — इसे crontab -e से root के crontab में जोड़ें:',
      }),
      ar: Object.freeze({
        'core-host-collector-runtime.loading': 'تجري قراءة جامع المضيف…',
        'core-host-collector-runtime.no_readings': 'لا توجد قراءات للمضيف.',
        'core-host-collector-runtime.request_queued':
          'طُلِب. سيعمل في الدورة التالية لجامع المضيف.',
        'core-host-collector-runtime.open_setup': 'افتح إعداد المضيف',
        'core-host-collector-runtime.add_root_cron':
          'بصفة root — سجّل الدخول عبر SSH أو استخدم Terminal في WHM — أضف هذا إلى crontab الخاص بـ root باستخدام crontab -e:',
      }),
      zh: Object.freeze({
        'core-host-collector-runtime.loading': '正在读取主机采集器…',
        'core-host-collector-runtime.no_readings': '没有主机读数。',
        'core-host-collector-runtime.request_queued':
          '已请求。它将在主机采集器的下一次运行中执行。',
        'core-host-collector-runtime.open_setup': '打开主机设置',
        'core-host-collector-runtime.add_root_cron':
          '以 root 身份通过 SSH 登录或使用 WHM 的终端，将此项用 crontab -e 加入 root 的 crontab：',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-host-collector-runtime.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
