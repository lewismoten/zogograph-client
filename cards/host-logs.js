/* Core card package: the shell supplies formatting and the scoped log viewer. */
window.ZogographHostLogsCard = {
  manifest: Object.freeze({
    id: 'core-host-logs',
    locales: ['core-host-logs.'],
    helpers: [
      'target',
      'renderTable',
      'humanBytes',
      'humanTime',
      'view',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-logs.title': "The server's own logs",
        'core-host-logs.log': 'Log',
        'core-host-logs.size': 'Size',
        'core-host-logs.last_written': 'Last written',
        'core-host-logs.state': 'State',
        'core-host-logs.not_on_machine': 'not on this machine',
        'core-host-logs.readable': 'readable',
        'core-host-logs.refused': 'refused by the host',
        'core-host-logs.refused_help':
          'The file is there and this account may not open it. That needs a group membership or an ACL granted by root — nothing in here can change it.',
        'core-host-logs.view': 'View',
      }),
      es: Object.freeze({
        'core-host-logs.title': 'Los registros del propio servidor',
        'core-host-logs.log': 'Registro',
        'core-host-logs.size': 'Tamaño',
        'core-host-logs.last_written': 'Última escritura',
        'core-host-logs.state': 'Estado',
        'core-host-logs.not_on_machine': 'no está en esta máquina',
        'core-host-logs.readable': 'legible',
        'core-host-logs.refused': 'el host denegó el acceso',
        'core-host-logs.refused_help':
          'El archivo existe, pero esta cuenta no puede abrirlo. Necesita pertenecer a un grupo o una ACL concedida por root; nada aquí puede cambiarlo.',
        'core-host-logs.view': 'Ver',
      }),
      hi: Object.freeze({
        'core-host-logs.title': 'सर्वर के अपने लॉग',
        'core-host-logs.log': 'लॉग',
        'core-host-logs.size': 'आकार',
        'core-host-logs.last_written': 'अंतिम लेखन',
        'core-host-logs.state': 'स्थिति',
        'core-host-logs.not_on_machine': 'इस मशीन पर नहीं है',
        'core-host-logs.readable': 'पढ़ने योग्य',
        'core-host-logs.refused': 'होस्ट ने पहुँच अस्वीकार की',
        'core-host-logs.refused_help':
          'फ़ाइल मौजूद है, लेकिन यह खाता इसे नहीं खोल सकता। इसके लिए root द्वारा दी गई समूह सदस्यता या ACL चाहिए — यहाँ कुछ भी इसे बदल नहीं सकता।',
        'core-host-logs.view': 'देखें',
      }),
      ar: Object.freeze({
        'core-host-logs.title': 'سجلات الخادم نفسه',
        'core-host-logs.log': 'السجل',
        'core-host-logs.size': 'الحجم',
        'core-host-logs.last_written': 'آخر كتابة',
        'core-host-logs.state': 'الحالة',
        'core-host-logs.not_on_machine': 'ليس على هذه الآلة',
        'core-host-logs.readable': 'يمكن قراءته',
        'core-host-logs.refused': 'رفض المضيف الوصول',
        'core-host-logs.refused_help':
          'الملف موجود، لكن قد لا يتمكن هذا الحساب من فتحه. يتطلب ذلك عضوية مجموعة أو ACL يمنحها root — ولا يمكن لأي شيء هنا تغيير ذلك.',
        'core-host-logs.view': 'عرض',
      }),
      zh: Object.freeze({
        'core-host-logs.title': '服务器自身的日志',
        'core-host-logs.log': '日志',
        'core-host-logs.size': '大小',
        'core-host-logs.last_written': '最后写入',
        'core-host-logs.state': '状态',
        'core-host-logs.not_on_machine': '不在此机器上',
        'core-host-logs.readable': '可读取',
        'core-host-logs.refused': '主机拒绝访问',
        'core-host-logs.refused_help':
          '文件存在，但此账户可能无法打开它。需要由 root 授予组成员资格或 ACL；这里无法更改此设置。',
        'core-host-logs.view': '查看',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-host-logs.${key}`,
      {},
      this.manifest.catalogs.en[`core-host-logs.${key}`],
    );
  },

  render(rows, helpers) {
    const { target, renderTable, humanBytes, humanTime, view, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    if (!target) return;
    renderTable(target.id, rows, [
      {
        label: t('core-host-logs.log', {}, 'Log'),
        className: 'name',
        render: (row) => row.path,
      },
      {
        label: t('core-host-logs.size', {}, 'Size'),
        className: 'size',
        render: (row) => (row.exists ? humanBytes(row.bytes) : '—'),
      },
      {
        label: t('core-host-logs.last_written', {}, 'Last written'),
        render: (row) => (row.modified_at ? humanTime(row.modified_at) : '—'),
      },
      {
        label: t('core-host-logs.state', {}, 'State'),
        render: (row) => {
          const span = document.createElement('span');
          if (!row.exists) {
            span.className = 'muted';
            span.textContent = t(
              'core-host-logs.not_on_machine',
              {},
              'not on this machine',
            );
          } else if (row.readable) {
            span.className = 'ok';
            span.textContent = t('core-host-logs.readable', {}, 'readable');
          } else {
            span.className = 'muted';
            span.textContent = t(
              'core-host-logs.refused',
              {},
              'refused by the host',
            );
            span.title = t(
              'core-host-logs.refused_help',
              {},
              'The file is there and this account may not open it. That needs a group membership or an ACL granted by root — nothing in here can change it.',
            );
          }
          return span;
        },
      },
      {
        label: '',
        render: (row) => {
          if (!row.readable) return '';
          const button = document.createElement('button');
          button.className = 'link';
          button.textContent = t('core-host-logs.view', {}, 'View');
          button.addEventListener('click', () => view(row.path));
          return button;
        },
      },
    ]);
  },
};
