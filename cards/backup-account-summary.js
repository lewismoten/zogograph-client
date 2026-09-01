/* Core card package: backup ownership and age summary table. */
window.ZogographBackupAccountSummaryCard = {
  manifest: Object.freeze({
    id: 'core-backup-account-summary',
    locales: ['core-backup-account-summary.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-account-summary.account': 'Account',
        'core-backup-account-summary.live_data': 'Live data',
        'core-backup-account-summary.backups': 'Backups',
        'core-backup-account-summary.ratio': 'Ratio',
        'core-backup-account-summary.copies': 'Copies',
        'core-backup-account-summary.oldest': 'Oldest',
        'core-backup-account-summary.newest': 'Newest',
        'core-backup-account-summary.not_attributed': 'not attributed',
        'core-backup-account-summary.by_account': 'By account',
      }),
      es: Object.freeze({
        'core-backup-account-summary.account': 'Cuenta',
        'core-backup-account-summary.live_data': 'Datos activos',
        'core-backup-account-summary.backups': 'Copias de seguridad',
        'core-backup-account-summary.ratio': 'Relación',
        'core-backup-account-summary.copies': 'Copias',
        'core-backup-account-summary.oldest': 'Más antiguo',
        'core-backup-account-summary.newest': 'Más reciente',
        'core-backup-account-summary.not_attributed': 'sin atribuir',
        'core-backup-account-summary.by_account': 'Por cuenta',
      }),
      hi: Object.freeze({
        'core-backup-account-summary.account': 'खाता',
        'core-backup-account-summary.live_data': 'लाइव डेटा',
        'core-backup-account-summary.backups': 'बैकअप',
        'core-backup-account-summary.ratio': 'अनुपात',
        'core-backup-account-summary.copies': 'प्रतियां',
        'core-backup-account-summary.oldest': 'सबसे पुराना',
        'core-backup-account-summary.newest': 'सबसे नया',
        'core-backup-account-summary.not_attributed': 'असंबद्ध',
        'core-backup-account-summary.by_account': 'खाते के अनुसार',
      }),
      ar: Object.freeze({
        'core-backup-account-summary.account': 'الحساب',
        'core-backup-account-summary.live_data': 'البيانات الحية',
        'core-backup-account-summary.backups': 'النسخ الاحتياطية',
        'core-backup-account-summary.ratio': 'النسبة',
        'core-backup-account-summary.copies': 'النسخ',
        'core-backup-account-summary.oldest': 'الأقدم',
        'core-backup-account-summary.newest': 'الأحدث',
        'core-backup-account-summary.not_attributed': 'غير منسوب',
        'core-backup-account-summary.by_account': 'حسب الحساب',
      }),
      zh: Object.freeze({
        'core-backup-account-summary.account': '账户',
        'core-backup-account-summary.live_data': '实时数据',
        'core-backup-account-summary.backups': '备份',
        'core-backup-account-summary.ratio': '比率',
        'core-backup-account-summary.copies': '副本',
        'core-backup-account-summary.oldest': '最早',
        'core-backup-account-summary.newest': '最新',
        'core-backup-account-summary.not_attributed': '未归属',
        'core-backup-account-summary.by_account': '按账户',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-backup-account-summary.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
