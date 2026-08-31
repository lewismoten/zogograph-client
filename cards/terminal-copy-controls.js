/* Core card package: copy controls for the live terminal transcript. */
window.ZogographTerminalCopyControlsCard = {
  manifest: Object.freeze({
    id: 'core-terminal-copy-controls',
    locales: ['core-terminal-copy-controls.'],
    helpers: ['humanSpan', 'stripAnsi', 'copyText', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-copy-controls.last_output': 'Copy the last output',
        'core-terminal-copy-controls.everything': 'Copy everything',
        'core-terminal-copy-controls.commands': 'Copy just the commands',
        'core-terminal-copy-controls.running': 'running',
        'core-terminal-copy-controls.stopped': 'stopped',
        'core-terminal-copy-controls.exit': 'exit {{code}}',
      }),
      es: Object.freeze({
        'core-terminal-copy-controls.last_output': 'Copiar la última salida',
        'core-terminal-copy-controls.everything': 'Copiar todo',
        'core-terminal-copy-controls.commands': 'Copiar solo los comandos',
        'core-terminal-copy-controls.running': 'en ejecución',
        'core-terminal-copy-controls.stopped': 'detenido',
        'core-terminal-copy-controls.exit': 'salida {{code}}',
      }),
      hi: Object.freeze({
        'core-terminal-copy-controls.last_output': 'अंतिम आउटपुट कॉपी करें',
        'core-terminal-copy-controls.everything': 'सब कुछ कॉपी करें',
        'core-terminal-copy-controls.commands': 'केवल कमांड कॉपी करें',
        'core-terminal-copy-controls.running': 'चल रहा है',
        'core-terminal-copy-controls.stopped': 'रुका हुआ',
        'core-terminal-copy-controls.exit': 'निकास {{code}}',
      }),
      ar: Object.freeze({
        'core-terminal-copy-controls.last_output': 'نسخ آخر مخرجات',
        'core-terminal-copy-controls.everything': 'نسخ كل شيء',
        'core-terminal-copy-controls.commands': 'نسخ الأوامر فقط',
        'core-terminal-copy-controls.running': 'قيد التشغيل',
        'core-terminal-copy-controls.stopped': 'متوقف',
        'core-terminal-copy-controls.exit': 'خروج {{code}}',
      }),
      zh: Object.freeze({
        'core-terminal-copy-controls.last_output': '复制最后输出',
        'core-terminal-copy-controls.everything': '复制全部',
        'core-terminal-copy-controls.commands': '仅复制命令',
        'core-terminal-copy-controls.running': '正在运行',
        'core-terminal-copy-controls.stopped': '已停止',
        'core-terminal-copy-controls.exit': '退出 {{code}}',
      }),
    }),
  }),

  render(entries, helpers) {
    const { humanSpan, stripAnsi, copyText, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const entryAsText = (entry) => {
      const verdict = entry.running
        ? `(${t('core-terminal-copy-controls.running', {}, 'running')})`
        : [
            entry.took === null || entry.took === undefined
              ? ''
              : humanSpan(entry.took),
            entry.stopped_by
              ? t('core-terminal-copy-controls.stopped', {}, 'stopped')
              : '',
            entry.exit_code === null || entry.exit_code === undefined
              ? ''
              : t(
                  'core-terminal-copy-controls.exit',
                  { code: entry.exit_code },
                  'exit {{code}}',
                ),
          ]
            .filter(Boolean)
            .join(' · ');
      return (
        `$ ${entry.command}${verdict ? `   [${verdict}]` : ''}\n` +
        stripAnsi(entry.text || '')
      );
    };
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
