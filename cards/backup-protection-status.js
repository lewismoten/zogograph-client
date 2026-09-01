/* Core card package: backup measurement and action safety status labels. */
window.ZogographBackupProtectionStatusCard = {
  manifest: Object.freeze({
    id: 'core-backup-protection-status',
    locales: ['core-backup-protection-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-protection-status.not_measured': 'not measured',
        'core-backup-protection-status.matched': 'Matched',
        'core-backup-protection-status.reach_here': 'you can do this here',
        'core-backup-protection-status.reach_root': 'needs root or WHM',
        'core-backup-protection-status.safe': 'Safe',
        'core-backup-protection-status.review': 'Review',
        'core-backup-protection-status.dangerous': 'Dangerous',
      }),
      es: Object.freeze({
        'core-backup-protection-status.not_measured': 'sin medir',
        'core-backup-protection-status.matched': 'Coincidente',
        'core-backup-protection-status.reach_here': 'puede hacerlo aquí',
        'core-backup-protection-status.reach_root': 'requiere root o WHM',
        'core-backup-protection-status.safe': 'Seguro',
        'core-backup-protection-status.review': 'Revisar',
        'core-backup-protection-status.dangerous': 'Peligroso',
      }),
      hi: Object.freeze({
        'core-backup-protection-status.not_measured': 'मापा नहीं गया',
        'core-backup-protection-status.matched': 'मेल खाता है',
        'core-backup-protection-status.reach_here': 'आप इसे यहीं कर सकते हैं',
        'core-backup-protection-status.reach_root': 'root या WHM आवश्यक है',
        'core-backup-protection-status.safe': 'सुरक्षित',
        'core-backup-protection-status.review': 'समीक्षा',
        'core-backup-protection-status.dangerous': 'खतरनाक',
      }),
      ar: Object.freeze({
        'core-backup-protection-status.not_measured': 'لم يُقَس',
        'core-backup-protection-status.matched': 'متطابق',
        'core-backup-protection-status.reach_here': 'يمكنك فعل هذا هنا',
        'core-backup-protection-status.reach_root': 'يتطلب root أو WHM',
        'core-backup-protection-status.safe': 'آمن',
        'core-backup-protection-status.review': 'مراجعة',
        'core-backup-protection-status.dangerous': 'خطير',
      }),
      zh: Object.freeze({
        'core-backup-protection-status.not_measured': '未测量',
        'core-backup-protection-status.matched': '匹配',
        'core-backup-protection-status.reach_here': '您可以在此处执行',
        'core-backup-protection-status.reach_root': '需要 root 或 WHM',
        'core-backup-protection-status.safe': '安全',
        'core-backup-protection-status.review': '审查',
        'core-backup-protection-status.dangerous': '危险',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-backup-protection-status.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
