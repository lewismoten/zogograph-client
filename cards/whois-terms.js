window.ZogographWhoisTermsCard = {
  manifest: Object.freeze({
    id: 'core-whois-terms',
    locales: ['core-whois-terms.'],
    helpers: ['note', 'section', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-whois-terms.title': 'Before looking a name up',
        'core-whois-terms.accept': 'Understood — look it up',
      }),
      es: Object.freeze({
        'core-whois-terms.title': 'Antes de buscar un nombre',
        'core-whois-terms.accept': 'Entendido — buscarlo',
      }),
      hi: Object.freeze({
        'core-whois-terms.title': 'नाम देखने से पहले',
        'core-whois-terms.accept': 'समझ गया — इसे देखें',
      }),
      ar: Object.freeze({
        'core-whois-terms.title': 'قبل البحث عن اسم',
        'core-whois-terms.accept': 'فهمت — ابحث عنه',
      }),
      zh: Object.freeze({
        'core-whois-terms.title': '查询名称之前',
        'core-whois-terms.accept': '已了解 — 立即查询',
      }),
    }),
  }),
  render(data, helpers) {
    const wrap = document.createDocumentFragment();
    wrap.appendChild(helpers.note(data.terms.summary));
    const list = document.createElement('ul');
    list.className = 'evidence-list';
    for (const point of data.terms.points) {
      const item = document.createElement('li');
      item.textContent = point;
      list.appendChild(item);
    }
    wrap.appendChild(list);
    const actions = document.createElement('div');
    actions.className = 'inspect-actions';
    const agree = document.createElement('button');
    agree.textContent = helpers.i18n.t(
      'core-whois-terms.accept',
      {},
      'Understood — look it up',
    );
    agree.addEventListener('click', data.onAccept);
    actions.appendChild(agree);
    wrap.appendChild(actions);
    return helpers.section(
      helpers.i18n.t('core-whois-terms.title', {}, 'Before looking a name up'),
      wrap,
    );
  },
};
