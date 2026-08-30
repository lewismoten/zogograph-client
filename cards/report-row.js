window.ZogographReportRowCard = {
  manifest: Object.freeze({
    id: 'core-report-row',
    locales: ['core-report-row.'],
    helpers: [
      'whenRange',
      'whenTitle',
      'whenLabel',
      'renderMarkdown',
      'remove',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-report-row.period_summary': '{{period}} summary',
        'core-report-row.emailed': 'emailed {{when}}',
        'core-report-row.not_emailed': 'not emailed',
        'core-report-row.cost_tip': 'What writing it cost.',
        'core-report-row.delete_confirm': 'Delete this summary?',
        'core-report-row.delete_title': 'Delete summary',
      }),
      es: Object.freeze({
        'core-report-row.period_summary': 'Resumen de {{period}}',
        'core-report-row.emailed': 'enviado por correo {{when}}',
        'core-report-row.not_emailed': 'no enviado por correo',
        'core-report-row.cost_tip': 'Lo que costó escribirlo.',
        'core-report-row.delete_confirm': '¿Eliminar este resumen?',
        'core-report-row.delete_title': 'Eliminar resumen',
      }),
      hi: Object.freeze({
        'core-report-row.period_summary': '{{period}} सारांश',
        'core-report-row.emailed': '{{when}} ईमेल किया गया',
        'core-report-row.not_emailed': 'ईमेल नहीं किया गया',
        'core-report-row.cost_tip': 'इसे लिखने की लागत।',
        'core-report-row.delete_confirm': 'क्या यह सारांश हटाना है?',
        'core-report-row.delete_title': 'सारांश हटाएँ',
      }),
      ar: Object.freeze({
        'core-report-row.period_summary': 'ملخص {{period}}',
        'core-report-row.emailed': 'أُرسل بالبريد {{when}}',
        'core-report-row.not_emailed': 'لم يُرسل بالبريد',
        'core-report-row.cost_tip': 'ما كلفته كتابته.',
        'core-report-row.delete_confirm': 'هل تريد حذف هذا الملخص؟',
        'core-report-row.delete_title': 'حذف الملخص',
      }),
      zh: Object.freeze({
        'core-report-row.period_summary': '{{period}}摘要',
        'core-report-row.emailed': '已于 {{when}} 发送电子邮件',
        'core-report-row.not_emailed': '未发送电子邮件',
        'core-report-row.cost_tip': '撰写此摘要的成本。',
        'core-report-row.delete_confirm': '删除此摘要？',
        'core-report-row.delete_title': '删除摘要',
      }),
    }),
  }),
  render(data, helpers) {
    const report = data.report;
    const card = document.createElement('div');
    card.className = 'report';
    const head = document.createElement('div');
    head.className = 'report-head';
    const title = document.createElement('strong');
    title.textContent = helpers.i18n.t(
      'core-report-row.period_summary',
      { period: data.periods[report.period]?.label ?? report.period },
      '{{period}} summary',
    );
    head.appendChild(title);
    const covers = document.createElement('span');
    covers.className = 'muted small';
    covers.textContent = helpers.whenRange(
      report.covers_from,
      report.covers_to,
    );
    covers.title = `${helpers.whenTitle(report.covers_from)}\n\nto\n\n${helpers.whenTitle(report.covers_to)}`;
    head.appendChild(covers);
    const sent = document.createElement('span');
    sent.className = 'muted small';
    sent.textContent = report.emailed_at
      ? helpers.i18n.t(
          'core-report-row.emailed',
          { when: helpers.whenLabel(report.emailed_at) },
          'emailed {{when}}',
        )
      : helpers.i18n.t('core-report-row.not_emailed', {}, 'not emailed');
    if (report.emailed_at) sent.title = helpers.whenTitle(report.emailed_at);
    head.appendChild(sent);
    if (report.cost && report.cost !== '—') {
      const cost = document.createElement('span');
      cost.className = 'muted small';
      cost.textContent = report.cost;
      cost.title = helpers.i18n.t(
        'core-report-row.cost_tip',
        {},
        'What writing it cost.',
      );
      head.appendChild(cost);
    }
    const drop = document.createElement('button');
    drop.className = 'link danger small';
    drop.textContent = helpers.i18n.t('action.delete', {}, 'Delete');
    drop.addEventListener('click', () =>
      helpers.remove(
        report,
        helpers.i18n.t(
          'core-report-row.delete_confirm',
          {},
          'Delete this summary?',
        ),
        helpers.i18n.t('core-report-row.delete_title', {}, 'Delete summary'),
      ),
    );
    head.appendChild(drop);
    card.appendChild(head);
    const text = document.createElement('div');
    text.className = 'report-body';
    text.appendChild(helpers.renderMarkdown(report.body));
    card.appendChild(text);
    return card;
  },
};
