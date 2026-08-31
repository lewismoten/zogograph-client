window.ZogographUrlsEmptyCard = {
  manifest: Object.freeze({
    id: 'core-urls-empty',
    locales: ['core-urls-empty.'],
    helpers: ['target', 'hideHistory', 'note', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-urls-empty.title': 'Is the site answering?',
        'core-urls-empty.add_check': 'Add a check',
        'core-urls-empty.help':
          'Everything else here watches the machine from the inside, which cannot answer the question a visitor is asking. A server can be healthy by every internal measure and still return 500 because a config file is wrong, or return 200 with an empty page because a database is down — nothing in {{proc}} knows. These are checked from this machine, so they cannot tell you the site is unreachable from somewhere else.',
        'core-urls-empty.message':
          'Nothing is being watched. Add the address somebody would actually type — the home page, or a health endpoint that touches the database.',
      }),
      es: Object.freeze({
        'core-urls-empty.title': '¿Responde el sitio?',
        'core-urls-empty.add_check': 'Añadir una comprobación',
        'core-urls-empty.help':
          'Todo lo demás aquí observa la máquina desde dentro, lo que no puede responder a la pregunta de un visitante. Un servidor puede estar sano según toda medida interna y aun así devolver 500 porque un archivo de configuración está mal, o devolver 200 con una página vacía porque la base de datos está caída: {{proc}} no sabe nada de eso. Estas comprobaciones se hacen desde esta máquina, así que no pueden indicar que el sitio sea inaccesible desde otro lugar.',
        'core-urls-empty.message':
          'No se vigila nada. Añada la dirección que alguien escribiría realmente: la página de inicio o un punto de comprobación de estado que consulte la base de datos.',
      }),
      hi: Object.freeze({
        'core-urls-empty.title': 'क्या साइट जवाब दे रही है?',
        'core-urls-empty.add_check': 'जाँच जोड़ें',
        'core-urls-empty.help':
          'यहाँ बाकी सब मशीन को अंदर से देखता है, जो आगंतुक के सवाल का जवाब नहीं दे सकता। हर आंतरिक माप से स्वस्थ सर्वर भी गलत कॉन्फ़िग फ़ाइल के कारण 500 लौटा सकता है, या डेटाबेस बंद होने पर खाली पेज के साथ 200 लौटा सकता है—{{proc}} को इनमें से कुछ पता नहीं होता। इनकी जाँच इसी मशीन से होती है, इसलिए वे नहीं बता सकते कि साइट कहीं और से पहुंच से बाहर है।',
        'core-urls-empty.message':
          'कुछ भी नहीं देखा जा रहा है। वह पता जोड़ें जिसे कोई वास्तव में लिखेगा — मुखपृष्ठ, या डेटाबेस को छूने वाला स्वास्थ्य एंडपॉइंट।',
      }),
      ar: Object.freeze({
        'core-urls-empty.title': 'هل يجيب الموقع؟',
        'core-urls-empty.add_check': 'أضف فحصًا',
        'core-urls-empty.help':
          'كل ما عدا ذلك هنا يراقب الجهاز من الداخل، ولا يستطيع الإجابة عن سؤال الزائر. قد يكون الخادم سليمًا بكل المقاييس الداخلية ومع ذلك يعيد 500 لأن ملف إعداد غير صحيح، أو يعيد 200 مع صفحة فارغة لأن قاعدة البيانات متوقفة — لا يعرف {{proc}} شيئًا من ذلك. تُفحص هذه من هذا الجهاز، فلا يمكنها إخبارك إن كان الموقع متعذر الوصول إليه من مكان آخر.',
        'core-urls-empty.message':
          'لا يجري مراقبة شيء. أضف العنوان الذي سيكتبه شخص فعليًا — الصفحة الرئيسية أو نقطة فحص حالة تصل إلى قاعدة البيانات.',
      }),
      zh: Object.freeze({
        'core-urls-empty.title': '网站有响应吗？',
        'core-urls-empty.add_check': '添加检查',
        'core-urls-empty.help':
          '此处其他功能都是从服务器内部观察机器，无法回答访客的问题。服务器的所有内部指标都可能健康，但仍可能因配置文件错误而返回 500，或因数据库宕机而返回空页面的 200——{{proc}} 对此一无所知。这些检查从本机发起，因此无法说明网站是否无法从其他位置访问。',
        'core-urls-empty.message':
          '当前没有监视任何内容。请添加用户实际会输入的地址——主页，或会访问数据库的健康检查端点。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-urls-empty.${key}`,
      {},
      this.manifest.catalogs.en[`core-urls-empty.${key}`],
    );
  },
  render(data, helpers) {
    if (data.rows.length) return;
    helpers.target.replaceChildren(
      helpers.note(
        helpers.i18n.t(
          'core-urls-empty.message',
          {},
          'Nothing is being watched.',
        ),
      ),
    );
    helpers.hideHistory();
  },
};
