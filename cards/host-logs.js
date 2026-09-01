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
        'core-host-logs.check_again': 'Check again',
        'core-host-logs.help':
          'The logs named in host_log_paths belong to root and live outside this account. On most shared servers they will be listed and refused, which is itself the answer. Where the host grants read access, they open in the same viewer as the account’s own logs.',
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
        'core-host-logs.check_again': 'Comprobar de nuevo',
        'core-host-logs.help':
          'Los registros indicados en host_log_paths pertenecen a root y se encuentran fuera de esta cuenta. En la mayoría de los servidores compartidos se mostrarán y se denegará el acceso; eso ya es una respuesta. Cuando el host concede acceso de lectura, se abren en el mismo visor que los registros de la cuenta.',
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
        'core-host-logs.check_again': 'फिर जाँचें',
        'core-host-logs.help':
          'host_log_paths में दिए गए लॉग root के हैं और इस खाते के बाहर रहते हैं। अधिकांश साझा सर्वरों पर वे सूचीबद्ध होंगे और उन तक पहुँच अस्वीकार होगी; यही अपने-आप में उत्तर है। जहाँ होस्ट पढ़ने की अनुमति देता है, वे खाते के अपने लॉग के समान व्यूअर में खुलते हैं।',
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
        'core-host-logs.check_again': 'تحقق مجددًا',
        'core-host-logs.help':
          'السجلات المسماة في host_log_paths تخص root وتوجد خارج هذا الحساب. في معظم الخوادم المشتركة ستُدرج ثم يُرفض الوصول إليها، وهذه إجابة بحد ذاتها. عندما يمنح المضيف إذن القراءة، تُفتح في العارض نفسه المستخدم لسجلات الحساب.',
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
        'core-host-logs.check_again': '再次检查',
        'core-host-logs.help':
          'host_log_paths 中列出的日志属于 root，位于此账户之外。在大多数共享服务器上，它们会被列出但拒绝访问；这本身就是答案。主机授予读取权限时，它们会在与账户自身日志相同的查看器中打开。',
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
