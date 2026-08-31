/* Core card package: the shell supplies table, note, and ownership helpers. */
window.ZogographNetworkPortsCard = {
  manifest: Object.freeze({
    id: 'core-network-ports',
    locales: ['core-network-ports.'],
    helpers: ['target', 'note', 'renderTable', 'labelled', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-network-ports.title': 'Open ports',
        'core-network-ports.public_empty':
          'Nothing is listening on an address reachable from outside.',
        'core-network-ports.empty': 'No listening sockets recorded.',
        'core-network-ports.port': 'Port',
        'core-network-ports.usually': 'Usually',
        'core-network-ports.held_by': 'Held by',
        'core-network-ports.not_attributed': 'not attributed',
        'core-network-ports.reachable': 'Reachable',
        'core-network-ports.from_anywhere': 'from anywhere',
        'core-network-ports.this_machine_only': 'this machine only',
        'core-network-ports.risky_tip':
          'This normally has no authentication by default. Reachable from off the machine, that is a serious exposure rather than a configuration choice.',
        'core-network-ports.addresses': 'Addresses',
        'core-network-ports.stack': 'Stack',
      }),
      es: Object.freeze({
        'core-network-ports.title': 'Puertos abiertos',
        'core-network-ports.public_empty':
          'No hay nada escuchando en una dirección accesible desde fuera.',
        'core-network-ports.empty': 'No se registraron sockets en escucha.',
        'core-network-ports.port': 'Puerto',
        'core-network-ports.usually': 'Normalmente',
        'core-network-ports.held_by': 'Usado por',
        'core-network-ports.not_attributed': 'sin atribuir',
        'core-network-ports.reachable': 'Accesible',
        'core-network-ports.from_anywhere': 'desde cualquier lugar',
        'core-network-ports.this_machine_only': 'solo esta máquina',
        'core-network-ports.risky_tip':
          'Normalmente no tiene autenticación de forma predeterminada. Que sea accesible fuera de la máquina es una exposición grave, no una opción de configuración.',
        'core-network-ports.addresses': 'Direcciones',
        'core-network-ports.stack': 'Pila',
      }),
      hi: Object.freeze({
        'core-network-ports.title': 'खुले पोर्ट',
        'core-network-ports.public_empty':
          'बाहर से पहुंचने योग्य पते पर कुछ भी सुन नहीं रहा है।',
        'core-network-ports.empty': 'कोई सुनने वाला सॉकेट दर्ज नहीं है।',
        'core-network-ports.port': 'पोर्ट',
        'core-network-ports.usually': 'आमतौर पर',
        'core-network-ports.held_by': 'इसके पास',
        'core-network-ports.not_attributed': 'निर्धारित नहीं',
        'core-network-ports.reachable': 'पहुंच योग्य',
        'core-network-ports.from_anywhere': 'कहीं से भी',
        'core-network-ports.this_machine_only': 'केवल यह मशीन',
        'core-network-ports.risky_tip':
          'इसमें आम तौर पर डिफ़ॉल्ट रूप से प्रमाणीकरण नहीं होता। मशीन के बाहर से पहुंच इसे कॉन्फ़िगरेशन विकल्प नहीं, बल्कि गंभीर जोखिम बनाती है।',
        'core-network-ports.addresses': 'पते',
        'core-network-ports.stack': 'स्टैक',
      }),
      ar: Object.freeze({
        'core-network-ports.title': 'المنافذ المفتوحة',
        'core-network-ports.public_empty':
          'لا توجد خدمة تستمع على عنوان يمكن الوصول إليه من الخارج.',
        'core-network-ports.empty': 'لم تُسجل مقابس في وضع الاستماع.',
        'core-network-ports.port': 'المنفذ',
        'core-network-ports.usually': 'عادةً',
        'core-network-ports.held_by': 'تستخدمه',
        'core-network-ports.not_attributed': 'غير منسوب',
        'core-network-ports.reachable': 'يمكن الوصول إليه',
        'core-network-ports.from_anywhere': 'من أي مكان',
        'core-network-ports.this_machine_only': 'هذه الآلة فقط',
        'core-network-ports.risky_tip':
          'لا يملك هذا عادة مصادقة افتراضيًا. إمكانية الوصول إليه من خارج الآلة تعرّض خطير وليست خيار إعداد.',
        'core-network-ports.addresses': 'العناوين',
        'core-network-ports.stack': 'المكدس',
      }),
      zh: Object.freeze({
        'core-network-ports.title': '开放端口',
        'core-network-ports.public_empty':
          '没有服务正在可从外部访问的地址上监听。',
        'core-network-ports.empty': '未记录监听套接字。',
        'core-network-ports.port': '端口',
        'core-network-ports.usually': '通常',
        'core-network-ports.held_by': '占用者',
        'core-network-ports.not_attributed': '未归属',
        'core-network-ports.reachable': '可访问',
        'core-network-ports.from_anywhere': '任意位置',
        'core-network-ports.this_machine_only': '仅此机器',
        'core-network-ports.risky_tip':
          '它通常默认没有身份验证。可从机器外部访问是严重暴露，而非配置选择。',
        'core-network-ports.addresses': '地址',
        'core-network-ports.stack': '协议栈',
      }),
    }),
  }),

  text(key, helpers) {
    return helpers.i18n.t(
      `core-network-ports.${key}`,
      {},
      this.manifest.catalogs.en[`core-network-ports.${key}`],
    );
  },

  render(data, helpers) {
    const { target, note, renderTable, labelled, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    const rows = (data?.ports || []).filter(
      (port) => !data.publicOnly || port.public,
    );
    if (!rows.length) {
      target.replaceChildren(
        note(
          data.publicOnly
            ? t(
                'core-network-ports.public_empty',
                {},
                'Nothing is listening on an address reachable from outside.',
              )
            : t(
                'core-network-ports.empty',
                {},
                'No listening sockets recorded.',
              ),
        ),
      );
      return;
    }
    renderTable(
      target.id,
      rows,
      [
        {
          label: t('core-network-ports.port', {}, 'Port'),
          className: 'size',
          render: (row) =>
            labelled(`${row.port}/${row.protocol}`, row.own_label),
        },
        {
          label: t('core-network-ports.usually', {}, 'Usually'),
          className: 'name',
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = row.service.name;
            if (!row.service.known) span.className = 'muted';
            if (row.service.note) span.title = row.service.note;
            return span;
          },
        },
        {
          label: t('core-network-ports.held_by', {}, 'Held by'),
          render: (row) =>
            row.process ||
            t('core-network-ports.not_attributed', {}, 'not attributed'),
        },
        {
          label: t('core-network-ports.reachable', {}, 'Reachable'),
          render: (row) => {
            const span = document.createElement('span');
            span.textContent = row.public
              ? t('core-network-ports.from_anywhere', {}, 'from anywhere')
              : t(
                  'core-network-ports.this_machine_only',
                  {},
                  'this machine only',
                );
            const risky = [3306, 5432, 6379, 11211, 27017, 9200];
            if (row.public && risky.includes(Number(row.port))) {
              span.className = 'band-crit';
              span.title = t(
                'core-network-ports.risky_tip',
                {},
                'This normally has no authentication by default. Reachable from off the machine, that is a serious exposure rather than a configuration choice.',
              );
            } else if (row.public) {
              span.className = 'muted';
            }
            return span;
          },
        },
        {
          label: t('core-network-ports.addresses', {}, 'Addresses'),
          className: 'cmd',
          render: (row) => (row.addresses || []).join(', '),
        },
        {
          label: t('core-network-ports.stack', {}, 'Stack'),
          render: (row) => (row.families || []).join(' + '),
        },
      ],
      null,
      null,
      (row) => `${row.protocol}:${row.port}`,
    );
  },
};
