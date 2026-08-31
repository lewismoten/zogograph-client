/* Core package: HTTP method labels used by the URL-monitor editor. */
window.ZogographUrlMonitorMethodsCard = {
  manifest: Object.freeze({
    id: 'core-url-monitor-methods',
    locales: ['core-url-monitor-methods.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-url-monitor-methods.label': 'Method',
        'core-url-monitor-methods.help':
          'HEAD is cheaper and has no body, so it cannot be combined with a content check.',
        'core-url-monitor-methods.get': 'GET — fetch the page',
        'core-url-monitor-methods.head': 'HEAD — headers only, no body',
        'core-url-monitor-methods.options': 'OPTIONS — what the server allows',
      }),
      es: Object.freeze({
        'core-url-monitor-methods.label': 'Método',
        'core-url-monitor-methods.help':
          'HEAD consume menos y no tiene cuerpo, por lo que no se puede combinar con una comprobación de contenido.',
        'core-url-monitor-methods.get': 'GET — obtener la página',
        'core-url-monitor-methods.head': 'HEAD — solo cabeceras, sin cuerpo',
        'core-url-monitor-methods.options':
          'OPTIONS — lo que permite el servidor',
      }),
      hi: Object.freeze({
        'core-url-monitor-methods.label': 'विधि',
        'core-url-monitor-methods.help':
          'HEAD सस्ता है और इसमें बॉडी नहीं होती, इसलिए इसे सामग्री जाँच के साथ नहीं जोड़ा जा सकता।',
        'core-url-monitor-methods.get': 'GET — पेज प्राप्त करें',
        'core-url-monitor-methods.head': 'HEAD — केवल हेडर, कोई बॉडी नहीं',
        'core-url-monitor-methods.options':
          'OPTIONS — सर्वर क्या अनुमति देता है',
      }),
      ar: Object.freeze({
        'core-url-monitor-methods.label': 'الطريقة',
        'core-url-monitor-methods.help':
          'يستهلك HEAD أقل ولا يحتوي على متن، لذا لا يمكن جمعه مع فحص المحتوى.',
        'core-url-monitor-methods.get': 'GET — اجلب الصفحة',
        'core-url-monitor-methods.head': 'HEAD — الترويسات فقط، بلا متن',
        'core-url-monitor-methods.options': 'OPTIONS — ما يسمح به الخادم',
      }),
      zh: Object.freeze({
        'core-url-monitor-methods.label': '方法',
        'core-url-monitor-methods.help':
          'HEAD 开销更低且没有正文，因此不能与内容检查结合使用。',
        'core-url-monitor-methods.get': 'GET — 获取页面',
        'core-url-monitor-methods.head': 'HEAD — 仅标头，无正文',
        'core-url-monitor-methods.options': 'OPTIONS — 服务器允许的内容',
      }),
    }),
  }),
  render({ key }, { i18n }) {
    const id = `core-url-monitor-methods.${key}`;
    return i18n.t(id, {}, this.manifest.catalogs.en[id]);
  },
};
