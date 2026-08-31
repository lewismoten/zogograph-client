/* Core card package: the calendar's editable event inventory. */
window.ZogographCalendarEventListCard = {
  manifest: Object.freeze({
    id: 'core-calendar-event-list',
    locales: ['core-calendar-event-list.'],
    helpers: [
      'target',
      'note',
      'renderTable',
      'humanTime',
      'openForm',
      'confirmDialog',
      'post',
      'renderCalendar',
      'notice',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-calendar-event-list.title': 'Dates you keep',
        'core-calendar-event-list.help':
          'Marked {{important}} means it appears on the dashboard and in the alert emails as it approaches, until you mark it dealt with. Marking it dealt with is reversible — it is a claim somebody makes, and sometimes makes wrongly.',
        'core-calendar-event-list.empty':
          'Nothing kept yet. Renewals, invoices, certificate purchases — anything with a date that this machine could not work out for itself.',
        'core-calendar-event-list.what': 'What',
        'core-calendar-event-list.when': 'When',
        'core-calendar-event-list.repeats': 'Repeats',
        'core-calendar-event-list.important': 'important',
        'core-calendar-event-list.important_help':
          'Appears on the dashboard and in the alert emails as it approaches.',
        'core-calendar-event-list.dealt_with': 'Dealt with',
        'core-calendar-event-list.note': 'Note',
        'core-calendar-event-list.edit': 'Edit',
        'core-calendar-event-list.remove': 'Remove',
        'core-calendar-event-list.remove_confirm': 'Remove "{{title}}"?',
        'core-calendar-event-list.remove_title': 'Remove event',
        'core-calendar-event-list.editing': 'Editing {{title}}',
        'core-calendar-event-list.title_placeholder': 'VPS renews',
        'core-calendar-event-list.repeats_help':
          'A monthly date set for the 31st falls on the last day of shorter months, not the 3rd of the next one.',
        'core-calendar-event-list.link': 'Link',
        'core-calendar-event-list.link_placeholder':
          'https://billing.example.com/',
        'core-calendar-event-list.link_help':
          'Optional. Where you go to deal with it.',
        'core-calendar-event-list.important_until_dealt':
          'Appears on the dashboard and in the alert emails as it approaches, until it is marked dealt with.',
        'core-calendar-event-list.saved': 'Saved.',
      }),
      es: Object.freeze({
        'core-calendar-event-list.title': 'Fechas que conserva',
        'core-calendar-event-list.help':
          'Marcado como {{important}} significa que aparece en el tablero y en los correos de alerta a medida que se acerca, hasta que lo marque como resuelto. Marcarlo como resuelto es reversible: es una afirmación que alguien hace, y a veces hace erróneamente.',
        'core-calendar-event-list.empty':
          'Aún no hay nada guardado. Renovaciones, facturas, compras de certificados: cualquier cosa con una fecha que esta máquina no pueda calcular por sí misma.',
        'core-calendar-event-list.what': 'Qué',
        'core-calendar-event-list.when': 'Cuándo',
        'core-calendar-event-list.repeats': 'Se repite',
        'core-calendar-event-list.important': 'importante',
        'core-calendar-event-list.important_help':
          'Aparece en el panel y en los correos de alerta a medida que se acerca.',
        'core-calendar-event-list.dealt_with': 'Resuelto',
        'core-calendar-event-list.note': 'Nota',
        'core-calendar-event-list.edit': 'Editar',
        'core-calendar-event-list.remove': 'Eliminar',
        'core-calendar-event-list.remove_confirm': '¿Eliminar "{{title}}"?',
        'core-calendar-event-list.remove_title': 'Eliminar evento',
        'core-calendar-event-list.editing': 'Editando {{title}}',
        'core-calendar-event-list.title_placeholder': 'Renovación del VPS',
        'core-calendar-event-list.repeats_help':
          'Una fecha mensual fijada para el día 31 cae el último día de los meses más cortos, no el día 3 del mes siguiente.',
        'core-calendar-event-list.link': 'Enlace',
        'core-calendar-event-list.link_placeholder':
          'https://facturacion.ejemplo.com/',
        'core-calendar-event-list.link_help':
          'Opcional. Adonde va para resolverlo.',
        'core-calendar-event-list.important_until_dealt':
          'Aparece en el panel y en los correos de alerta a medida que se acerca, hasta que lo marque como resuelto.',
        'core-calendar-event-list.saved': 'Guardado.',
      }),
      hi: Object.freeze({
        'core-calendar-event-list.title': 'वे तारीखें जिन्हें आप रखते हैं',
        'core-calendar-event-list.help':
          '{{important}} चिह्नित होने का अर्थ है कि वह नज़दीक आने पर डैशबोर्ड और अलर्ट ईमेल में दिखाई देगा, जब तक आप उसे निपटा हुआ चिह्नित नहीं करते। उसे निपटा हुआ चिह्नित करना पलटा जा सकता है — यह किसी का दावा है, जो कभी गलत भी हो सकता है।',
        'core-calendar-event-list.empty':
          'अभी कुछ रखा नहीं गया है। नवीनीकरण, इनवॉइस, प्रमाणपत्र खरीद — तारीख वाली कोई भी चीज़ जिसे यह मशीन स्वयं नहीं निकाल सकती।',
        'core-calendar-event-list.what': 'क्या',
        'core-calendar-event-list.when': 'कब',
        'core-calendar-event-list.repeats': 'दोहराता है',
        'core-calendar-event-list.important': 'महत्वपूर्ण',
        'core-calendar-event-list.important_help':
          'पास आने पर यह डैशबोर्ड और चेतावनी ईमेल में दिखता है।',
        'core-calendar-event-list.dealt_with': 'निपटाया गया',
        'core-calendar-event-list.note': 'नोट',
        'core-calendar-event-list.edit': 'संपादित करें',
        'core-calendar-event-list.remove': 'हटाएँ',
        'core-calendar-event-list.remove_confirm': '"{{title}}" हटाएँ?',
        'core-calendar-event-list.remove_title': 'कार्यक्रम हटाएँ',
        'core-calendar-event-list.editing': '{{title}} संपादित कर रहे हैं',
        'core-calendar-event-list.title_placeholder': 'VPS नवीनीकृत होता है',
        'core-calendar-event-list.repeats_help':
          'महीने की 31 तारीख पर रखी गई तारीख छोटे महीनों के अंतिम दिन पर होगी, अगले महीने की 3 तारीख पर नहीं।',
        'core-calendar-event-list.link': 'लिंक',
        'core-calendar-event-list.link_placeholder':
          'https://billing.example.com/',
        'core-calendar-event-list.link_help':
          'वैकल्पिक। इसे निपटाने के लिए जहाँ जाते हैं।',
        'core-calendar-event-list.important_until_dealt':
          'यह निकट आने पर डैशबोर्ड और अलर्ट ईमेल में दिखाई देता है, जब तक इसे निपटाया हुआ चिह्नित न कर दिया जाए।',
        'core-calendar-event-list.saved': 'सहेजा गया।',
      }),
      ar: Object.freeze({
        'core-calendar-event-list.title': 'التواريخ التي تحتفظ بها',
        'core-calendar-event-list.help':
          'تعني علامة {{important}} أنه سيظهر في لوحة المعلومات ورسائل التنبيه عند اقترابه، إلى أن تضع علامة تمت معالجته. يمكن التراجع عن وضع العلامة؛ فهي ادعاء يقدمه شخص وقد يكون خاطئًا أحيانًا.',
        'core-calendar-event-list.empty':
          'لا توجد عناصر محفوظة بعد. التجديدات والفواتير وشراء الشهادات — أي شيء له تاريخ لا تستطيع هذه الآلة حسابه بنفسها.',
        'core-calendar-event-list.what': 'ما هو',
        'core-calendar-event-list.when': 'متى',
        'core-calendar-event-list.repeats': 'يتكرر',
        'core-calendar-event-list.important': 'مهم',
        'core-calendar-event-list.important_help':
          'يظهر في لوحة المعلومات ورسائل التنبيه الإلكترونية عند اقترابه.',
        'core-calendar-event-list.dealt_with': 'تمت معالجته',
        'core-calendar-event-list.note': 'ملاحظة',
        'core-calendar-event-list.edit': 'تعديل',
        'core-calendar-event-list.remove': 'إزالة',
        'core-calendar-event-list.remove_confirm': 'إزالة "{{title}}"؟',
        'core-calendar-event-list.remove_title': 'إزالة الحدث',
        'core-calendar-event-list.editing': 'تحرير {{title}}',
        'core-calendar-event-list.title_placeholder': 'تجديد الخادم الافتراضي',
        'core-calendar-event-list.repeats_help':
          'التاريخ الشهري المحدد في اليوم 31 يقع في آخر يوم من الأشهر الأقصر، لا في اليوم الثالث من الشهر التالي.',
        'core-calendar-event-list.link': 'رابط',
        'core-calendar-event-list.link_placeholder':
          'https://billing.example.com/',
        'core-calendar-event-list.link_help':
          'اختياري. المكان الذي تذهب إليه لمعالجته.',
        'core-calendar-event-list.important_until_dealt':
          'يظهر في لوحة المعلومات ورسائل التنبيه الإلكترونية مع اقترابه، حتى يتم وضع علامة تمت معالجته.',
        'core-calendar-event-list.saved': 'تم الحفظ.',
      }),
      zh: Object.freeze({
        'core-calendar-event-list.title': '您保留的日期',
        'core-calendar-event-list.help':
          '标记为 {{important}} 表示它会在临近时显示在仪表板和警报邮件中，直到您将其标记为已处理。标记为已处理可随时撤销——这是某人作出的判断，也可能判断错误。',
        'core-calendar-event-list.empty':
          '尚未记录任何事项。续订、账单、证书购买——任何这台机器无法自行推算日期的事项。',
        'core-calendar-event-list.what': '事项',
        'core-calendar-event-list.when': '时间',
        'core-calendar-event-list.repeats': '重复',
        'core-calendar-event-list.important': '重要',
        'core-calendar-event-list.important_help':
          '临近时会显示在仪表板和提醒邮件中。',
        'core-calendar-event-list.dealt_with': '已处理',
        'core-calendar-event-list.note': '备注',
        'core-calendar-event-list.edit': '编辑',
        'core-calendar-event-list.remove': '移除',
        'core-calendar-event-list.remove_confirm': '移除“{{title}}”？',
        'core-calendar-event-list.remove_title': '移除事项',
        'core-calendar-event-list.editing': '正在编辑 {{title}}',
        'core-calendar-event-list.title_placeholder': 'VPS 续订',
        'core-calendar-event-list.repeats_help':
          '每月设为 31 日的日期会落在较短月份的最后一天，而不是下个月的 3 日。',
        'core-calendar-event-list.link': '链接',
        'core-calendar-event-list.link_placeholder':
          'https://billing.example.com/',
        'core-calendar-event-list.link_help': '可选。用于处理此事的链接。',
        'core-calendar-event-list.important_until_dealt':
          '随着日期临近，它会显示在仪表板和警报电子邮件中，直到被标记为已处理。',
        'core-calendar-event-list.saved': '已保存。',
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-calendar-event-list.${key}`,
      {},
      this.manifest.catalogs.en[`core-calendar-event-list.${key}`],
    );
  },

  render(data, helpers) {
    const {
      target,
      note,
      renderTable,
      humanTime,
      openForm,
      confirmDialog,
      post,
      renderCalendar,
      notice,
      i18n,
    } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!data.events?.length) {
      target.replaceChildren(
        note(
          t(
            'core-calendar-event-list.empty',
            {},
            'Nothing kept yet. Renewals, invoices, certificate purchases — anything with a date that this machine could not work out for itself.',
          ),
        ),
      );
      return;
    }
    const box = document.createElement('div');
    box.id = 'eventTable';
    target.replaceChildren(box);
    renderTable(
      'eventTable',
      data.events,
      [
        {
          label: t('core-calendar-event-list.what', {}, 'What'),
          className: 'name',
          render: (row) => row.title,
        },
        {
          label: t('core-calendar-event-list.when', {}, 'When'),
          render: (row) => humanTime(row.at).split(',')[0],
        },
        {
          label: t('core-calendar-event-list.repeats', {}, 'Repeats'),
          render: (row) => data.repeats[row.repeats] || row.repeats,
        },
        {
          label: '',
          render: (row) => {
            if (!Number(row.priority)) return '';
            const span = document.createElement('span');
            span.className = 'band-warn small';
            span.textContent = t(
              'core-calendar-event-list.important',
              {},
              'important',
            );
            span.title = t(
              'core-calendar-event-list.important_help',
              {},
              'Appears on the dashboard and in the alert emails as it approaches.',
            );
            return span;
          },
        },
        {
          label: t('core-calendar-event-list.dealt_with', {}, 'Dealt with'),
          render: (row) =>
            row.resolved_at ? humanTime(row.resolved_at).split(',')[0] : '—',
        },
        {
          label: t('core-calendar-event-list.note', {}, 'Note'),
          className: 'cmd',
          render: (row) => row.note || '',
        },
        {
          label: '',
          render: (row) => {
            const wrap = document.createDocumentFragment();
            const edit = document.createElement('button');
            edit.className = 'link';
            edit.textContent = t('core-calendar-event-list.edit', {}, 'Edit');
            edit.addEventListener('click', () => openForm(row));
            wrap.appendChild(edit);
            const remove = document.createElement('button');
            remove.className = 'link danger';
            remove.textContent = t(
              'core-calendar-event-list.remove',
              {},
              'Remove',
            );
            remove.addEventListener('click', async () => {
              if (
                !(await confirmDialog(
                  t(
                    'core-calendar-event-list.remove_confirm',
                    { title: row.title },
                    'Remove "{{title}}"?',
                  ),
                  t(
                    'core-calendar-event-list.remove_title',
                    {},
                    'Remove event',
                  ),
                ))
              )
                return;
              try {
                renderCalendar(await post('event-delete', { id: row.id }));
              } catch (error) {
                notice(String(error.message || error));
              }
            });
            wrap.appendChild(remove);
            return wrap;
          },
        },
      ],
      null,
      null,
      (row) => String(row.id),
    );
  },
};
