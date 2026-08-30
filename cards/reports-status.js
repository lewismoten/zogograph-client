window.ZogographReportsStatusCard = {
  manifest: Object.freeze({
    id: 'core-reports-status',
    locales: ['core-reports-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-reports-status.none':
          'None of the three schedules are switched on, so none are written automatically. Turn them on under Thresholds, or write one now to see what they look like.',
        'core-reports-status.on':
          'Writing {{schedules}}. Each one is emailed when it is written.',
      }),
      es: Object.freeze({
        'core-reports-status.none':
          'Ninguno de los tres horarios está activado, por lo que no se escribe nada automáticamente. Actívelos en Umbrales o escriba uno ahora para ver cómo son.',
        'core-reports-status.on':
          'Escribiendo {{schedules}}. Cada uno se envía por correo cuando se escribe.',
      }),
      hi: Object.freeze({
        'core-reports-status.none':
          'तीनों में से कोई शेड्यूल चालू नहीं है, इसलिए कुछ भी अपने आप नहीं लिखा जाता। उन्हें थ्रेशहोल्ड में चालू करें, या वे कैसे दिखते हैं देखने के लिए अभी एक लिखें।',
        'core-reports-status.on':
          '{{schedules}} लिखे जा रहे हैं। हर एक लिखते समय ईमेल किया जाता है।',
      }),
      ar: Object.freeze({
        'core-reports-status.none':
          'لا يوجد أي من الجداول الثلاثة قيد التشغيل، لذا لا يُكتب شيء تلقائيًا. شغّلها ضمن الحدود، أو اكتب واحدًا الآن لترى شكله.',
        'core-reports-status.on':
          'تجري كتابة {{schedules}}. يُرسل كل واحد بالبريد عند كتابته.',
      }),
      zh: Object.freeze({
        'core-reports-status.none':
          '三个计划均未开启，因此不会自动写出摘要。请在“阈值”中开启它们，或现在写一份以查看效果。',
        'core-reports-status.on':
          '正在撰写 {{schedules}}。每份写成后都会通过电子邮件发送。',
      }),
    }),
  }),
  render(data, helpers) {
    const on = Object.entries(data.on)
      .filter(([, yes]) => yes)
      .map(([key]) => key);
    const said = document.createElement('p');
    said.className = 'muted small';
    said.textContent = on.length
      ? helpers.i18n.t('core-reports-status.on', { schedules: on.join(', ') })
      : helpers.i18n.t('core-reports-status.none', {}, 'No schedules are on.');
    return said;
  },
};
