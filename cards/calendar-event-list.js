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
      }),
      es: Object.freeze({
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
      }),
      hi: Object.freeze({
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
      }),
      ar: Object.freeze({
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
      }),
      zh: Object.freeze({
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
      }),
    }),
  }),

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
