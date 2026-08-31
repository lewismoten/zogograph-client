/* Core card package: on-page highlight feedback sent through chat. */
window.ZogographChatHighlightCard = {
  manifest: Object.freeze({
    id: 'core-chat-highlight',
    locales: ['core-chat-highlight.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-highlight.no_query': 'Nothing to look for.',
        'core-chat-highlight.no_page': 'No page is open.',
        'core-chat-highlight.not_found':
          'Nothing on the {{page}} page contains that text, so nothing was highlighted. The user may be on a different page — open_page first — or it may not be shown there at all. Do not describe a highlight that did not happen.',
        'core-chat-highlight.reason': '{{assistant}}: {{reason}}',
        'core-chat-highlight.shown':
          'It is outlined and pulsing for a few seconds. Say what you are pointing at in the same message; a highlight with no sentence is a poke.',
      }),
      es: Object.freeze({
        'core-chat-highlight.no_query': 'No hay nada que buscar.',
        'core-chat-highlight.no_page': 'No hay ninguna página abierta.',
        'core-chat-highlight.not_found':
          'Nada en la página {{page}} contiene ese texto, por lo que no se resaltó nada. El usuario podría estar en otra página — abra open_page primero — o quizá no se muestre allí. No describa un resaltado que no ocurrió.',
        'core-chat-highlight.reason': '{{assistant}}: {{reason}}',
        'core-chat-highlight.shown':
          'Está delineado y pulsando durante unos segundos. Diga a qué apunta en el mismo mensaje; un resaltado sin una oración es un toque.',
      }),
      hi: Object.freeze({
        'core-chat-highlight.no_query': 'खोजने के लिए कुछ नहीं है।',
        'core-chat-highlight.no_page': 'कोई पृष्ठ खुला नहीं है।',
        'core-chat-highlight.not_found':
          '{{page}} पृष्ठ पर उस पाठ वाला कुछ नहीं है, इसलिए कुछ भी हाइलाइट नहीं किया गया। उपयोगकर्ता किसी दूसरे पृष्ठ पर हो सकता है — पहले open_page खोलें — या वह वहाँ बिल्कुल नहीं दिखता होगा। ऐसे हाइलाइट का वर्णन न करें जो हुआ ही नहीं।',
        'core-chat-highlight.reason': '{{assistant}}: {{reason}}',
        'core-chat-highlight.shown':
          'यह कुछ सेकंड के लिए रूपरेखा में और धड़कता हुआ है। उसी संदेश में बताएं कि आप किस ओर इशारा कर रहे हैं; वाक्य के बिना हाइलाइट केवल एक इशारा है।',
      }),
      ar: Object.freeze({
        'core-chat-highlight.no_query': 'لا شيء للبحث عنه.',
        'core-chat-highlight.no_page': 'لا توجد صفحة مفتوحة.',
        'core-chat-highlight.not_found':
          'لا يحتوي شيء في صفحة {{page}} على ذلك النص، لذلك لم يُبرز شيء. قد يكون المستخدم في صفحة أخرى — افتح open_page أولاً — أو قد لا يظهر هناك أصلًا. لا تصف إبرازًا لم يحدث.',
        'core-chat-highlight.reason': '{{assistant}}: {{reason}}',
        'core-chat-highlight.shown':
          'يظهر محددًا ونابضًا لبضع ثوانٍ. وضّح ما تشير إليه في الرسالة نفسها؛ فالإبراز بلا جملة مجرد نكزة.',
      }),
      zh: Object.freeze({
        'core-chat-highlight.no_query': '没有可查找的内容。',
        'core-chat-highlight.no_page': '没有打开的页面。',
        'core-chat-highlight.not_found':
          '{{page}} 页面上没有包含该文本的内容，因此没有突出显示任何项目。用户可能在其他页面上——请先打开 open_page——或者该内容根本未显示在此处。请勿描述并未发生的突出显示。',
        'core-chat-highlight.reason': '{{assistant}}：{{reason}}',
        'core-chat-highlight.shown':
          '它会在几秒钟内显示轮廓并闪动。请在同一条消息中说明您指向的内容；没有句子的突出显示只是一次指点。',
      }),
    }),
  }),
  text(key, variables, helpers) {
    return helpers.i18n.t(
      `core-chat-highlight.${key}`,
      variables,
      this.manifest.catalogs.en[`core-chat-highlight.${key}`],
    );
  },
  render(_input, helpers) {
    return this.text('shown', {}, helpers);
  },
};
