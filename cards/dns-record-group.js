window.ZogographDnsRecordGroupCard = {
  manifest: Object.freeze({
    id: 'core-dns-record-group',
    locales: ['core-dns-record-group.'],
    helpers: [
      'target',
      'renderTable',
      'recordValueCell',
      'humanDuration',
      'humanTime',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-dns-record-group.ttl': 'TTL',
        'core-dns-record-group.short_ttl_help':
          'A short TTL means resolvers re-ask constantly. Often left over from a migration.',
        'core-dns-record-group.gone_since': 'Gone since {{date}}',
        'core-dns-record-group.seen_since': 'Seen since {{date}}',
      }),
      es: Object.freeze({
        'core-dns-record-group.ttl': 'TTL',
        'core-dns-record-group.short_ttl_help':
          'Un TTL corto hace que los resolutores vuelvan a consultar constantemente. Suele quedar de una migración.',
        'core-dns-record-group.gone_since': 'Eliminado desde {{date}}',
        'core-dns-record-group.seen_since': 'Visto desde {{date}}',
      }),
      hi: Object.freeze({
        'core-dns-record-group.ttl': 'TTL',
        'core-dns-record-group.short_ttl_help':
          'छोटे TTL का अर्थ है कि रिज़ॉल्वर लगातार फिर से पूछते हैं। यह अक्सर माइग्रेशन के बाद बचा रह जाता है।',
        'core-dns-record-group.gone_since': '{{date}} से अनुपस्थित',
        'core-dns-record-group.seen_since': '{{date}} से देखा गया',
      }),
      ar: Object.freeze({
        'core-dns-record-group.ttl': 'TTL',
        'core-dns-record-group.short_ttl_help':
          'يعني TTL القصير أن المحلِّلات تعيد السؤال باستمرار. وغالبًا ما يكون متبقيًا من عملية ترحيل.',
        'core-dns-record-group.gone_since': 'غائب منذ {{date}}',
        'core-dns-record-group.seen_since': 'شوهد منذ {{date}}',
      }),
      zh: Object.freeze({
        'core-dns-record-group.ttl': 'TTL',
        'core-dns-record-group.short_ttl_help':
          'TTL 过短意味着解析器会不断重新查询。这通常是迁移后遗留下来的。',
        'core-dns-record-group.gone_since': '自 {{date}} 起已消失',
        'core-dns-record-group.seen_since': '自 {{date}} 起已发现',
      }),
    }),
  }),
  render(data, helpers) {
    if (!helpers.target) return;
    helpers.renderTable(
      helpers.target.id,
      data.rows,
      [
        {
          label: helpers.i18n.t('table.name', {}, 'Name'),
          className: 'name',
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = row.name;
            if (row.gone) span.className = 'muted struck';
            return span;
          },
        },
        {
          label: helpers.i18n.t('table.type', {}, 'Type'),
          render: (row) => row.type,
        },
        {
          label: helpers.i18n.t('table.value', {}, 'Value'),
          className: 'cmd',
          render: helpers.recordValueCell,
        },
        {
          label: helpers.i18n.t('core-dns-record-group.ttl', {}, 'TTL'),
          className: 'size',
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = helpers.humanDuration(Number(row.ttl));
            if (Number(row.ttl) > 0 && Number(row.ttl) <= 300) {
              span.className = 'band-warn';
              span.title = helpers.i18n.t(
                'core-dns-record-group.short_ttl_help',
                {},
                'A short TTL means resolvers re-ask constantly. Often left over from a migration.',
              );
            }
            return span;
          },
        },
        {
          label: '',
          render: (row) => {
            const span = document.createElement('span');
            span.className = 'muted small';
            span.textContent = helpers.i18n.t(
              row.gone
                ? 'core-dns-record-group.gone_since'
                : 'core-dns-record-group.seen_since',
              {
                date: helpers
                  .humanTime(row.gone ? row.gone_at : row.first_seen)
                  .split(',')[0],
              },
              row.gone ? 'Gone since {{date}}' : 'Seen since {{date}}',
            );
            return span;
          },
        },
      ],
      null,
      (row) => (row.gone ? 'row-bad' : ''),
      (row) => `${row.name}|${row.type}|${row.value}`,
    );
  },
};
