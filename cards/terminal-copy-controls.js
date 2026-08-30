/* Core card package: copy controls for the live terminal transcript. */
window.ZogographTerminalCopyControlsCard = {
  manifest: Object.freeze({
    id: 'core-terminal-copy-controls',
    locales: ['core-terminal-copy-controls.'],
    helpers: ['entryAsText', 'copyText', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-copy-controls.last_output': 'Copy the last output',
        'core-terminal-copy-controls.everything': 'Copy everything',
        'core-terminal-copy-controls.commands': 'Copy just the commands',
      }),
      es: Object.freeze({
        'core-terminal-copy-controls.last_output': 'Copiar la última salida',
        'core-terminal-copy-controls.everything': 'Copiar todo',
        'core-terminal-copy-controls.commands': 'Copiar solo los comandos',
      }),
      hi: Object.freeze({
        'core-terminal-copy-controls.last_output': 'अंतिम आउटपुट कॉपी करें',
        'core-terminal-copy-controls.everything': 'सब कुछ कॉपी करें',
        'core-terminal-copy-controls.commands': 'केवल कमांड कॉपी करें',
      }),
      ar: Object.freeze({
        'core-terminal-copy-controls.last_output': 'نسخ آخر مخرجات',
        'core-terminal-copy-controls.everything': 'نسخ كل شيء',
        'core-terminal-copy-controls.commands': 'نسخ الأوامر فقط',
      }),
      zh: Object.freeze({
        'core-terminal-copy-controls.last_output': '复制最后输出',
        'core-terminal-copy-controls.everything': '复制全部',
        'core-terminal-copy-controls.commands': '仅复制命令',
      }),
    }),
  }),

  render(entries, helpers) {
    const { entryAsText, copyText, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const row = document.createElement('div');
    row.className = 'term-copy';
    const options = [
      [
        t(
          'core-terminal-copy-controls.last_output',
          {},
          'Copy the last output',
        ),
        () => entries.at(-1)?.text ?? '',
      ],
      [
        t('core-terminal-copy-controls.everything', {}, 'Copy everything'),
        () => entries.map(entryAsText).join('\n'),
      ],
      [
        t('core-terminal-copy-controls.commands', {}, 'Copy just the commands'),
        () => entries.map((entry) => entry.command).join('\n'),
      ],
    ];
    for (const [label, take] of options) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'link';
      button.textContent = label;
      button.addEventListener('click', () => copyText(take()));
      row.appendChild(button);
    }
    return row;
  },
};
