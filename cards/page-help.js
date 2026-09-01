/* Core card package: command-reference help shown for each dashboard page. */
window.ZogographPageHelpCard = {
  manifest: Object.freeze({
    id: 'core-page-help',
    locales: ['core-page-help.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-page-help.purpose': 'What this page is for',
        'core-page-help.useful': 'Why it is useful',
        'core-page-help.commands': 'Related terminal commands',
        'core-page-help.commands_note':
          'These are read-only checks or the collectors that supply this dashboard. Run only commands appropriate for this host and account.',
        'core-page-help.references': 'Official command references',
      }),
      es: Object.freeze({
        'core-page-help.purpose': 'Para qué sirve esta página',
        'core-page-help.useful': 'Por qué es útil',
        'core-page-help.commands': 'Comandos de terminal relacionados',
        'core-page-help.commands_note':
          'Estas son comprobaciones de solo lectura o los recopiladores que alimentan este panel. Ejecute solo los comandos apropiados para este host y cuenta.',
        'core-page-help.references': 'Referencias oficiales de comandos',
      }),
      hi: Object.freeze({
        'core-page-help.purpose': 'यह पृष्ठ किस लिए है',
        'core-page-help.useful': 'यह उपयोगी क्यों है',
        'core-page-help.commands': 'संबंधित टर्मिनल कमांड',
        'core-page-help.commands_note':
          'ये केवल-पढ़ने वाली जाँचें या इस डैशबोर्ड को डेटा देने वाले कलेक्टर हैं। केवल इस होस्ट और खाते के लिए उपयुक्त कमांड चलाएँ।',
        'core-page-help.references': 'आधिकारिक कमांड संदर्भ',
      }),
      ar: Object.freeze({
        'core-page-help.purpose': 'الغرض من هذه الصفحة',
        'core-page-help.useful': 'لماذا هي مفيدة',
        'core-page-help.commands': 'أوامر طرفية ذات صلة',
        'core-page-help.commands_note':
          'هذه فحوصات للقراءة فقط أو جامعات بيانات تزوّد لوحة المعلومات هذه. شغّل فقط الأوامر المناسبة لهذا المضيف والحساب.',
        'core-page-help.references': 'مراجع الأوامر الرسمية',
      }),
      zh: Object.freeze({
        'core-page-help.purpose': '此页面的用途',
        'core-page-help.useful': '为什么它有用',
        'core-page-help.commands': '相关终端命令',
        'core-page-help.commands_note':
          '这些是只读检查或为此仪表板提供数据的采集器。请仅运行适合此主机和账户的命令。',
        'core-page-help.references': '官方命令参考',
      }),
    }),
  }),
  text(key, helpers) {
    return helpers.i18n.t(
      `core-page-help.${key}`,
      {},
      this.manifest.catalogs.en[`core-page-help.${key}`],
    );
  },
  render() {},
};
