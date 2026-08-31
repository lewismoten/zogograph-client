/* Core card package: static heading, action, and guidance for notes. */
window.ZogographNotesShellCard = {
  manifest: Object.freeze({
    id: 'core-notes-shell',
    locales: ['core-notes-shell.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-notes-shell.title': 'Notes',
        'core-notes-shell.add': 'Write one',
        'core-notes-shell.write_title': 'Write a note',
        'core-notes-shell.help':
          'Every chart here answers {{what}}. None of them answer {{why}}, and the why is almost never in the data — it is that somebody upgraded PHP on the Tuesday, or that the backup window moved. Six months later the chart still shows the step and nobody remembers the reason. A note is pinned to a moment and drawn on the charts it belongs to.',
        'core-notes-shell.body_placeholder':
          'Upgraded PHP to 8.4 on the host. The memory step at 14:20 is that.',
        'core-notes-shell.body_label': 'What happened',
        'core-notes-shell.when_label': 'When',
        'core-notes-shell.when_help':
          'Defaults to now. Move it back if you are writing up something that already happened — the mark belongs where the event was, not where the typing was.',
        'core-notes-shell.scope_label': 'Where it shows',
        'core-notes-shell.charts_label': 'Which charts',
        'core-notes-shell.charts_help':
          'Ticking more than one puts the same mark on each of them.',
        'core-notes-shell.capture_label': 'Also capture',
        'core-notes-shell.take_another_screenshot': 'Take another',
        'core-notes-shell.take_screenshot': 'Take a screenshot',
        'core-notes-shell.discard_screenshot': 'Do not keep it',
        'core-notes-shell.screenshot': 'Screenshot',
        'core-notes-shell.screenshot_help':
          'This dialog closes while the picture is taken and everything you have typed comes back with it. You can crop and black out anything before it is kept.',
        'core-notes-shell.keep_pictures': 'Keep pictures of',
        'core-notes-shell.keep_pictures_help':
          'Saved with the note exactly as they are drawn now, so the picture still shows what you saw after retention has rolled the readings up underneath it.',
        'core-notes-shell.no_page_charts': 'There are no charts on this page.',
        'core-notes-shell.no_page_charts_help':
          'The note itself is saved either way, and a mark on this page will show on any chart added here later.',
        'core-notes-shell.saving': 'Saving…',
        'core-notes-shell.saving_and_capturing': 'Saving and capturing…',
        'core-notes-shell.page_label': 'Page',
        'core-notes-shell.page_help': 'Where the mark will be drawn.',
      }),
      es: Object.freeze({
        'core-notes-shell.title': 'Notas',
        'core-notes-shell.add': 'Escribir una',
        'core-notes-shell.write_title': 'Escribir una nota',
        'core-notes-shell.help':
          'Cada gráfico responde {{what}}. Ninguno responde {{why}}, y el porqué casi nunca está en los datos: alguien actualizó PHP un martes o se movió la ventana de copia de seguridad. Seis meses después el gráfico aún muestra el salto y nadie recuerda la razón. Una nota queda fijada a un momento y se dibuja en los gráficos a los que pertenece.',
        'core-notes-shell.body_placeholder':
          'Se actualizó PHP a 8.4 en el host. El salto de memoria a las 14:20 se debe a eso.',
        'core-notes-shell.body_label': 'Qué ocurrió',
        'core-notes-shell.when_label': 'Cuándo',
        'core-notes-shell.when_help':
          'De forma predeterminada es ahora. Muévalo atrás si está anotando algo que ya ocurrió; la marca pertenece al lugar del evento, no al momento de escribir.',
        'core-notes-shell.scope_label': 'Dónde se muestra',
        'core-notes-shell.charts_label': 'Qué gráficos',
        'core-notes-shell.charts_help':
          'Marcar más de uno coloca la misma marca en cada uno.',
        'core-notes-shell.capture_label': 'Capturar también',
        'core-notes-shell.take_another_screenshot': 'Tomar otra',
        'core-notes-shell.take_screenshot': 'Tomar una captura de pantalla',
        'core-notes-shell.discard_screenshot': 'No conservarla',
        'core-notes-shell.screenshot': 'Captura de pantalla',
        'core-notes-shell.screenshot_help':
          'Este diálogo se cierra mientras se toma la imagen y todo lo que haya escrito vuelve con él. Puede recortar y tachar cualquier elemento antes de conservarla.',
        'core-notes-shell.keep_pictures': 'Conservar imágenes de',
        'core-notes-shell.keep_pictures_help':
          'Se guardan con la nota exactamente como se dibujan ahora, para que la imagen siga mostrando lo que vio tras consolidarse las lecturas por retención.',
        'core-notes-shell.no_page_charts': 'No hay gráficos en esta página.',
        'core-notes-shell.no_page_charts_help':
          'La nota se guarda de todos modos, y una marca en esta página aparecerá en cualquier gráfico que se agregue después.',
        'core-notes-shell.saving': 'Guardando…',
        'core-notes-shell.saving_and_capturing': 'Guardando y capturando…',
        'core-notes-shell.page_label': 'Página',
        'core-notes-shell.page_help': 'Dónde se dibujará la marca.',
      }),
      hi: Object.freeze({
        'core-notes-shell.title': 'नोट्स',
        'core-notes-shell.add': 'एक लिखें',
        'core-notes-shell.write_title': 'नोट लिखें',
        'core-notes-shell.help':
          'यहाँ हर चार्ट {{what}} का उत्तर देता है। कोई {{why}} का उत्तर नहीं देता और कारण लगभग कभी डेटा में नहीं होता—किसी ने मंगलवार को PHP अपग्रेड किया या बैकअप विंडो बदली। छह महीने बाद भी चार्ट कदम दिखाता है और किसी को कारण याद नहीं होता। नोट एक क्षण पर पिन होता है और उन चार्टों पर बनाया जाता है जिनसे वह जुड़ा है।',
        'core-notes-shell.body_placeholder':
          'होस्ट पर PHP को 8.4 में अपग्रेड किया। 14:20 पर मेमोरी का बढ़ना उसी कारण है।',
        'core-notes-shell.body_label': 'क्या हुआ',
        'core-notes-shell.when_label': 'कब',
        'core-notes-shell.when_help':
          'डिफ़ॉल्ट रूप से अभी का समय है। यदि आप पहले हुई किसी बात का विवरण लिख रहे हैं तो इसे पीछे करें — निशान घटना के समय का है, लिखने के समय का नहीं।',
        'core-notes-shell.scope_label': 'यह कहाँ दिखेगा',
        'core-notes-shell.charts_label': 'कौन से चार्ट',
        'core-notes-shell.charts_help':
          'एक से अधिक चुनने पर हर एक पर वही निशान लगता है।',
        'core-notes-shell.capture_label': 'यह भी कैप्चर करें',
        'core-notes-shell.take_another_screenshot': 'एक और लें',
        'core-notes-shell.take_screenshot': 'स्क्रीनशॉट लें',
        'core-notes-shell.discard_screenshot': 'इसे न रखें',
        'core-notes-shell.screenshot': 'स्क्रीनशॉट',
        'core-notes-shell.screenshot_help':
          'चित्र लेते समय यह संवाद बंद हो जाता है और आपका लिखा हुआ सब कुछ इसके साथ वापस आता है। रखने से पहले आप किसी भी चीज़ को काट या काला कर सकते हैं।',
        'core-notes-shell.keep_pictures': 'इनके चित्र रखें',
        'core-notes-shell.keep_pictures_help':
          'ये नोट के साथ ठीक वैसे ही सहेजे जाते हैं जैसे अभी दिख रहे हैं, इसलिए रिटेंशन द्वारा रीडिंग समेटे जाने के बाद भी चित्र वही दिखाता है जो आपने देखा था।',
        'core-notes-shell.no_page_charts': 'इस पेज पर कोई चार्ट नहीं है।',
        'core-notes-shell.no_page_charts_help':
          'नोट फिर भी सहेजा जाता है, और इस पेज का चिह्न बाद में जोड़े जाने वाले किसी भी चार्ट पर दिखेगा।',
        'core-notes-shell.saving': 'सहेजा जा रहा है…',
        'core-notes-shell.saving_and_capturing':
          'सहेजा और कैप्चर किया जा रहा है…',
        'core-notes-shell.page_label': 'पृष्ठ',
        'core-notes-shell.page_help': 'जहाँ निशान बनाया जाएगा।',
      }),
      ar: Object.freeze({
        'core-notes-shell.title': 'الملاحظات',
        'core-notes-shell.add': 'اكتب واحدة',
        'core-notes-shell.write_title': 'اكتب ملاحظة',
        'core-notes-shell.help':
          'يجيب كل مخطط هنا عن {{what}}. لا يجيب أي منها عن {{why}}، والسبب لا يكون في البيانات تقريبًا — فقد يكون أحدهم رقّى PHP يوم الثلاثاء أو تغيرت نافذة النسخ الاحتياطي. بعد ستة أشهر لا يزال المخطط يظهر القفزة ولا يتذكر أحد السبب. تُثبّت ملاحظة عند لحظة وتُرسم على المخططات التي تنتمي إليها.',
        'core-notes-shell.body_placeholder':
          'تمت ترقية PHP إلى 8.4 على المضيف. قفزة الذاكرة عند 14:20 سببها ذلك.',
        'core-notes-shell.body_label': 'ما الذي حدث',
        'core-notes-shell.when_label': 'متى',
        'core-notes-shell.when_help':
          'الافتراضي هو الآن. أعده إلى وقت سابق إذا كنت تكتب عن شيء حدث بالفعل — فالعلامة تخص موضع الحدث لا وقت الكتابة.',
        'core-notes-shell.scope_label': 'أين يظهر',
        'core-notes-shell.charts_label': 'أي المخططات',
        'core-notes-shell.charts_help':
          'تحديد أكثر من واحد يضع العلامة نفسها على كل واحد منها.',
        'core-notes-shell.capture_label': 'التقاط أيضاً',
        'core-notes-shell.take_another_screenshot': 'التقط أخرى',
        'core-notes-shell.take_screenshot': 'التقط لقطة شاشة',
        'core-notes-shell.discard_screenshot': 'لا تحتفظ بها',
        'core-notes-shell.screenshot': 'لقطة شاشة',
        'core-notes-shell.screenshot_help':
          'يُغلق هذا الحوار أثناء التقاط الصورة ويعود كل ما كتبته معه. يمكنك اقتصاص أي شيء أو طمسه قبل الاحتفاظ به.',
        'core-notes-shell.keep_pictures': 'احتفظ بصور لـ',
        'core-notes-shell.keep_pictures_help':
          'تُحفظ مع الملاحظة تمامًا كما تُرسم الآن، لذا تظل الصورة تعرض ما رأيته بعد أن يدمج الاحتفاظ القراءات تحتها.',
        'core-notes-shell.no_page_charts': 'لا توجد مخططات في هذه الصفحة.',
        'core-notes-shell.no_page_charts_help':
          'تُحفظ الملاحظة على أي حال، وستظهر علامة في هذه الصفحة على أي مخطط يضاف هنا لاحقًا.',
        'core-notes-shell.saving': 'يجري الحفظ…',
        'core-notes-shell.saving_and_capturing': 'يجري الحفظ والالتقاط…',
        'core-notes-shell.page_label': 'الصفحة',
        'core-notes-shell.page_help': 'حيث سيُرسم التمييز.',
      }),
      zh: Object.freeze({
        'core-notes-shell.title': '备注',
        'core-notes-shell.add': '写一条',
        'core-notes-shell.write_title': '写一条备注',
        'core-notes-shell.help':
          '这里的每张图表都回答{{what}}。没有一张回答{{why}}，而原因几乎从不在数据中——可能是有人在周二升级了 PHP，或备份窗口发生了变化。六个月后图表仍显示这次跳变，却没人记得原因。一条备注会固定到某个时刻，并绘制在它所属的图表上。',
        'core-notes-shell.body_placeholder':
          '已将主机上的 PHP 升级到 8.4。14:20 的内存阶跃由此造成。',
        'core-notes-shell.body_label': '发生了什么',
        'core-notes-shell.when_label': '时间',
        'core-notes-shell.when_help':
          '默认是现在。如果记录的是已经发生的事情，请将时间调早——标记属于事件发生时，而不是输入时。',
        'core-notes-shell.scope_label': '显示位置',
        'core-notes-shell.charts_label': '哪些图表',
        'core-notes-shell.charts_help':
          '勾选多个会在每一个图表上放置相同的标记。',
        'core-notes-shell.capture_label': '同时捕获',
        'core-notes-shell.take_another_screenshot': '再拍一张',
        'core-notes-shell.take_screenshot': '截取屏幕截图',
        'core-notes-shell.discard_screenshot': '不保留它',
        'core-notes-shell.screenshot': '屏幕截图',
        'core-notes-shell.screenshot_help':
          '截取图片时此对话框会关闭，您输入的所有内容都会随之恢复。保留前，您可以裁剪或涂黑任何内容。',
        'core-notes-shell.keep_pictures': '保留以下项目的图片',
        'core-notes-shell.keep_pictures_help':
          '图片会按当前绘制的样子与便笺一起保存，因此即使保留策略汇总了下面的读数，图片仍会显示您当时所见的内容。',
        'core-notes-shell.no_page_charts': '此页面没有图表。',
        'core-notes-shell.no_page_charts_help':
          '便笺仍会保存，且此页面上的标记会显示在之后添加到这里的任何图表中。',
        'core-notes-shell.saving': '正在保存…',
        'core-notes-shell.saving_and_capturing': '正在保存并捕获…',
        'core-notes-shell.page_label': '页面',
        'core-notes-shell.page_help': '标记将绘制在此处。',
      }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-notes-shell.${key}`,
      values,
      this.manifest.catalogs.en[`core-notes-shell.${key}`],
    );
  },
  render() {},
};
