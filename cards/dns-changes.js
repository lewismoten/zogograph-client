window.ZogographDnsChangesCard = {
  manifest: Object.freeze({
    id: 'core-dns-changes',
    locales: ['core-dns-changes.'],
    helpers: ['target', 'note', 'renderTable', 'whenSpan', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-dns-changes.title': 'What changed',
        'core-dns-changes.help':
          'A record that stops resolving is kept and marked gone rather than deleted — ‘the MX disappeared last Tuesday’ is the question somebody asks a week later, and a deleted row cannot answer it.',
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
        'core-dns-changes.title': 'Qué cambió',
        'core-dns-changes.help':
          'Un registro que deja de resolver se conserva y se marca como desaparecido, en lugar de borrarlo: «el MX desapareció el martes pasado» es la pregunta que alguien hace una semana después, y una fila eliminada no puede responderla.',
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
        'core-dns-changes.title': 'क्या बदला',
        'core-dns-changes.help':
          'जो रिकॉर्ड रिज़ॉल्व होना बंद कर दे, उसे मिटाने के बजाय रखा और गायब के रूप में चिह्नित किया जाता है—‘MX पिछले मंगलवार गायब हो गया’ वह सवाल है जो कोई एक सप्ताह बाद पूछता है, और हटाई गई पंक्ति उसका उत्तर नहीं दे सकती।',
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
        'core-dns-changes.title': 'ما الذي تغير',
        'core-dns-changes.help':
          'يُحتفظ بالسجل الذي يتوقف عن الحل ويُعلّم بأنه اختفى بدل حذفه — «اختفى MX الثلاثاء الماضي» سؤال سيطرحه أحد بعد أسبوع، ولا يمكن لصف محذوف الإجابة عنه.',
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
        'core-dns-changes.title': '发生了什么变化',
        'core-dns-changes.help':
          '停止解析的记录会保留并标为已消失，而不是删除——“MX 上周二消失了”是有人一周后会问的问题，已删除的行无法回答。',
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
  text(key, helpers) {
    return helpers.i18n.t(
      `core-dns-changes.${key}`,
      {},
      this.manifest.catalogs.en[`core-dns-changes.${key}`],
    );
  },
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
