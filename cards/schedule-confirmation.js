/* Core card package: crontab change confirmation dialog. */
window.ZogographScheduleConfirmationCard = {
  manifest: Object.freeze({
    id: 'core-schedule-confirmation',
    locales: ['core-schedule-confirmation.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-schedule-confirmation.remove':
          "Remove this dashboard's lines from your crontab?",
        'core-schedule-confirmation.write':
          'Write this into your crontab?\n\n{{preview}}',
        'core-schedule-confirmation.unchanged':
          'Nothing else in it is changed.',
        'core-schedule-confirmation.title': 'Apply crontab change',
      }),
      es: Object.freeze({
        'core-schedule-confirmation.remove':
          '¿Eliminar del crontab las líneas de este panel?',
        'core-schedule-confirmation.write':
          '¿Escribir esto en su crontab?\n\n{{preview}}',
        'core-schedule-confirmation.unchanged': 'No se modifica nada más.',
        'core-schedule-confirmation.title': 'Aplicar cambio de crontab',
      }),
      hi: Object.freeze({
        'core-schedule-confirmation.remove':
          'इस डैशबोर्ड की पंक्तियाँ अपने crontab से हटाएँ?',
        'core-schedule-confirmation.write':
          'इसे अपने crontab में लिखें?\n\n{{preview}}',
        'core-schedule-confirmation.unchanged': 'इसमें और कुछ नहीं बदला जाएगा।',
        'core-schedule-confirmation.title': 'crontab परिवर्तन लागू करें',
      }),
      ar: Object.freeze({
        'core-schedule-confirmation.remove':
          'إزالة سطور هذه اللوحة من crontab لديك؟',
        'core-schedule-confirmation.write':
          'كتابة هذا في crontab لديك؟\n\n{{preview}}',
        'core-schedule-confirmation.unchanged': 'لن يتغير أي شيء آخر فيه.',
        'core-schedule-confirmation.title': 'تطبيق تغيير crontab',
      }),
      zh: Object.freeze({
        'core-schedule-confirmation.remove':
          '从您的 crontab 中移除此仪表板的行吗？',
        'core-schedule-confirmation.write':
          '将此内容写入您的 crontab 吗？\n\n{{preview}}',
        'core-schedule-confirmation.unchanged': '其中的其他内容不会被更改。',
        'core-schedule-confirmation.title': '应用 crontab 更改',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-schedule-confirmation.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
