/* Core card package: saved address, port, and header aliases. */
window.ZogographLabelsTableCard = {
  manifest: Object.freeze({
    id: 'core-labels-table',
    locales: ['core-labels-table.'],
    helpers: ['target', 'renderTable', 'remove', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-labels-table.kind': 'Kind',
        'core-labels-table.kind_address': 'Address',
        'core-labels-table.kind_port': 'Port',
        'core-labels-table.kind_header': 'HTTP header',
        'core-labels-table.matches': 'Matches',
        'core-labels-table.shown_as': 'Shown as',
        'core-labels-table.note': 'Note',
        'core-labels-table.remove': 'Remove',
      }),
      es: Object.freeze({
        'core-labels-table.kind': 'Tipo',
        'core-labels-table.kind_address': 'Dirección',
        'core-labels-table.kind_port': 'Puerto',
        'core-labels-table.kind_header': 'Cabecera HTTP',
        'core-labels-table.matches': 'Coincide con',
        'core-labels-table.shown_as': 'Mostrado como',
        'core-labels-table.note': 'Nota',
        'core-labels-table.remove': 'Eliminar',
      }),
      hi: Object.freeze({
        'core-labels-table.kind': 'प्रकार',
        'core-labels-table.kind_address': 'पता',
        'core-labels-table.kind_port': 'पोर्ट',
        'core-labels-table.kind_header': 'HTTP हेडर',
        'core-labels-table.matches': 'मेल खाता है',
        'core-labels-table.shown_as': 'इस रूप में दिखाया गया',
        'core-labels-table.note': 'नोट',
        'core-labels-table.remove': 'हटाएँ',
      }),
      ar: Object.freeze({
        'core-labels-table.kind': 'النوع',
        'core-labels-table.kind_address': 'العنوان',
        'core-labels-table.kind_port': 'المنفذ',
        'core-labels-table.kind_header': 'ترويسة HTTP',
        'core-labels-table.matches': 'يطابق',
        'core-labels-table.shown_as': 'يُعرض كـ',
        'core-labels-table.note': 'ملاحظة',
        'core-labels-table.remove': 'إزالة',
      }),
      zh: Object.freeze({
        'core-labels-table.kind': '类型',
        'core-labels-table.kind_address': '地址',
        'core-labels-table.kind_port': '端口',
        'core-labels-table.kind_header': 'HTTP 标头',
        'core-labels-table.matches': '匹配',
        'core-labels-table.shown_as': '显示为',
        'core-labels-table.note': '备注',
        'core-labels-table.remove': '移除',
      }),
    }),
  }),

  render(data, helpers) {
    const { target, renderTable, remove, i18n } = helpers;
    if (!target || !data.rows.length) return;
    const t = (key) =>
      i18n.t(
        `core-labels-table.${key}`,
        {},
        this.manifest.catalogs.en[`core-labels-table.${key}`],
      );
    const holder = document.createElement('div');
    holder.id = 'labelsTable';
    target.replaceChildren(holder);
    renderTable(
      'labelsTable',
      data.rows,
      [
        {
          label: t('kind'),
          render: (row) =>
            ({
              address: t('kind_address'),
              port: t('kind_port'),
              header: t('kind_header'),
            })[row.kind] || row.kind,
        },
        { label: t('matches'), className: 'name', render: (row) => row.match },
        {
          label: t('shown_as'),
          className: 'name',
          render: (row) => row.label,
        },
        { label: t('note'), className: 'cmd', render: (row) => row.note || '' },
        {
          label: '',
          render: (row) => {
            const button = document.createElement('button');
            button.className = 'link danger';
            button.textContent = t('remove');
            button.addEventListener('click', () => remove(row));
            return button;
          },
        },
      ],
      null,
      null,
      (row) => String(row.id),
    );
  },
};
