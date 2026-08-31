/* Core card package: the shell supplies table, time, and label helpers. */
window.ZogographNetworkPeersCard = {
  manifest: Object.freeze({
    id: 'core-network-peers',
    locales: ['core-network-peers.'],
    helpers: [
      'target',
      'note',
      'renderTable',
      'humanCount',
      'humanTime',
      'labelled',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-network-peers.title': 'Who is connected',
        'core-network-peers.empty':
          'No established connections were recorded. Either the machine is idle, or ss could not be run by the collector.',
        'core-network-peers.summary':
          '{{connections}} connections across {{peers}}, recorded {{at}}.',
        'core-network-peers.address': 'Address',
        'core-network-peers.connections': 'Connections',
        'core-network-peers.share': 'Share',
        'core-network-peers.private_address':
          'a private address — another machine on this network',
      }),
      es: Object.freeze({
        'core-network-peers.title': 'Quién está conectado',
        'core-network-peers.empty':
          'No se registraron conexiones establecidas. La máquina está inactiva o el recopilador no pudo ejecutar ss.',
        'core-network-peers.summary':
          '{{connections}} conexiones entre {{peers}}, registradas {{at}}.',
        'core-network-peers.address': 'Dirección',
        'core-network-peers.connections': 'Conexiones',
        'core-network-peers.share': 'Proporción',
        'core-network-peers.private_address':
          'una dirección privada — otra máquina de esta red',
      }),
      hi: Object.freeze({
        'core-network-peers.title': 'कौन जुड़ा है',
        'core-network-peers.empty':
          'कोई स्थापित कनेक्शन दर्ज नहीं था। मशीन निष्क्रिय है या कलेक्टर ss नहीं चला सका।',
        'core-network-peers.summary':
          '{{peers}} में {{connections}} कनेक्शन, {{at}} दर्ज किए गए।',
        'core-network-peers.address': 'पता',
        'core-network-peers.connections': 'कनेक्शन',
        'core-network-peers.share': 'हिस्सा',
        'core-network-peers.private_address':
          'निजी पता — इस नेटवर्क की दूसरी मशीन',
      }),
      ar: Object.freeze({
        'core-network-peers.title': 'من المتصل',
        'core-network-peers.empty':
          'لم تُسجل اتصالات قائمة. إما أن الآلة خاملة أو لم يتمكن الجامع من تشغيل ss.',
        'core-network-peers.summary':
          '{{connections}} اتصالات عبر {{peers}}، سُجلت {{at}}.',
        'core-network-peers.address': 'العنوان',
        'core-network-peers.connections': 'الاتصالات',
        'core-network-peers.share': 'الحصة',
        'core-network-peers.private_address':
          'عنوان خاص — آلة أخرى على هذه الشبكة',
      }),
      zh: Object.freeze({
        'core-network-peers.title': '谁已连接',
        'core-network-peers.empty':
          '未记录已建立的连接。机器可能空闲，或采集器无法运行 ss。',
        'core-network-peers.summary':
          '{{peers}} 个对端共有 {{connections}} 个连接，记录于 {{at}}。',
        'core-network-peers.address': '地址',
        'core-network-peers.connections': '连接',
        'core-network-peers.share': '占比',
        'core-network-peers.private_address': '私有地址 — 此网络中的另一台机器',
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-network-peers.${key}`,
      {},
      this.manifest.catalogs.en[`core-network-peers.${key}`],
    );
  },

  render(data, helpers) {
    const { target, note, renderTable, humanCount, humanTime, labelled, i18n } =
      helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    const rows = data?.peers || [];
    if (!rows.length) {
      target.replaceChildren(
        note(
          data?.reason ||
            t(
              'core-network-peers.empty',
              {},
              'No established connections were recorded. Either the machine is idle, or ss could not be run by the collector.',
            ),
        ),
      );
      return;
    }

    const total = rows.reduce(
      (sum, row) => sum + Number(row.connections || 0),
      0,
    );
    const wrap = document.createDocumentFragment();
    wrap.appendChild(
      note(
        t(
          'core-network-peers.summary',
          {
            connections: humanCount(total),
            peers: data.peerCount,
            at: humanTime(data.taken_at),
          },
          '{{connections}} connections across {{peers}}, recorded {{at}}.',
        ),
      ),
    );
    target.replaceChildren(wrap);

    const holder = document.createElement('div');
    holder.id = 'peersTable';
    target.appendChild(holder);
    renderTable(
      holder.id,
      rows,
      [
        {
          label: t('core-network-peers.address', {}, 'Address'),
          className: 'name',
          render: (row) => labelled(row.address, row.own_label, row),
        },
        {
          label: t('core-network-peers.connections', {}, 'Connections'),
          className: 'size',
          render: (row) => humanCount(row.connections),
        },
        {
          label: t('core-network-peers.share', {}, 'Share'),
          className: 'size',
          render: (row) =>
            total ? `${((row.connections / total) * 100).toFixed(1)}%` : '—',
        },
        {
          label: '',
          render: (row) => {
            if (!row.private) return '';
            const span = document.createElement('span');
            span.className = 'muted small';
            span.textContent = t(
              'core-network-peers.private_address',
              {},
              'a private address — another machine on this network',
            );
            return span;
          },
        },
      ],
      null,
      null,
      (row) => row.address,
    );
  },
};
