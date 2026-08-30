/* Core card package: one terminal command, outcome, and bounded output. */
window.ZogographTerminalEntryCard = {
  manifest: Object.freeze({
    id: 'core-terminal-entry',
    locales: ['core-terminal-entry.'],
    helpers: ['outputLimit', 'verdict', 'humanBytes', 'ansiToNodes', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-entry.output_truncated':
          '{{amount}} earlier in this command is not drawn. Copy it to get all of it.',
      }),
      es: Object.freeze({
        'core-terminal-entry.output_truncated':
          'No se muestran {{amount}} anteriores de este comando. Cópielo para obtener todo.',
      }),
      hi: Object.freeze({
        'core-terminal-entry.output_truncated':
          'इस कमांड का पहले का {{amount}} नहीं दिखाया गया है। सब पाने के लिए इसे कॉपी करें।',
      }),
      ar: Object.freeze({
        'core-terminal-entry.output_truncated':
          'لا يُعرض {{amount}} سابق من هذا الأمر. انسخه للحصول على كل المخرجات.',
      }),
      zh: Object.freeze({
        'core-terminal-entry.output_truncated':
          '未显示此命令较早的 {{amount}} 输出。复制可获取全部内容。',
      }),
    }),
  }),

  render(entry, helpers) {
    const { outputLimit, verdict, humanBytes, ansiToNodes, i18n } = helpers;
    const wrap = document.createElement('div');
    wrap.className = 'term-entry';
    if (entry.running) wrap.classList.add('running');
    const prompt = document.createElement('div');
    prompt.className = 'term-prompt';
    const mark = document.createElement('span');
    mark.className = 'term-mark';
    mark.textContent = '$';
    const command = document.createElement('span');
    command.className = 'term-command';
    command.textContent = entry.command;
    prompt.append(mark, command, verdict(entry));
    wrap.appendChild(prompt);
    if (!entry.text) return wrap;
    const body = document.createElement('div');
    body.className = 'term-body';
    const text =
      entry.text.length > outputLimit
        ? entry.text.slice(-outputLimit)
        : entry.text;
    if (text !== entry.text) {
      const truncated = document.createElement('div');
      truncated.className = 'term-folded';
      truncated.textContent = i18n.t(
        'core-terminal-entry.output_truncated',
        { amount: humanBytes(entry.text.length - text.length) },
        '{{amount}} earlier in this command is not drawn. Copy it to get all of it.',
      );
      body.appendChild(truncated);
    }
    body.appendChild(ansiToNodes(text));
    wrap.appendChild(body);
    return wrap;
  },
};
