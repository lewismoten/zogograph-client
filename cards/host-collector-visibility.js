/* Core card package: visibility reported by the elevated host collector. */
window.ZogographHostCollectorVisibilityCard = {
  manifest: Object.freeze({
    id: 'core-host-collector-visibility',
    locales: ['core-host-collector-visibility.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-host-collector-visibility.kernel': 'Kernel {{kernel}}',
        'core-host-collector-visibility.device_readable':
          'device counters readable',
        'core-host-collector-visibility.device_unreadable':
          'device counters unreadable',
        'core-host-collector-visibility.psi_available':
          'pressure stall info available',
        'core-host-collector-visibility.psi_unavailable':
          'pressure stall info unavailable',
        'core-host-collector-visibility.summary':
          'As root, the collector sees: {{values}}.',
      }),
      es: Object.freeze({
        'core-host-collector-visibility.kernel': 'Kernel {{kernel}}',
        'core-host-collector-visibility.device_readable':
          'contadores de dispositivo legibles',
        'core-host-collector-visibility.device_unreadable':
          'contadores de dispositivo no legibles',
        'core-host-collector-visibility.psi_available':
          'información de bloqueo por presión disponible',
        'core-host-collector-visibility.psi_unavailable':
          'información de bloqueo por presión no disponible',
        'core-host-collector-visibility.summary':
          'Como root, el recopilador ve: {{values}}.',
      }),
      hi: Object.freeze({
        'core-host-collector-visibility.kernel': 'कर्नेल {{kernel}}',
        'core-host-collector-visibility.device_readable':
          'डिवाइस काउंटर पढ़ने योग्य',
        'core-host-collector-visibility.device_unreadable':
          'डिवाइस काउंटर पढ़ने योग्य नहीं',
        'core-host-collector-visibility.psi_available':
          'प्रेशर स्टॉल जानकारी उपलब्ध',
        'core-host-collector-visibility.psi_unavailable':
          'प्रेशर स्टॉल जानकारी उपलब्ध नहीं',
        'core-host-collector-visibility.summary':
          'root के रूप में कलेक्टर देखता है: {{values}}।',
      }),
      ar: Object.freeze({
        'core-host-collector-visibility.kernel': 'النواة {{kernel}}',
        'core-host-collector-visibility.device_readable':
          'عدادات الأجهزة قابلة للقراءة',
        'core-host-collector-visibility.device_unreadable':
          'عدادات الأجهزة غير قابلة للقراءة',
        'core-host-collector-visibility.psi_available':
          'معلومات توقف الضغط متاحة',
        'core-host-collector-visibility.psi_unavailable':
          'معلومات توقف الضغط غير متاحة',
        'core-host-collector-visibility.summary':
          'بصفته root، يرى الجامع: {{values}}.',
      }),
      zh: Object.freeze({
        'core-host-collector-visibility.kernel': '内核 {{kernel}}',
        'core-host-collector-visibility.device_readable': '设备计数器可读',
        'core-host-collector-visibility.device_unreadable': '设备计数器不可读',
        'core-host-collector-visibility.psi_available': '压力停顿信息可用',
        'core-host-collector-visibility.psi_unavailable': '压力停顿信息不可用',
        'core-host-collector-visibility.summary':
          '作为 root，收集器可见：{{values}}。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-host-collector-visibility.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
