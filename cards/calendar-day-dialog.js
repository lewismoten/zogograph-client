/* Core card package: details and actions for entries selected on a calendar day. */
window.ZogographCalendarDayDialogCard = {
  manifest: Object.freeze({
    id: 'core-calendar-day-dialog',
    locales: ['core-calendar-day-dialog.'],
    helpers: [
      'showListDialog',
      'showView',
      'post',
      'renderCalendar',
      'notice',
      'views',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-calendar-day-dialog.kind_domain': 'domain expiry',
        'core-calendar-day-dialog.kind_certificate': 'HTTPS certificate expiry',
        'core-calendar-day-dialog.kind_repeats': 'repeats {{repeats}}',
        'core-calendar-day-dialog.kind_event': 'a date you keep',
        'core-calendar-day-dialog.kind_projection': 'projected from the trend',
        'core-calendar-day-dialog.kind_alert': 'an alert that fired',
        'core-calendar-day-dialog.kind_reset': 'the host restarted',
        'core-calendar-day-dialog.marked_dealt': 'marked dealt with',
        'core-calendar-day-dialog.open_link': 'Open the link',
        'core-calendar-day-dialog.go_to': 'Go to {{page}}',
        'core-calendar-day-dialog.reopen': 'Reopen it',
        'core-calendar-day-dialog.mark_dealt': 'Mark it dealt with',
      }),
      es: Object.freeze({
        'core-calendar-day-dialog.kind_domain': 'vencimiento del dominio',
        'core-calendar-day-dialog.kind_certificate':
          'vencimiento del certificado HTTPS',
        'core-calendar-day-dialog.kind_repeats': 'se repite {{repeats}}',
        'core-calendar-day-dialog.kind_event': 'una fecha que conserva',
        'core-calendar-day-dialog.kind_projection':
          'proyectado a partir de la tendencia',
        'core-calendar-day-dialog.kind_alert': 'una alerta que se activó',
        'core-calendar-day-dialog.kind_reset': 'el host se reinició',
        'core-calendar-day-dialog.marked_dealt': 'marcado como resuelto',
        'core-calendar-day-dialog.open_link': 'Abrir el enlace',
        'core-calendar-day-dialog.go_to': 'Ir a {{page}}',
        'core-calendar-day-dialog.reopen': 'Reabrirlo',
        'core-calendar-day-dialog.mark_dealt': 'Marcar como resuelto',
      }),
      hi: Object.freeze({
        'core-calendar-day-dialog.kind_domain': 'डोमेन समाप्ति',
        'core-calendar-day-dialog.kind_certificate': 'HTTPS प्रमाणपत्र समाप्ति',
        'core-calendar-day-dialog.kind_repeats': '{{repeats}} दोहराता है',
        'core-calendar-day-dialog.kind_event': 'आपकी रखी हुई तारीख',
        'core-calendar-day-dialog.kind_projection': 'रुझान से अनुमानित',
        'core-calendar-day-dialog.kind_alert': 'एक अलर्ट जो चला',
        'core-calendar-day-dialog.kind_reset': 'होस्ट पुनः आरंभ हुआ',
        'core-calendar-day-dialog.marked_dealt': 'निपटाया हुआ चिह्नित',
        'core-calendar-day-dialog.open_link': 'लिंक खोलें',
        'core-calendar-day-dialog.go_to': '{{page}} पर जाएँ',
        'core-calendar-day-dialog.reopen': 'इसे फिर खोलें',
        'core-calendar-day-dialog.mark_dealt': 'निपटाया हुआ चिह्नित करें',
      }),
      ar: Object.freeze({
        'core-calendar-day-dialog.kind_domain': 'انتهاء النطاق',
        'core-calendar-day-dialog.kind_certificate': 'انتهاء شهادة HTTPS',
        'core-calendar-day-dialog.kind_repeats': 'يتكرر {{repeats}}',
        'core-calendar-day-dialog.kind_event': 'تاريخ تحتفظ به',
        'core-calendar-day-dialog.kind_projection': 'متوقع من الاتجاه',
        'core-calendar-day-dialog.kind_alert': 'تنبيه أُطلق',
        'core-calendar-day-dialog.kind_reset': 'أُعيد تشغيل المضيف',
        'core-calendar-day-dialog.marked_dealt': 'تم وضع علامة تمت معالجته',
        'core-calendar-day-dialog.open_link': 'افتح الرابط',
        'core-calendar-day-dialog.go_to': 'انتقل إلى {{page}}',
        'core-calendar-day-dialog.reopen': 'أعد فتحه',
        'core-calendar-day-dialog.mark_dealt': 'ضع علامة تمت معالجته',
      }),
      zh: Object.freeze({
        'core-calendar-day-dialog.kind_domain': '域名到期',
        'core-calendar-day-dialog.kind_certificate': 'HTTPS 证书到期',
        'core-calendar-day-dialog.kind_repeats': '每 {{repeats}} 重复',
        'core-calendar-day-dialog.kind_event': '您保存的日期',
        'core-calendar-day-dialog.kind_projection': '根据趋势预测',
        'core-calendar-day-dialog.kind_alert': '已触发的警报',
        'core-calendar-day-dialog.kind_reset': '主机已重启',
        'core-calendar-day-dialog.marked_dealt': '已标记为已处理',
        'core-calendar-day-dialog.open_link': '打开链接',
        'core-calendar-day-dialog.go_to': '转到 {{page}}',
        'core-calendar-day-dialog.reopen': '重新打开',
        'core-calendar-day-dialog.mark_dealt': '标记为已处理',
      }),
    }),
  }),
  render(data, helpers) {
    const t = (key, values, fallback) =>
      helpers.i18n.t(`core-calendar-day-dialog.${key}`, values, fallback);
    const parts = [];
    for (const entry of data.entries) {
      const row = document.createElement('div');
      row.className = `day-entry level-${entry.level}`;
      const title = document.createElement('strong');
      title.textContent = entry.title;
      row.appendChild(title);
      const kind = document.createElement('span');
      kind.className = 'muted small';
      const kinds = {
        domain: t('kind_domain', {}, 'domain expiry'),
        certificate: t('kind_certificate', {}, 'HTTPS certificate expiry'),
        event:
          entry.repeats && entry.repeats !== 'once'
            ? t(
                'kind_repeats',
                { repeats: entry.repeats },
                'repeats {{repeats}}',
              )
            : t('kind_event', {}, 'a date you keep'),
        projection: t('kind_projection', {}, 'projected from the trend'),
        alert: t('kind_alert', {}, 'an alert that fired'),
        reset: t('kind_reset', {}, 'the host restarted'),
      };
      kind.textContent = kinds[entry.kind] || entry.kind;
      row.appendChild(kind);
      if (entry.note) {
        const note = document.createElement('div');
        note.className = 'muted small';
        note.textContent = entry.note;
        row.appendChild(note);
      }
      if (entry.resolved) {
        const done = document.createElement('div');
        done.className = 'band-ok small';
        done.textContent = t('marked_dealt', {}, 'marked dealt with');
        row.appendChild(done);
      }
      const actions = document.createElement('div');
      actions.className = 'inspect-actions';
      if (entry.link) {
        const go = document.createElement('a');
        go.href = entry.link;
        go.target = '_blank';
        go.rel = 'noopener noreferrer';
        go.className = 'link';
        go.textContent = t('open_link', {}, 'Open the link');
        actions.appendChild(go);
      }
      if (entry.view && helpers.views[entry.view]) {
        const go = document.createElement('button');
        go.className = 'link';
        go.textContent = t(
          'go_to',
          { page: helpers.views[entry.view].label },
          'Go to {{page}}',
        );
        go.addEventListener('click', () => {
          helpers.close();
          helpers.showView(entry.view);
        });
        actions.appendChild(go);
      }
      if (entry.kind === 'event') {
        const mark = document.createElement('button');
        mark.className = 'link';
        mark.textContent = entry.resolved
          ? t('reopen', {}, 'Reopen it')
          : t('mark_dealt', {}, 'Mark it dealt with');
        mark.addEventListener('click', async () => {
          try {
            const result = await helpers.post('event-resolve', {
              id: entry.id,
              resolved: entry.resolved ? '0' : '1',
            });
            helpers.close();
            helpers.renderCalendar(result);
          } catch (error) {
            helpers.notice(String(error.message || error));
          }
        });
        actions.appendChild(mark);
      }
      if (actions.childElementCount) row.appendChild(actions);
      parts.push(row);
    }
    helpers.showListDialog(data.title, parts);
  },
};
