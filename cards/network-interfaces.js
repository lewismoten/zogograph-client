/* Core card package: the shell supplies table and unit-formatting helpers. */
window.ZogographNetworkInterfacesCard = {
  manifest: Object.freeze({
    id: 'core-network-interfaces',
    locales: ['core-network-interfaces.'],
    helpers: [
      'target',
      'note',
      'renderTable',
      'humanBytes',
      'humanCount',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-network-interfaces.empty': 'No interfaces recorded.',
        'core-network-interfaces.interface': 'Interface',
        'core-network-interfaces.loopback':
          ' · loopback, never leaves the machine',
        'core-network-interfaces.state': 'State',
        'core-network-interfaces.unknown': 'unknown',
        'core-network-interfaces.up': 'up',
        'core-network-interfaces.down': 'down',
        'core-network-interfaces.in': 'In',
        'core-network-interfaces.out': 'Out',
        'core-network-interfaces.packets_per_second': 'Packets/s',
        'core-network-interfaces.packets_in_out':
          '{{incoming, number}} in, {{outgoing, number}} out',
        'core-network-interfaces.link': 'Link',
        'core-network-interfaces.not_reported': 'not reported',
        'core-network-interfaces.errors_drops': 'Errors / drops',
        'core-network-interfaces.since_boot': 'Since boot',
        'core-network-interfaces.bytes_in_out':
          '{{incoming}} in, {{outgoing}} out',
      }),
      es: Object.freeze({
        'core-network-interfaces.empty': 'No se registraron interfaces.',
        'core-network-interfaces.interface': 'Interfaz',
        'core-network-interfaces.loopback':
          ' · bucle local, nunca sale de la máquina',
        'core-network-interfaces.state': 'Estado',
        'core-network-interfaces.unknown': 'desconocido',
        'core-network-interfaces.up': 'activo',
        'core-network-interfaces.down': 'inactivo',
        'core-network-interfaces.in': 'Entrada',
        'core-network-interfaces.out': 'Salida',
        'core-network-interfaces.packets_per_second': 'Paquetes/s',
        'core-network-interfaces.packets_in_out':
          '{{incoming, number}} de entrada, {{outgoing, number}} de salida',
        'core-network-interfaces.link': 'Enlace',
        'core-network-interfaces.not_reported': 'sin informar',
        'core-network-interfaces.errors_drops': 'Errores / descartes',
        'core-network-interfaces.since_boot': 'Desde el arranque',
        'core-network-interfaces.bytes_in_out':
          '{{incoming}} de entrada, {{outgoing}} de salida',
      }),
      hi: Object.freeze({
        'core-network-interfaces.empty': 'कोई इंटरफ़ेस दर्ज नहीं है।',
        'core-network-interfaces.interface': 'इंटरफ़ेस',
        'core-network-interfaces.loopback': ' · लूपबैक, मशीन से बाहर नहीं जाता',
        'core-network-interfaces.state': 'स्थिति',
        'core-network-interfaces.unknown': 'अज्ञात',
        'core-network-interfaces.up': 'चालू',
        'core-network-interfaces.down': 'बंद',
        'core-network-interfaces.in': 'अंदर',
        'core-network-interfaces.out': 'बाहर',
        'core-network-interfaces.packets_per_second': 'पैकेट/से.',
        'core-network-interfaces.packets_in_out':
          '{{incoming, number}} अंदर, {{outgoing, number}} बाहर',
        'core-network-interfaces.link': 'लिंक',
        'core-network-interfaces.not_reported': 'रिपोर्ट नहीं किया गया',
        'core-network-interfaces.errors_drops': 'त्रुटियाँ / ड्रॉप',
        'core-network-interfaces.since_boot': 'बूट से',
        'core-network-interfaces.bytes_in_out':
          '{{incoming}} अंदर, {{outgoing}} बाहर',
      }),
      ar: Object.freeze({
        'core-network-interfaces.empty': 'لم تُسجل واجهات.',
        'core-network-interfaces.interface': 'الواجهة',
        'core-network-interfaces.loopback': ' · حلقة محلية، لا يغادر الجهاز',
        'core-network-interfaces.state': 'الحالة',
        'core-network-interfaces.unknown': 'غير معروف',
        'core-network-interfaces.up': 'يعمل',
        'core-network-interfaces.down': 'متوقف',
        'core-network-interfaces.in': 'وارد',
        'core-network-interfaces.out': 'صادر',
        'core-network-interfaces.packets_per_second': 'حزم/ث',
        'core-network-interfaces.packets_in_out':
          '{{incoming, number}} وارد، {{outgoing, number}} صادر',
        'core-network-interfaces.link': 'الوصلة',
        'core-network-interfaces.not_reported': 'غير مُبلّغ عنه',
        'core-network-interfaces.errors_drops': 'أخطاء / إسقاطات',
        'core-network-interfaces.since_boot': 'منذ الإقلاع',
        'core-network-interfaces.bytes_in_out':
          '{{incoming}} وارد، {{outgoing}} صادر',
      }),
      zh: Object.freeze({
        'core-network-interfaces.empty': '未记录接口。',
        'core-network-interfaces.interface': '接口',
        'core-network-interfaces.loopback': ' · 回环，不会离开此机器',
        'core-network-interfaces.state': '状态',
        'core-network-interfaces.unknown': '未知',
        'core-network-interfaces.up': '正常',
        'core-network-interfaces.down': '关闭',
        'core-network-interfaces.in': '入站',
        'core-network-interfaces.out': '出站',
        'core-network-interfaces.packets_per_second': '数据包/秒',
        'core-network-interfaces.packets_in_out':
          '{{incoming, number}} 入站，{{outgoing, number}} 出站',
        'core-network-interfaces.link': '链路',
        'core-network-interfaces.not_reported': '未报告',
        'core-network-interfaces.errors_drops': '错误 / 丢弃',
        'core-network-interfaces.since_boot': '自启动以来',
        'core-network-interfaces.bytes_in_out':
          '{{incoming}} 入站，{{outgoing}} 出站',
      }),
    }),
  }),

  render(rows, helpers) {
    const { target, note, renderTable, humanBytes, humanCount, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    if (!rows.length) {
      target.replaceChildren(
        note(t('core-network-interfaces.empty', {}, 'No interfaces recorded.')),
      );
      return;
    }
    renderTable(
      target.id,
      rows,
      [
        {
          label: t('core-network-interfaces.interface', {}, 'Interface'),
          className: 'name',
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = row.name;
            if (row.loopback) {
              const tag = document.createElement('span');
              tag.className = 'muted small';
              tag.textContent = t(
                'core-network-interfaces.loopback',
                {},
                ' · loopback, never leaves the machine',
              );
              span.appendChild(tag);
            }
            return span;
          },
        },
        {
          label: t('core-network-interfaces.state', {}, 'State'),
          render: (row) => {
            const span = document.createElement('span');
            span.textContent =
              row.up === null
                ? t('core-network-interfaces.unknown', {}, 'unknown')
                : row.up
                  ? t('core-network-interfaces.up', {}, 'up')
                  : t('core-network-interfaces.down', {}, 'down');
            if (row.up === false) span.className = 'band-crit';
            return span;
          },
        },
        {
          label: t('core-network-interfaces.in', {}, 'In'),
          className: 'size',
          render: (row) =>
            row.rx_rate == null ? '—' : `${humanBytes(row.rx_rate)}/s`,
        },
        {
          label: t('core-network-interfaces.out', {}, 'Out'),
          className: 'size',
          render: (row) =>
            row.tx_rate == null ? '—' : `${humanBytes(row.tx_rate)}/s`,
        },
        {
          label: t(
            'core-network-interfaces.packets_per_second',
            {},
            'Packets/s',
          ),
          className: 'size',
          render: (row) =>
            row.rx_packet_rate == null
              ? '—'
              : t(
                  'core-network-interfaces.packets_in_out',
                  {
                    incoming: Math.round(row.rx_packet_rate),
                    outgoing: Math.round(row.tx_packet_rate),
                  },
                  '{{incoming, number}} in, {{outgoing, number}} out',
                ),
        },
        {
          label: t('core-network-interfaces.link', {}, 'Link'),
          className: 'size',
          render: (row) =>
            row.speed_mbit
              ? `${row.speed_mbit} Mbit`
              : t('core-network-interfaces.not_reported', {}, 'not reported'),
        },
        {
          label: t(
            'core-network-interfaces.errors_drops',
            {},
            'Errors / drops',
          ),
          className: 'size',
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = `${humanCount(row.errors)} / ${humanCount(row.dropped)}`;
            if (row.errors > 0 || row.dropped > 0) span.className = 'grew';
            return span;
          },
        },
        {
          label: t('core-network-interfaces.since_boot', {}, 'Since boot'),
          className: 'size',
          render: (row) =>
            t(
              'core-network-interfaces.bytes_in_out',
              {
                incoming: humanBytes(row.rx_bytes),
                outgoing: humanBytes(row.tx_bytes),
              },
              '{{incoming}} in, {{outgoing}} out',
            ),
        },
      ],
      null,
      null,
      (row) => row.name,
    );
  },
};
