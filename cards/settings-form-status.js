/* Core card package: shared feedback when a settings form cannot refresh or save. */
window.ZogographSettingsFormStatusCard = {
  manifest: Object.freeze({
    id: 'core-settings-form-status',
    locales: ['core-settings-form-status.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-settings-form-status.form_held':
          'Not refreshed while you are editing. Save, or move away and come back, to see what is stored.',
        'core-settings-form-status.config_not_writable':
          'lib/config.php is not writable, so these cannot change.',
      }),
      es: Object.freeze({
        'core-settings-form-status.form_held':
          'No se actualizó mientras estaba editando. Guarde o salga y vuelva para ver qué se almacenó.',
        'core-settings-form-status.config_not_writable':
          'lib/config.php no permite escritura, por lo que no se pueden cambiar estos valores.',
      }),
      hi: Object.freeze({
        'core-settings-form-status.form_held':
          'संपादन के दौरान इसे रीफ़्रेश नहीं किया गया। सहेजें या दूर जाकर वापस आएँ ताकि संग्रहित जानकारी देख सकें।',
        'core-settings-form-status.config_not_writable':
          'lib/config.php लिखने योग्य नहीं है, इसलिए इन्हें बदला नहीं जा सकता।',
      }),
      ar: Object.freeze({
        'core-settings-form-status.form_held':
          'لم تُحدَّث الصفحة أثناء التحرير. احفظ، أو انتقل بعيدًا ثم عد لترى ما حُفظ.',
        'core-settings-form-status.config_not_writable':
          'لا يمكن الكتابة إلى lib/config.php، لذا لا يمكن تغيير هذه الإعدادات.',
      }),
      zh: Object.freeze({
        'core-settings-form-status.form_held':
          '编辑时不会刷新。请保存，或离开后再回来查看已存储的内容。',
        'core-settings-form-status.config_not_writable':
          'lib/config.php 不可写，因此无法更改这些设置。',
      }),
    }),
  }),
  render({ key, values = {} }, { i18n }) {
    const id = `core-settings-form-status.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
