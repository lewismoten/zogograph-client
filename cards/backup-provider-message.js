/* Core card package: safe provider-facing guidance for backup findings. */
window.ZogographBackupProviderMessageCard = {
  manifest: Object.freeze({
    id: 'core-backup-provider-message',
    locales: ['core-backup-provider-message.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-backup-provider-message.title':
          'Message for your hosting provider',
        'core-backup-provider-message.help':
          'Explains what was measured and requests only safe actions. It deliberately does not ask anyone to delete anything in the backup directory.',
      }),
      es: Object.freeze({
        'core-backup-provider-message.title':
          'Mensaje para su proveedor de alojamiento',
        'core-backup-provider-message.help':
          'Explica qué se midió y solicita únicamente acciones seguras. Deliberadamente no pide a nadie que elimine nada del directorio de copias de seguridad.',
      }),
      hi: Object.freeze({
        'core-backup-provider-message.title':
          'अपने होस्टिंग प्रदाता के लिए संदेश',
        'core-backup-provider-message.help':
          'यह बताता है कि क्या मापा गया था और केवल सुरक्षित कार्रवाई का अनुरोध करता है। इसमें जानबूझकर किसी से बैकअप निर्देशिका से कुछ भी हटाने के लिए नहीं कहा गया है।',
      }),
      ar: Object.freeze({
        'core-backup-provider-message.title':
          'رسالة إلى مزود الاستضافة الخاص بك',
        'core-backup-provider-message.help':
          'يوضح ما تم قياسه ويطلب الإجراءات الآمنة فقط. وهو لا يطلب عمدًا من أي شخص حذف أي شيء من دليل النسخ الاحتياطي.',
      }),
      zh: Object.freeze({
        'core-backup-provider-message.title': '给您的托管服务提供商的消息',
        'core-backup-provider-message.help':
          '说明了已测量的内容，并且只请求安全的操作。它特意不会要求任何人删除备份目录中的任何内容。',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-backup-provider-message.${key}`,
      {},
      this.manifest.catalogs.en[`core-backup-provider-message.${key}`],
    );
  },
  render() {},
};
