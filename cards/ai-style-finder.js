/* Core card package: searchable, retained selection of AI image styles. */
window.ZogographAiStyleFinderCard = {
  manifest: Object.freeze({
    id: 'core-ai-style-finder',
    locales: ['core-ai-style-finder.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-style-finder.placeholder':
          'type to narrow — cyberpunk, watercolour, pixel…',
        'core-ai-style-finder.ticked': '{{count, number}} styles ticked · ',
        'core-ai-style-finder.choose_from': '{{count, number}} to choose from',
        'core-ai-style-finder.matches':
          '{{shown, number}} of {{total, number}} match',
      }),
      es: Object.freeze({
        'core-ai-style-finder.placeholder':
          'escriba para filtrar — ciberpunk, acuarela, píxel…',
        'core-ai-style-finder.ticked': '{{count, number}} estilos marcados · ',
        'core-ai-style-finder.choose_from': '{{count, number}} disponibles',
        'core-ai-style-finder.matches':
          '{{shown, number}} de {{total, number}} coinciden',
      }),
      hi: Object.freeze({
        'core-ai-style-finder.placeholder':
          'छाँटने के लिए लिखें — साइबरपंक, वॉटरकलर, पिक्सेल…',
        'core-ai-style-finder.ticked': '{{count, number}} शैलियाँ चुनी गईं · ',
        'core-ai-style-finder.choose_from': '{{count, number}} में से चुनें',
        'core-ai-style-finder.matches':
          '{{total, number}} में से {{shown, number}} मेल',
      }),
      ar: Object.freeze({
        'core-ai-style-finder.placeholder':
          'اكتب للتضييق — سايبربانك، ألوان مائية، بكسل…',
        'core-ai-style-finder.ticked': '{{count, number}} أنماط محددة · ',
        'core-ai-style-finder.choose_from': '{{count, number}} للاختيار منها',
        'core-ai-style-finder.matches':
          '{{shown, number}} من {{total, number}} متطابقة',
      }),
      zh: Object.freeze({
        'core-ai-style-finder.placeholder':
          '输入以筛选——赛博朋克、水彩、像素……',
        'core-ai-style-finder.ticked': '已勾选 {{count, number}} 种风格 · ',
        'core-ai-style-finder.choose_from': '可选 {{count, number}} 种',
        'core-ai-style-finder.matches':
          '{{total, number}} 种中匹配 {{shown, number}} 种',
      }),
    }),
  }),
  render(input, helpers) {
    const { pictures } = input;
    const t = (key, vars, fallback) => helpers.i18n.t(key, vars, fallback);
    const wrap = document.createElement('div');
    wrap.className = 'style-finder';
    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'wide-field';
    search.spellcheck = false;
    search.placeholder = t(
      'core-ai-style-finder.placeholder',
      {},
      'type to narrow — cyberpunk, watercolour, pixel…',
    );
    const said = document.createElement('div');
    said.className = 'muted small';
    const list = document.createElement('div');
    list.className = 'style-list';
    wrap.append(search, said, list);
    const entries = Object.entries(pictures.all_styles ?? {});
    entries.sort(([leftKey, leftName], [rightKey, rightName]) => {
      const leftOn = pictures.styles.includes(leftKey),
        rightOn = pictures.styles.includes(rightKey);
      if (leftOn !== rightOn) return leftOn ? -1 : 1;
      return String(leftName).localeCompare(String(rightName));
    });
    const rows = entries.map(([key, name]) => {
      const line = document.createElement('label');
      line.className = 'scan-toggle style-row';
      const box = document.createElement('input');
      box.type = 'checkbox';
      box.className = 'picture-style';
      box.dataset.style = key;
      box.checked = pictures.styles.includes(key);
      const label = document.createElement('span');
      label.textContent = ` ${name}`;
      const id = document.createElement('span');
      id.className = 'muted small';
      id.textContent = ` ${key}`;
      line.append(box, label, id);
      list.appendChild(line);
      return { line, box, haystack: `${name} ${key}`.toLowerCase() };
    });
    const sync = () => {
      const wanted = search.value.trim().toLowerCase();
      let shown = 0;
      for (const row of rows) {
        const keep =
          row.box.checked || wanted === '' || row.haystack.includes(wanted);
        row.line.hidden = !keep;
        if (keep) shown++;
      }
      const chosen = rows.filter((row) => row.box.checked).length;
      said.textContent =
        t(
          'core-ai-style-finder.ticked',
          { count: chosen },
          '{{count, number}} styles ticked · ',
        ) +
        (wanted === ''
          ? t(
              'core-ai-style-finder.choose_from',
              { count: rows.length },
              '{{count, number}} to choose from',
            )
          : t(
              'core-ai-style-finder.matches',
              { shown, total: rows.length },
              '{{shown, number}} of {{total, number}} match',
            ));
    };
    search.addEventListener('input', sync);
    list.addEventListener('change', sync);
    sync();
    return wrap;
  },
};
