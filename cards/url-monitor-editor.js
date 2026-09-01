/* Core card package: title and address controls for watched URL editing. */
window.ZogographUrlMonitorEditorCard = {
  manifest: Object.freeze({
    id: 'core-url-monitor-editor',
    locales: ['core-url-monitor-editor.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-url-monitor-editor.editing': 'Editing {{label}}',
        'core-url-monitor-editor.add_check': 'Add a check',
        'core-url-monitor-editor.address_placeholder': 'https://example.com/',
        'core-url-monitor-editor.address': 'Address',
        'core-url-monitor-editor.address_help':
          'The address somebody would actually type.',
        'core-url-monitor-editor.label_placeholder':
          'left blank, the host is used',
        'core-url-monitor-editor.label': 'Called',
        'core-url-monitor-editor.interval': 'How often',
      }),
      es: Object.freeze({
        'core-url-monitor-editor.editing': 'Editando {{label}}',
        'core-url-monitor-editor.add_check': 'Añadir una comprobación',
        'core-url-monitor-editor.address_placeholder': 'https://ejemplo.com/',
        'core-url-monitor-editor.address': 'Dirección',
        'core-url-monitor-editor.address_help':
          'La dirección que alguien escribiría realmente.',
        'core-url-monitor-editor.label_placeholder':
          'déjelo vacío para usar el host',
        'core-url-monitor-editor.label': 'Nombre',
        'core-url-monitor-editor.interval': 'Frecuencia',
      }),
      hi: Object.freeze({
        'core-url-monitor-editor.editing': '{{label}} संपादित किया जा रहा है',
        'core-url-monitor-editor.add_check': 'जांच जोड़ें',
        'core-url-monitor-editor.address_placeholder': 'https://example.com/',
        'core-url-monitor-editor.address': 'पता',
        'core-url-monitor-editor.address_help':
          'वह पता जो कोई वास्तव में टाइप करेगा।',
        'core-url-monitor-editor.label_placeholder':
          'खाली छोड़ने पर होस्ट इस्तेमाल होता है',
        'core-url-monitor-editor.label': 'नाम',
        'core-url-monitor-editor.interval': 'कितनी बार',
      }),
      ar: Object.freeze({
        'core-url-monitor-editor.editing': 'تحرير {{label}}',
        'core-url-monitor-editor.add_check': 'أضف فحصًا',
        'core-url-monitor-editor.address_placeholder': 'https://example.com/',
        'core-url-monitor-editor.address': 'العنوان',
        'core-url-monitor-editor.address_help':
          'العنوان الذي سيكتبه شخص فعليًا.',
        'core-url-monitor-editor.label_placeholder':
          'اتركه فارغًا لاستخدام المضيف',
        'core-url-monitor-editor.label': 'الاسم',
        'core-url-monitor-editor.interval': 'كم مرة',
      }),
      zh: Object.freeze({
        'core-url-monitor-editor.editing': '正在编辑 {{label}}',
        'core-url-monitor-editor.add_check': '添加检查',
        'core-url-monitor-editor.address_placeholder': 'https://example.com/',
        'core-url-monitor-editor.address': '地址',
        'core-url-monitor-editor.address_help': '用户实际会输入的地址。',
        'core-url-monitor-editor.label_placeholder': '留空则使用主机名',
        'core-url-monitor-editor.label': '名称',
        'core-url-monitor-editor.interval': '频率',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-url-monitor-editor.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
