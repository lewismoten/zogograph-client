window.ZogographJetBackupCard = {
  manifest: Object.freeze({
    id: 'core-jetbackup',
    locales: ['core-jetbackup.'],
    helpers: [
      'note',
      'section',
      'simpleTable',
      'humanTime',
      'humanBytes',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-jetbackup.title': 'JetBackup',
        'core-jetbackup.help':
          'What the backup system says about its own backups is the only place the account and date behind a directory called files0000000005 can be found. It needs the JetBackup command line tool and usually needs running as root.',
        'core-jetbackup.account': 'Account',
        'core-jetbackup.when': 'When',
        'core-jetbackup.type': 'Type',
        'core-jetbackup.destination': 'Destination',
        'core-jetbackup.destinations': 'Destinations',
        'core-jetbackup.name': 'Name',
        'core-jetbackup.size': 'Size',
        'core-jetbackup.path': 'Path',
        'core-jetbackup.not_reported': 'not reported',
        'core-jetbackup.known': '{{count}} backups known to JetBackup',
        'core-jetbackup.empty':
          'JetBackup answered, but listed no backups and no destinations.',
      }),
      es: Object.freeze({
        'core-jetbackup.title': 'JetBackup',
        'core-jetbackup.help':
          'La información que el sistema de copias de seguridad ofrece sobre sus propias copias es el único lugar donde se encuentra la cuenta y la fecha tras un directorio llamado files0000000005. Requiere la herramienta de línea de comandos JetBackup y normalmente ejecutarse como root.',
        'core-jetbackup.account': 'Cuenta',
        'core-jetbackup.when': 'Cuándo',
        'core-jetbackup.type': 'Tipo',
        'core-jetbackup.destination': 'Destino',
        'core-jetbackup.destinations': 'Destinos',
        'core-jetbackup.name': 'Nombre',
        'core-jetbackup.size': 'Tamaño',
        'core-jetbackup.path': 'Ruta',
        'core-jetbackup.not_reported': 'sin informar',
        'core-jetbackup.known':
          '{{count}} copias de seguridad conocidas por JetBackup',
        'core-jetbackup.empty':
          'JetBackup respondió, pero no enumeró copias de seguridad ni destinos.',
      }),
      hi: Object.freeze({
        'core-jetbackup.title': 'JetBackup',
        'core-jetbackup.help':
          'बैकअप सिस्टम अपने बैकअप के बारे में जो बताता है, वही files0000000005 नाम की डायरेक्टरी के पीछे का खाता और तारीख जानने का एकमात्र स्थान है। इसके लिए JetBackup कमांड लाइन टूल और सामान्यतः root के रूप में चलाना आवश्यक है।',
        'core-jetbackup.account': 'खाता',
        'core-jetbackup.when': 'कब',
        'core-jetbackup.type': 'प्रकार',
        'core-jetbackup.destination': 'गंतव्य',
        'core-jetbackup.destinations': 'गंतव्य',
        'core-jetbackup.name': 'नाम',
        'core-jetbackup.size': 'आकार',
        'core-jetbackup.path': 'पथ',
        'core-jetbackup.not_reported': 'रिपोर्ट नहीं किया गया',
        'core-jetbackup.known': 'JetBackup को {{count}} बैकअप की जानकारी है',
        'core-jetbackup.empty':
          'JetBackup ने जवाब दिया, लेकिन कोई बैकअप या गंतव्य सूचीबद्ध नहीं किया।',
      }),
      ar: Object.freeze({
        'core-jetbackup.title': 'JetBackup',
        'core-jetbackup.help':
          'ما يقوله نظام النسخ الاحتياطي عن نسخه هو المكان الوحيد لمعرفة الحساب والتاريخ خلف مجلد اسمه files0000000005. يتطلب أداة سطر أوامر JetBackup، وعادةً التشغيل بصفة root.',
        'core-jetbackup.account': 'الحساب',
        'core-jetbackup.when': 'متى',
        'core-jetbackup.type': 'النوع',
        'core-jetbackup.destination': 'الوجهة',
        'core-jetbackup.destinations': 'الوجهات',
        'core-jetbackup.name': 'الاسم',
        'core-jetbackup.size': 'الحجم',
        'core-jetbackup.path': 'المسار',
        'core-jetbackup.not_reported': 'غير مُبلّغ عنه',
        'core-jetbackup.known': 'يعرف JetBackup {{count}} نسخة احتياطية',
        'core-jetbackup.empty':
          'أجاب JetBackup، لكنه لم يسرد أي نسخ احتياطية أو وجهات.',
      }),
      zh: Object.freeze({
        'core-jetbackup.title': 'JetBackup',
        'core-jetbackup.help':
          '备份系统对其自身备份的说明，是查找名为 files0000000005 的目录背后账户和日期的唯一途径。它需要 JetBackup 命令行工具，通常还需要以 root 身份运行。',
        'core-jetbackup.account': '账户',
        'core-jetbackup.when': '时间',
        'core-jetbackup.type': '类型',
        'core-jetbackup.destination': '目标位置',
        'core-jetbackup.destinations': '目标位置',
        'core-jetbackup.name': '名称',
        'core-jetbackup.size': '大小',
        'core-jetbackup.path': '路径',
        'core-jetbackup.not_reported': '未报告',
        'core-jetbackup.known': 'JetBackup 已知 {{count}} 个备份',
        'core-jetbackup.empty':
          'JetBackup 已响应，但未列出任何备份或目标位置。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-jetbackup.${key}`,
      {},
      this.manifest.catalogs.en[`core-jetbackup.${key}`],
    );
  },
  render(jet, helpers) {
    const out = document.createDocumentFragment();
    if (jet.backups?.length) {
      out.appendChild(
        helpers.section(
          helpers.i18n.t(
            'core-jetbackup.known',
            { count: jet.backups.length },
            '{{count}} backups known to JetBackup',
          ),
          helpers.simpleTable(
            [
              helpers.i18n.t('core-jetbackup.account', {}, 'Account'),
              helpers.i18n.t('core-jetbackup.when', {}, 'When'),
              helpers.i18n.t('core-jetbackup.type', {}, 'Type'),
              helpers.i18n.t('core-jetbackup.size', {}, 'Size'),
              helpers.i18n.t('core-jetbackup.destination', {}, 'Destination'),
            ],
            jet.backups.map((backup) => [
              backup.account ?? '—',
              backup.created_at ? helpers.humanTime(backup.created_at) : '—',
              backup.type ?? '—',
              backup.bytes
                ? helpers.humanBytes(backup.bytes)
                : helpers.i18n.t(
                    'core-jetbackup.not_reported',
                    {},
                    'not reported',
                  ),
              backup.destination ?? '—',
            ]),
            ['name', null, null, 'size', null],
          ),
        ),
      );
    }
    if (jet.destinations?.length) {
      out.appendChild(
        helpers.section(
          helpers.i18n.t('core-jetbackup.destinations', {}, 'Destinations'),
          helpers.simpleTable(
            [
              helpers.i18n.t('core-jetbackup.name', {}, 'Name'),
              helpers.i18n.t('core-jetbackup.type', {}, 'Type'),
              helpers.i18n.t('core-jetbackup.path', {}, 'Path'),
            ],
            jet.destinations.map((destination) => [
              destination.name ?? '—',
              destination.type ?? '—',
              destination.path ?? '—',
            ]),
            ['name', null, 'name'],
          ),
        ),
      );
    }
    if (!out.childElementCount) {
      out.appendChild(
        helpers.note(
          helpers.i18n.t(
            'core-jetbackup.empty',
            {},
            'JetBackup answered, but listed no backups and no destinations.',
          ),
        ),
      );
    }
    return out;
  },
};
