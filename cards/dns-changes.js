window.ZogographDnsChangesCard = {
  manifest: Object.freeze({
    id: 'core-dns-changes',
    locales: ['core-dns-changes.'],
    helpers: ['target', 'note', 'renderTable', 'whenSpan', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-dns-changes.empty':
          'Nothing has changed since this started watching.',
        'core-dns-changes.appeared': 'Appeared',
        'core-dns-changes.disappeared': 'Disappeared',
        'core-dns-changes.when': 'When',
        'core-dns-changes.what': 'What',
        'core-dns-changes.name': 'Name',
        'core-dns-changes.type': 'Type',
        'core-dns-changes.value': 'Value',
      }),
      es: Object.freeze({
        'core-dns-changes.empty':
          'No ha cambiado nada desde que se inició la vigilancia.',
        'core-dns-changes.appeared': 'Apareció',
        'core-dns-changes.disappeared': 'Desapareció',
        'core-dns-changes.when': 'Cuándo',
        'core-dns-changes.what': 'Qué',
        'core-dns-changes.name': 'Nombre',
        'core-dns-changes.type': 'Tipo',
        'core-dns-changes.value': 'Valor',
      }),
      hi: Object.freeze({
        'core-dns-changes.empty':
          'निगरानी शुरू होने के बाद से कुछ भी नहीं बदला है।',
        'core-dns-changes.appeared': 'दिखाई दिया',
        'core-dns-changes.disappeared': 'गायब हुआ',
        'core-dns-changes.when': 'कब',
        'core-dns-changes.what': 'क्या',
        'core-dns-changes.name': 'नाम',
        'core-dns-changes.type': 'प्रकार',
        'core-dns-changes.value': 'मान',
      }),
      ar: Object.freeze({
        'core-dns-changes.empty': 'لم يتغير شيء منذ بدء المراقبة.',
        'core-dns-changes.appeared': 'ظهر',
        'core-dns-changes.disappeared': 'اختفى',
        'core-dns-changes.when': 'متى',
        'core-dns-changes.what': 'ما الذي',
        'core-dns-changes.name': 'الاسم',
        'core-dns-changes.type': 'النوع',
        'core-dns-changes.value': 'القيمة',
      }),
      zh: Object.freeze({
        'core-dns-changes.empty': '自开始监测以来没有任何变化。',
        'core-dns-changes.appeared': '已出现',
        'core-dns-changes.disappeared': '已消失',
        'core-dns-changes.when': '时间',
        'core-dns-changes.what': '内容',
        'core-dns-changes.name': '名称',
        'core-dns-changes.type': '类型',
        'core-dns-changes.value': '值',
      }),
    }),
  }),
  render(data, helpers) {
    if (!data.changes.length) {
      helpers.target.replaceChildren(
        helpers.note(
          helpers.i18n.t(
            'core-dns-changes.empty',
            {},
            'Nothing has changed since this started watching.',
          ),
        ),
      );
      return;
    }
    const holder = document.createElement('div');
    holder.id = 'dnsChangeTable';
    helpers.target.replaceChildren(holder);
    helpers.renderTable(
      'dnsChangeTable',
      data.changes,
      [
        {
          label: helpers.i18n.t('core-dns-changes.when', {}, 'When'),
          render: (row) => helpers.whenSpan(row.at),
        },
        {
          label: helpers.i18n.t('core-dns-changes.what', {}, 'What'),
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = helpers.i18n.t(
              row.change === 'added'
                ? 'core-dns-changes.appeared'
                : 'core-dns-changes.disappeared',
              {},
              row.change === 'added' ? 'Appeared' : 'Disappeared',
            );
            span.className = row.change === 'added' ? 'band-ok' : 'band-warn';
            return span;
          },
        },
        {
          label: helpers.i18n.t('core-dns-changes.name', {}, 'Name'),
          className: 'name',
          render: (row) => row.name,
        },
        {
          label: helpers.i18n.t('core-dns-changes.type', {}, 'Type'),
          render: (row) => row.type,
        },
        {
          label: helpers.i18n.t('core-dns-changes.value', {}, 'Value'),
          className: 'cmd',
          render: (row) => row.value,
        },
      ],
      null,
      null,
      (row) => String(row.id),
    );
  },
};
