window.ZogographEmailSaveControlsCard = {
  manifest: Object.freeze({
    id: 'core-email-save-controls',
    locales: ['core-email-save-controls.'],
    helpers: ['save', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-email-save-controls.save': 'Save these settings',
      }),
      es: Object.freeze({
        'core-email-save-controls.save': 'Guardar estos ajustes',
      }),
      hi: Object.freeze({
        'core-email-save-controls.save': 'ये सेटिंग सहेजें',
      }),
      ar: Object.freeze({
        'core-email-save-controls.save': 'احفظ هذه الإعدادات',
      }),
      zh: Object.freeze({
        'core-email-save-controls.save': '保存这些设置',
      }),
    }),
  }),
  render(data, helpers) {
    const save = document.createElement('div');
    save.className = 'inspect-actions';
    const button = document.createElement('button');
    button.id = 'emailSave';
    button.textContent = helpers.i18n.t(
      'core-email-save-controls.save',
      {},
      'Save these settings',
    );
    button.disabled = !data.writable;
    button.addEventListener('click', helpers.save);
    save.appendChild(button);
    return save;
  },
};
