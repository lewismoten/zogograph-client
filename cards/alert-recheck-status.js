/* Core card package: confirmation shown after requesting a finding recheck. */
window.ZogographAlertRecheckStatusCard = {
  manifest: Object.freeze({
    id: 'core-alert-recheck-status',
    locales: ['core-alert-recheck-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-alert-recheck-status.queued':
          'The finding will be checked again.',
      }),
      es: Object.freeze({
        'core-alert-recheck-status.queued':
          'El hallazgo se volverá a comprobar.',
      }),
      hi: Object.freeze({
        'core-alert-recheck-status.queued': 'निष्कर्ष की फिर से जाँच की जाएगी।',
      }),
      ar: Object.freeze({
        'core-alert-recheck-status.queued': 'سيُفحص الاكتشاف مرة أخرى.',
      }),
      zh: Object.freeze({
        'core-alert-recheck-status.queued': '将再次检查此发现。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-alert-recheck-status.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
