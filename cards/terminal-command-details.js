/* Core card package: explanation and fallback controls for suggested commands. */
window.ZogographTerminalCommandDetailsCard = {
  manifest: Object.freeze({
    id: 'core-terminal-command-details',
    locales: ['core-terminal-command-details.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-command-details.suggestion_expectation':
          'What to look for: {{expectation}}',
        'core-terminal-command-details.copy_manually':
          'Select the command and copy it — this browser would not.',
        'core-terminal-command-details.command_reading':
          'What this command does',
      }),
      es: Object.freeze({
        'core-terminal-command-details.suggestion_expectation':
          'Qué buscar: {{expectation}}',
        'core-terminal-command-details.copy_manually':
          'Seleccione el comando y cópielo; este navegador no lo permitió.',
        'core-terminal-command-details.command_reading':
          'Qué hace este comando',
      }),
      hi: Object.freeze({
        'core-terminal-command-details.suggestion_expectation':
          'क्या देखना है: {{expectation}}',
        'core-terminal-command-details.copy_manually':
          'कमांड चुनें और कॉपी करें — इस ब्राउज़र ने इसे नहीं करने दिया।',
        'core-terminal-command-details.command_reading':
          'यह कमांड क्या करता है',
      }),
      ar: Object.freeze({
        'core-terminal-command-details.suggestion_expectation':
          'ما الذي تبحث عنه: {{expectation}}',
        'core-terminal-command-details.copy_manually':
          'حدد الأمر وانسخه — لم يسمح هذا المتصفح بذلك.',
        'core-terminal-command-details.command_reading':
          'ما الذي يفعله هذا الأمر',
      }),
      zh: Object.freeze({
        'core-terminal-command-details.suggestion_expectation':
          '要查找的内容：{{expectation}}',
        'core-terminal-command-details.copy_manually':
          '请选择该命令并复制——此浏览器不允许这样做。',
        'core-terminal-command-details.command_reading': '此命令的作用',
      }),
    }),
  }),

  render({ key, values = {} }, { i18n }) {
    const id = `core-terminal-command-details.${key}`;
    return i18n.t(id, values, this.manifest.catalogs.en[id]);
  },
};
