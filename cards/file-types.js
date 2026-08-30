/* Core card package: measured file types and the scan they came from. */
window.ZogographFileTypesCard = {
  manifest: Object.freeze({
    id: 'core-file-types',
    locales: ['core-file-types.'],
    helpers: [
      'cards',
      'pieSection',
      'typeWhen',
      'pieNote',
      'typeColor',
      'humanBytes',
      'humanCount',
      'humanTime',
      'count',
      'renderTypePie',
      'renderOtherExtensions',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-file-types.not_measured':
          'The last scan ran before this was measured. The next one will fill it in.',
        'core-file-types.no_scan': 'No completed scan in this range.',
        'core-file-types.measured': '{{bytes}} in {{files}}, measured {{when}}',
        'core-file-types.total': '{{bytes}} in {{files}}',
      }),
      es: Object.freeze({
        'core-file-types.not_measured':
          'El último análisis se ejecutó antes de medir esto. El siguiente lo completará.',
        'core-file-types.no_scan':
          'No hay análisis completado en este intervalo.',
        'core-file-types.measured': '{{bytes}} en {{files}}, medido {{when}}',
        'core-file-types.total': '{{bytes}} en {{files}}',
      }),
      hi: Object.freeze({
        'core-file-types.not_measured':
          'पिछला स्कैन इसे मापने से पहले चला था। अगला इसे भर देगा।',
        'core-file-types.no_scan': 'इस दायरे में कोई पूरा स्कैन नहीं है।',
        'core-file-types.measured':
          '{{files}} में {{bytes}}, मापा गया {{when}}',
        'core-file-types.total': '{{files}} में {{bytes}}',
      }),
      ar: Object.freeze({
        'core-file-types.not_measured':
          'جرى آخر فحص قبل قياس هذا. سيكمله الفحص التالي.',
        'core-file-types.no_scan': 'لا يوجد فحص مكتمل في هذا النطاق.',
        'core-file-types.measured': '{{bytes}} في {{files}}، قيس {{when}}',
        'core-file-types.total': '{{bytes}} في {{files}}',
      }),
      zh: Object.freeze({
        'core-file-types.not_measured':
          '上次扫描在测量此项之前运行。下一次会补齐。',
        'core-file-types.no_scan': '此范围内没有完成的扫描。',
        'core-file-types.measured': '{{files}} 中有 {{bytes}}，测量于 {{when}}',
        'core-file-types.total': '{{files}} 中有 {{bytes}}',
      }),
    }),
  }),
  render(scans, h) {
    const {
        cards,
        pieSection,
        typeWhen,
        pieNote,
        typeColor,
        humanBytes,
        humanTime,
        count,
        renderTypePie,
        renderOtherExtensions,
        i18n,
      } = h,
      t = (k, v = {}, f = k) => i18n.t(`core-file-types.${k}`, v, f),
      types = scans.file_types ?? [];
    if (!types.length) {
      cards.replaceChildren(
        Object.assign(document.createElement('p'), {
          className: 'muted',
          textContent: (scans.rows ?? []).length
            ? t(
                'not_measured',
                {},
                'The last scan ran before this was measured. The next one will fill it in.',
              )
            : t('no_scan', {}, 'No completed scan in this range.'),
        }),
      );
      pieSection.hidden = true;
      renderOtherExtensions([]);
      typeWhen.textContent = '';
      pieNote.textContent = '';
      return cards;
    }
    pieSection.hidden = false;
    const total = types.reduce((s, x) => s + Number(x.bytes || 0), 0),
      files = types.reduce((s, x) => s + Number(x.files || 0), 0),
      frag = document.createDocumentFragment();
    for (const type of types) {
      const bytes = Number(type.bytes || 0),
        card = document.createElement('div'),
        label = document.createElement('div'),
        value = document.createElement('div'),
        sub = document.createElement('div');
      card.className = 'card';
      label.className = 'label';
      label.style.setProperty('--dot', typeColor(type.type));
      label.textContent = type.type;
      value.className = 'value';
      value.textContent = humanBytes(bytes);
      sub.className = 'sub muted';
      sub.textContent = `${total ? ((bytes / total) * 100).toFixed(1) : '0'}% · ${count(Number(type.files || 0), 'file')}`;
      card.append(label, value, sub);
      frag.appendChild(card);
    }
    cards.replaceChildren(frag);
    renderTypePie(scans);
    renderOtherExtensions(scans.other_extensions ?? []);
    const scan = (scans.rows ?? []).find(
      (x) => x.id === scans.file_types_scan_id,
    );
    typeWhen.textContent = scan
      ? t(
          'measured',
          {
            bytes: humanBytes(total),
            files: count(files, 'file'),
            when: humanTime(scan.finished_at || scan.started_at),
          },
          '{{bytes}} in {{files}}, measured {{when}}',
        )
      : t(
          'total',
          { bytes: humanBytes(total), files: count(files, 'file') },
          '{{bytes}} in {{files}}',
        );
    return cards;
  },
};
