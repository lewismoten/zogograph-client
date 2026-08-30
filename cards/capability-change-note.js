window.ZogographCapabilityChangeNoteCard = {
  manifest: Object.freeze({
    id: 'core-capability-change-note',
    locales: ['core-capability-change-note.'],
    helpers: ['target', 'count', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-capability-change-note.summary':
          '{{changes}} in this period, one bar per {{unit}}.',
        'core-capability-change-note.truncated':
          'Only the most recent {{shown, number}} are listed when a bar is clicked.',
        'core-capability-change-note.detail': 'Click a bar for the detail.',
      }),
      es: Object.freeze({
        'core-capability-change-note.summary':
          '{{changes}} en este período; una barra por {{unit}}.',
        'core-capability-change-note.truncated':
          'Al hacer clic en una barra, solo se enumeran los {{shown, number}} más recientes.',
        'core-capability-change-note.detail':
          'Haga clic en una barra para ver el detalle.',
      }),
      hi: Object.freeze({
        'core-capability-change-note.summary':
          'इस अवधि में {{changes}}; प्रति {{unit}} एक बार।',
        'core-capability-change-note.truncated':
          'बार पर क्लिक करने पर केवल सबसे हाल के {{shown, number}} सूचीबद्ध होते हैं।',
        'core-capability-change-note.detail':
          'विवरण के लिए किसी बार पर क्लिक करें।',
      }),
      ar: Object.freeze({
        'core-capability-change-note.summary':
          '{{changes}} في هذه الفترة، شريط واحد لكل {{unit}}.',
        'core-capability-change-note.truncated':
          'لا تُسرد عند النقر على شريط إلا أحدث {{shown, number}}.',
        'core-capability-change-note.detail': 'انقر على شريط للتفاصيل.',
      }),
      zh: Object.freeze({
        'core-capability-change-note.summary':
          '此期间有 {{changes}}，每 {{unit}} 一个柱条。',
        'core-capability-change-note.truncated':
          '点击柱条时只列出最近的 {{shown, number}} 条。',
        'core-capability-change-note.detail': '点击柱条查看详情。',
      }),
    }),
  }),
  render(data, helpers) {
    const parts = [
      helpers.i18n.t(
        'core-capability-change-note.summary',
        {
          changes: helpers.count(data.total, 'change'),
          unit: data.bucket_unit,
        },
        '{{changes}} in this period, one bar per {{unit}}.',
      ),
    ];
    if (data.truncated) {
      parts.push(
        helpers.i18n.t(
          'core-capability-change-note.truncated',
          { shown: data.changes.length },
          'Only the most recent {{shown, number}} are listed when a bar is clicked.',
        ),
      );
    }
    if (data.total) {
      parts.push(
        helpers.i18n.t(
          'core-capability-change-note.detail',
          {},
          'Click a bar for the detail.',
        ),
      );
    }
    helpers.target.textContent = parts.join(' ');
  },
};
