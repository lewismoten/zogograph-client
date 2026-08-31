window.ZogographFetchMetadataCard = {
  manifest: Object.freeze({
    id: 'core-fetch-metadata',
    locales: ['core-fetch-metadata.'],
    helpers: ['note', 'section', 'simpleTable', 'linkifyText', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-fetch-metadata.title': 'Title',
        'core-fetch-metadata.missing_title':
          'This page has no title, which is a real problem for anyone finding it or bookmarking it.',
        'core-fetch-metadata.none': 'There are no meta tags at all.',
        'core-fetch-metadata.what_it_does': 'What it does',
        'core-fetch-metadata.name': 'Name',
        'core-fetch-metadata.value': 'Value',
      }),
      es: Object.freeze({
        'core-fetch-metadata.title': 'Título',
        'core-fetch-metadata.missing_title':
          'Esta página no tiene título, lo que es un problema real para quien la busque o la guarde como marcador.',
        'core-fetch-metadata.none': 'No hay etiquetas meta en absoluto.',
        'core-fetch-metadata.what_it_does': 'Qué hace',
        'core-fetch-metadata.name': 'Nombre',
        'core-fetch-metadata.value': 'Valor',
      }),
      hi: Object.freeze({
        'core-fetch-metadata.title': 'शीर्षक',
        'core-fetch-metadata.missing_title':
          'इस पृष्ठ का कोई शीर्षक नहीं है, जो इसे खोजने या बुकमार्क करने वाले किसी भी व्यक्ति के लिए वास्तविक समस्या है।',
        'core-fetch-metadata.none': 'कोई भी मेटा टैग नहीं है।',
        'core-fetch-metadata.what_it_does': 'यह क्या करता है',
        'core-fetch-metadata.name': 'नाम',
        'core-fetch-metadata.value': 'मान',
      }),
      ar: Object.freeze({
        'core-fetch-metadata.title': 'العنوان',
        'core-fetch-metadata.missing_title':
          'لا تحتوي هذه الصفحة على عنوان، وهذه مشكلة فعلية لأي شخص يبحث عنها أو يضعها في الإشارات المرجعية.',
        'core-fetch-metadata.none': 'لا توجد أي وسوم meta على الإطلاق.',
        'core-fetch-metadata.what_it_does': 'ما الذي يفعله',
        'core-fetch-metadata.name': 'الاسم',
        'core-fetch-metadata.value': 'القيمة',
      }),
      zh: Object.freeze({
        'core-fetch-metadata.title': '标题',
        'core-fetch-metadata.missing_title':
          '此页面没有标题，对于查找它或为其添加书签的任何人来说都是一个实际问题。',
        'core-fetch-metadata.none': '完全没有元标记。',
        'core-fetch-metadata.what_it_does': '作用',
        'core-fetch-metadata.name': '名称',
        'core-fetch-metadata.value': '值',
      }),
    }),
  }),
  render(data, helpers) {
    const wrap = document.createDocumentFragment();
    const meta = data.meta || { tags: [] };

    if (meta.error) {
      wrap.appendChild(helpers.note(meta.error));
      return wrap;
    }

    const title = document.createElement('p');
    title.className = meta.title ? 'page-title' : 'page-title muted';
    title.textContent = meta.title
      ? meta.title
      : helpers.i18n.t(
          'core-fetch-metadata.missing_title',
          {},
          'This page has no title, which is a real problem for anyone finding it or bookmarking it.',
        );
    wrap.appendChild(
      helpers.section(
        helpers.i18n.t('core-fetch-metadata.title', {}, 'Title'),
        title,
      ),
    );

    if (!meta.tags.length) {
      wrap.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-fetch-metadata.none',
            {},
            'There are no meta tags at all.',
          ),
        ),
      );
      return wrap;
    }

    let group = null;
    let rows = [];
    const flush = () => {
      if (!rows.length) return;
      wrap.appendChild(
        helpers.section(
          group,
          helpers.simpleTable(
            [
              helpers.i18n.t('core-fetch-metadata.name', {}, 'Name'),
              helpers.i18n.t('core-fetch-metadata.value', {}, 'Value'),
              helpers.i18n.t(
                'core-fetch-metadata.what_it_does',
                {},
                'What it does',
              ),
            ],
            rows,
            ['name', '', 'muted'],
          ),
        ),
      );
      rows = [];
    };

    for (const tag of meta.tags) {
      if (tag.group !== group) {
        flush();
        group = tag.group;
      }
      rows.push([tag.key, helpers.linkifyText(tag.value), tag.note || '']);
    }
    flush();
    return wrap;
  },
};
