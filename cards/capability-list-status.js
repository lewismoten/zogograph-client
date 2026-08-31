/* Core card package: concise availability states in the capability list. */
window.ZogographCapabilityListStatusCard = {
  manifest: Object.freeze({
    id: 'core-capability-list-status',
    locales: ['core-capability-list-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-capability-list-status.alternate': 'alternate path',
        'core-capability-list-status.host_only': 'host-only',
        'core-capability-list-status.unusable': 'not executable',
        'core-capability-list-status.missing': 'not installed',
        'core-capability-list-status.unknown_host': 'not here',
      }),
      es: Object.freeze({
        'core-capability-list-status.alternate': 'ruta alternativa',
        'core-capability-list-status.host_only': 'solo en el host',
        'core-capability-list-status.unusable': 'no ejecutable',
        'core-capability-list-status.missing': 'no instalado',
        'core-capability-list-status.unknown_host': 'no está aquí',
      }),
      hi: Object.freeze({
        'core-capability-list-status.alternate': 'वैकल्पिक पथ',
        'core-capability-list-status.host_only': 'केवल होस्ट',
        'core-capability-list-status.unusable': 'निष्पादित नहीं हो सकता',
        'core-capability-list-status.missing': 'स्थापित नहीं',
        'core-capability-list-status.unknown_host': 'यहाँ नहीं',
      }),
      ar: Object.freeze({
        'core-capability-list-status.alternate': 'مسار بديل',
        'core-capability-list-status.host_only': 'المضيف فقط',
        'core-capability-list-status.unusable': 'غير قابل للتنفيذ',
        'core-capability-list-status.missing': 'غير مثبت',
        'core-capability-list-status.unknown_host': 'ليس هنا',
      }),
      zh: Object.freeze({
        'core-capability-list-status.alternate': '替代路径',
        'core-capability-list-status.host_only': '仅主机',
        'core-capability-list-status.unusable': '不可执行',
        'core-capability-list-status.missing': '未安装',
        'core-capability-list-status.unknown_host': '不在此处',
      }),
    }),
  }),
  text(key, helpers) {
    const localeKey = `core-capability-list-status.${key}`;
    return helpers.i18n.t(localeKey, {}, this.manifest.catalogs.en[localeKey]);
  },
  render(data, helpers) {
    return data?.state === 'available'
      ? ''
      : this.text(data?.state || 'missing', helpers);
  },
};
