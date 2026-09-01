/* Core card package: title and address controls for watched URL editing. */
window.ZogographUrlMonitorEditorCard = {
  manifest: Object.freeze({
    id: 'core-url-monitor-editor',
    locales: ['core-url-monitor-editor.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-url-monitor-editor.editing': 'Editing {{label}}',
        'core-url-monitor-editor.add_check': 'Add a check',
        'core-url-monitor-editor.address_placeholder': 'https://example.com/',
        'core-url-monitor-editor.address': 'Address',
        'core-url-monitor-editor.address_help':
          'The address somebody would actually type.',
        'core-url-monitor-editor.label_placeholder':
          'left blank, the host is used',
        'core-url-monitor-editor.label': 'Called',
        'core-url-monitor-editor.interval': 'How often',
        'core-url-monitor-editor.follow_redirects': 'Follow redirects',
        'core-url-monitor-editor.follow_redirects_help':
          'Each redirect is checked as a new destination. A redirect into an unapproved private host is refused rather than followed.',
        'core-url-monitor-editor.ask_if_changed': 'Ask if it changed',
        'core-url-monitor-editor.ask_if_changed_help':
          'Sends the last ETag, so an unchanged page answers 304 with no body. A 304 counts as healthy and skips the content check, because there is no body to check.',
      }),
      es: Object.freeze({
        'core-url-monitor-editor.editing': 'Editando {{label}}',
        'core-url-monitor-editor.add_check': 'Añadir una comprobación',
        'core-url-monitor-editor.address_placeholder': 'https://ejemplo.com/',
        'core-url-monitor-editor.address': 'Dirección',
        'core-url-monitor-editor.address_help':
          'La dirección que alguien escribiría realmente.',
        'core-url-monitor-editor.label_placeholder':
          'déjelo vacío para usar el host',
        'core-url-monitor-editor.label': 'Nombre',
        'core-url-monitor-editor.interval': 'Frecuencia',
        'core-url-monitor-editor.follow_redirects': 'Seguir redirecciones',
        'core-url-monitor-editor.follow_redirects_help':
          'Cada redirección se comprueba como un destino nuevo. Se rechaza una redirección a un host privado no aprobado en vez de seguirla.',
        'core-url-monitor-editor.ask_if_changed': 'Preguntar si cambió',
        'core-url-monitor-editor.ask_if_changed_help':
          'Envía el último ETag, por lo que una página sin cambios responde 304 sin cuerpo. Un 304 cuenta como saludable y omite la comprobación de contenido porque no hay cuerpo que comprobar.',
      }),
      hi: Object.freeze({
        'core-url-monitor-editor.editing': '{{label}} संपादित किया जा रहा है',
        'core-url-monitor-editor.add_check': 'जांच जोड़ें',
        'core-url-monitor-editor.address_placeholder': 'https://example.com/',
        'core-url-monitor-editor.address': 'पता',
        'core-url-monitor-editor.address_help':
          'वह पता जो कोई वास्तव में टाइप करेगा।',
        'core-url-monitor-editor.label_placeholder':
          'खाली छोड़ने पर होस्ट इस्तेमाल होता है',
        'core-url-monitor-editor.label': 'नाम',
        'core-url-monitor-editor.interval': 'कितनी बार',
        'core-url-monitor-editor.follow_redirects': 'रीडायरेक्ट का अनुसरण करें',
        'core-url-monitor-editor.follow_redirects_help':
          'हर रीडायरेक्ट को नए गंतव्य के रूप में जाँचा जाता है। अस्वीकृत निजी होस्ट के रीडायरेक्ट को अनुसरण करने के बजाय अस्वीकार किया जाता है।',
        'core-url-monitor-editor.ask_if_changed': 'बदला है या नहीं पूछें',
        'core-url-monitor-editor.ask_if_changed_help':
          'पिछला ETag भेजता है, इसलिए अपरिवर्तित पेज बिना बॉडी के 304 लौटाता है। 304 को स्वस्थ माना जाता है और सामग्री जाँच छोड़ दी जाती है क्योंकि जाँचने के लिए बॉडी नहीं है।',
      }),
      ar: Object.freeze({
        'core-url-monitor-editor.editing': 'تحرير {{label}}',
        'core-url-monitor-editor.add_check': 'أضف فحصًا',
        'core-url-monitor-editor.address_placeholder': 'https://example.com/',
        'core-url-monitor-editor.address': 'العنوان',
        'core-url-monitor-editor.address_help':
          'العنوان الذي سيكتبه شخص فعليًا.',
        'core-url-monitor-editor.label_placeholder':
          'اتركه فارغًا لاستخدام المضيف',
        'core-url-monitor-editor.label': 'الاسم',
        'core-url-monitor-editor.interval': 'كم مرة',
        'core-url-monitor-editor.follow_redirects': 'اتبع عمليات إعادة التوجيه',
        'core-url-monitor-editor.follow_redirects_help':
          'يُفحص كل تحويل كوجهة جديدة. يُرفض التحويل إلى مضيف خاص غير معتمد بدل اتباعه.',
        'core-url-monitor-editor.ask_if_changed': 'اسأل إن كان قد تغير',
        'core-url-monitor-editor.ask_if_changed_help':
          'يرسل آخر ETag، لذا تستجيب الصفحة غير المتغيرة بـ 304 بلا متن. يعد 304 سليمًا ويتخطى فحص المحتوى لأنه لا يوجد متن لفحصه.',
      }),
      zh: Object.freeze({
        'core-url-monitor-editor.editing': '正在编辑 {{label}}',
        'core-url-monitor-editor.add_check': '添加检查',
        'core-url-monitor-editor.address_placeholder': 'https://example.com/',
        'core-url-monitor-editor.address': '地址',
        'core-url-monitor-editor.address_help': '用户实际会输入的地址。',
        'core-url-monitor-editor.label_placeholder': '留空则使用主机名',
        'core-url-monitor-editor.label': '名称',
        'core-url-monitor-editor.interval': '频率',
        'core-url-monitor-editor.follow_redirects': '跟随重定向',
        'core-url-monitor-editor.follow_redirects_help':
          '每个重定向都会作为新目标检查。重定向到未经批准的私有主机将被拒绝，而不是跟随。',
        'core-url-monitor-editor.ask_if_changed': '询问它是否变更',
        'core-url-monitor-editor.ask_if_changed_help':
          '发送上次的 ETag，因此未更改的页面会在没有正文的情况下返回 304。304 视为正常，并跳过内容检查，因为没有可检查的正文。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-url-monitor-editor.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
