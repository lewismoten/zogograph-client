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
