/* Core card package: host service history table labels and chart guidance. */
window.ZogographHostServicesTableCard = {
  manifest: Object.freeze({
    id: 'core-host-services-table',
    locales: ['core-host-services-table.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-services-table.no_history':
          'No service history yet — this needs a few readings to build up.',
        'core-host-services-table.service': 'Service',
        'core-host-services-table.processes': 'Processes',
        'core-host-services-table.now': 'Now',
        'core-host-services-table.hours_24_ago': '24h ago',
        'core-host-services-table.change_24h': 'Change 24h',
        'core-host-services-table.days_7_ago': '7d ago',
        'core-host-services-table.change_7d': 'Change 7d',
        'core-host-services-table.swapped': 'Swapped',
        'core-host-services-table.io_reading': 'Reading',
        'core-host-services-table.io_rate_per_second': '{{amount}}/s',
        'core-host-services-table.io_writing': 'Writing',
        'core-host-services-table.chart_empty':
          'Not enough service history to draw yet',
        'core-host-services-table.counter_rate_help':
          'Counters differentiated into a rate; a gap means the service restarted.',
      }),
      es: Object.freeze({
        'core-host-services-table.no_history':
          'Aún no hay historial de servicios; necesita acumular algunas lecturas.',
        'core-host-services-table.service': 'Servicio',
        'core-host-services-table.processes': 'Procesos',
        'core-host-services-table.now': 'Ahora',
        'core-host-services-table.hours_24_ago': 'Hace 24 h',
        'core-host-services-table.change_24h': 'Cambio en 24 h',
        'core-host-services-table.days_7_ago': 'Hace 7 d',
        'core-host-services-table.change_7d': 'Cambio en 7 d',
        'core-host-services-table.swapped': 'En intercambio',
        'core-host-services-table.io_reading': 'Lectura',
        'core-host-services-table.io_rate_per_second': '{{amount}}/s',
        'core-host-services-table.io_writing': 'Escritura',
        'core-host-services-table.chart_empty':
          'Aún no hay suficiente historial de servicios para dibujar',
        'core-host-services-table.counter_rate_help':
          'Los contadores se diferenciaron en una tasa; una brecha significa que el servicio se reinició.',
      }),
      hi: Object.freeze({
        'core-host-services-table.no_history':
          'अभी कोई सेवा इतिहास नहीं है — इसे बनाने के लिए कुछ रीडिंग चाहिए।',
        'core-host-services-table.service': 'सेवा',
        'core-host-services-table.processes': 'प्रोसेस',
        'core-host-services-table.now': 'अभी',
        'core-host-services-table.hours_24_ago': '24घं. पहले',
        'core-host-services-table.change_24h': '24घं. बदलाव',
        'core-host-services-table.days_7_ago': '7दि. पहले',
        'core-host-services-table.change_7d': '7दि. बदलाव',
        'core-host-services-table.swapped': 'स्वैप किया गया',
        'core-host-services-table.io_reading': 'पढ़ रहा है',
        'core-host-services-table.io_rate_per_second': '{{amount}}/सेकंड',
        'core-host-services-table.io_writing': 'लिख रहा है',
        'core-host-services-table.chart_empty':
          'अभी बनाने के लिए पर्याप्त सेवा इतिहास नहीं है',
        'core-host-services-table.counter_rate_help':
          'काउंटरों को दर में बदला गया; अंतर का अर्थ है कि सेवा पुनः आरंभ हुई।',
      }),
      ar: Object.freeze({
        'core-host-services-table.no_history':
          'لا يوجد سجل للخدمات بعد — يحتاج إلى بعض القراءات ليتكوّن.',
        'core-host-services-table.service': 'الخدمة',
        'core-host-services-table.processes': 'العمليات',
        'core-host-services-table.now': 'الآن',
        'core-host-services-table.hours_24_ago': 'قبل 24 س',
        'core-host-services-table.change_24h': 'تغير 24 س',
        'core-host-services-table.days_7_ago': 'قبل 7 أيام',
        'core-host-services-table.change_7d': 'تغير 7 أيام',
        'core-host-services-table.swapped': 'مبدّل',
        'core-host-services-table.io_reading': 'قراءة',
        'core-host-services-table.io_rate_per_second': '{{amount}}/ث',
        'core-host-services-table.io_writing': 'كتابة',
        'core-host-services-table.chart_empty':
          'لا يوجد سجل خدمات كافٍ للرسم بعد',
        'core-host-services-table.counter_rate_help':
          'حُوّلت العدادات إلى معدل؛ والفجوة تعني أن الخدمة أعيد تشغيلها.',
      }),
      zh: Object.freeze({
        'core-host-services-table.no_history':
          '尚无服务历史——需要积累几次读数。',
        'core-host-services-table.service': '服务',
        'core-host-services-table.processes': '进程',
        'core-host-services-table.now': '现在',
        'core-host-services-table.hours_24_ago': '24 小时前',
        'core-host-services-table.change_24h': '24 小时变化',
        'core-host-services-table.days_7_ago': '7 天前',
        'core-host-services-table.change_7d': '7 天变化',
        'core-host-services-table.swapped': '已交换',
        'core-host-services-table.io_reading': '读取',
        'core-host-services-table.io_rate_per_second': '{{amount}}/秒',
        'core-host-services-table.io_writing': '写入',
        'core-host-services-table.chart_empty': '尚无足够的服务历史可供绘制',
        'core-host-services-table.counter_rate_help':
          '计数器已微分为速率；出现间隔意味着服务已重启。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-host-services-table.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
