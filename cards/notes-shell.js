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
      }),
      es: Object.freeze({
        'core-notes-shell.title': 'Notas',
        'core-notes-shell.add': 'Escribir una',
        'core-notes-shell.write_title': 'Escribir una nota',
        'core-notes-shell.help':
          'Cada gráfico responde {{what}}. Ninguno responde {{why}}, y el porqué casi nunca está en los datos: alguien actualizó PHP un martes o se movió la ventana de copia de seguridad. Seis meses después el gráfico aún muestra el salto y nadie recuerda la razón. Una nota queda fijada a un momento y se dibuja en los gráficos a los que pertenece.',
      }),
      hi: Object.freeze({
        'core-notes-shell.title': 'नोट्स',
        'core-notes-shell.add': 'एक लिखें',
        'core-notes-shell.write_title': 'नोट लिखें',
        'core-notes-shell.help':
          'यहाँ हर चार्ट {{what}} का उत्तर देता है। कोई {{why}} का उत्तर नहीं देता और कारण लगभग कभी डेटा में नहीं होता—किसी ने मंगलवार को PHP अपग्रेड किया या बैकअप विंडो बदली। छह महीने बाद भी चार्ट कदम दिखाता है और किसी को कारण याद नहीं होता। नोट एक क्षण पर पिन होता है और उन चार्टों पर बनाया जाता है जिनसे वह जुड़ा है।',
      }),
      ar: Object.freeze({
        'core-notes-shell.title': 'الملاحظات',
        'core-notes-shell.add': 'اكتب واحدة',
        'core-notes-shell.write_title': 'اكتب ملاحظة',
        'core-notes-shell.help':
          'يجيب كل مخطط هنا عن {{what}}. لا يجيب أي منها عن {{why}}، والسبب لا يكون في البيانات تقريبًا — فقد يكون أحدهم رقّى PHP يوم الثلاثاء أو تغيرت نافذة النسخ الاحتياطي. بعد ستة أشهر لا يزال المخطط يظهر القفزة ولا يتذكر أحد السبب. تُثبّت ملاحظة عند لحظة وتُرسم على المخططات التي تنتمي إليها.',
      }),
      zh: Object.freeze({
        'core-notes-shell.title': '备注',
        'core-notes-shell.add': '写一条',
        'core-notes-shell.write_title': '写一条备注',
        'core-notes-shell.help':
          '这里的每张图表都回答{{what}}。没有一张回答{{why}}，而原因几乎从不在数据中——可能是有人在周二升级了 PHP，或备份窗口发生了变化。六个月后图表仍显示这次跳变，却没人记得原因。一条备注会固定到某个时刻，并绘制在它所属的图表上。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-notes-shell.${key}`,
      {},
      this.manifest.catalogs.en[`core-notes-shell.${key}`],
    );
  },
  render() {},
};
