/* Core card package: a saved terminal session and its copy controls. */
window.ZogographTerminalSessionLogCard = {
  manifest: Object.freeze({
    id: 'core-terminal-session-log',
    locales: ['core-terminal-session-log.'],
    helpers: [
      'panel',
      'title',
      'copies',
      'body',
      'whenLabel',
      'sessionEntryText',
      'stripAnsi',
      'copyText',
      'note',
      'terminalEntry',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-session-log.session_of': 'Session of {{when}}',
        'core-terminal-session-log.copy_commands': 'Copy just the commands',
        'core-terminal-session-log.copy_log': 'Copy the whole log',
        'core-terminal-session-log.copy_last_output': 'Copy the last output',
        'core-terminal-session-log.empty':
          'Nothing was run in this one, or it has been swept.',
        'core-terminal-session-log.copy_entry': 'Copy this one',
        'core-terminal-session-log.copy_entry_output': 'Copy only its output',
        'core-terminal-session-log.output_tail': 'the end of a longer output',
      }),
      es: Object.freeze({
        'core-terminal-session-log.session_of': 'Sesión de {{when}}',
        'core-terminal-session-log.copy_commands': 'Copiar solo los comandos',
        'core-terminal-session-log.copy_log': 'Copiar todo el registro',
        'core-terminal-session-log.copy_last_output': 'Copiar la última salida',
        'core-terminal-session-log.empty':
          'No se ejecutó nada aquí, o ya se eliminó.',
        'core-terminal-session-log.copy_entry': 'Copiar este',
        'core-terminal-session-log.copy_entry_output': 'Copiar solo su salida',
        'core-terminal-session-log.output_tail':
          'el final de una salida más larga',
      }),
      hi: Object.freeze({
        'core-terminal-session-log.session_of': '{{when}} का सत्र',
        'core-terminal-session-log.copy_commands': 'केवल कमांड कॉपी करें',
        'core-terminal-session-log.copy_log': 'पूरा लॉग कॉपी करें',
        'core-terminal-session-log.copy_last_output': 'अंतिम आउटपुट कॉपी करें',
        'core-terminal-session-log.empty':
          'इसमें कुछ नहीं चलाया गया, या इसे साफ़ कर दिया गया है।',
        'core-terminal-session-log.copy_entry': 'इसे कॉपी करें',
        'core-terminal-session-log.copy_entry_output':
          'केवल इसका आउटपुट कॉपी करें',
        'core-terminal-session-log.output_tail': 'लंबे आउटपुट का अंतिम भाग',
      }),
      ar: Object.freeze({
        'core-terminal-session-log.session_of': 'جلسة {{when}}',
        'core-terminal-session-log.copy_commands': 'نسخ الأوامر فقط',
        'core-terminal-session-log.copy_log': 'نسخ السجل كاملًا',
        'core-terminal-session-log.copy_last_output': 'نسخ آخر مخرجات',
        'core-terminal-session-log.empty': 'لم يُشغل شيء هنا، أو تم تنظيفه.',
        'core-terminal-session-log.copy_entry': 'نسخ هذا',
        'core-terminal-session-log.copy_entry_output': 'نسخ مخرجاته فقط',
        'core-terminal-session-log.output_tail': 'نهاية مخرجات أطول',
      }),
      zh: Object.freeze({
        'core-terminal-session-log.session_of': '{{when}} 的会话',
        'core-terminal-session-log.copy_commands': '仅复制命令',
        'core-terminal-session-log.copy_log': '复制完整日志',
        'core-terminal-session-log.copy_last_output': '复制最后输出',
        'core-terminal-session-log.empty':
          '此会话未运行任何内容，或内容已被清除。',
        'core-terminal-session-log.copy_entry': '复制此项',
        'core-terminal-session-log.copy_entry_output': '仅复制其输出',
        'core-terminal-session-log.output_tail': '较长输出的末尾',
      }),
    }),
  }),

  render(data, helpers) {
    const {
      panel,
      title,
      copies,
      body,
      whenLabel,
      sessionEntryText,
      stripAnsi,
      copyText,
      note,
      terminalEntry,
      i18n,
    } = helpers;
    if (!panel || !title || !copies || !body) return;
    if (!data) {
      panel.hidden = true;
      return;
    }
    panel.hidden = false;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const session = data.session;
    title.textContent =
      session?.topic ||
      t(
        'core-terminal-session-log.session_of',
        { when: whenLabel(session?.started_at ?? 0) },
        'Session of {{when}}',
      );
    const commands = data.commands || [];
    const copyOptions = [
      [
        t(
          'core-terminal-session-log.copy_commands',
          {},
          'Copy just the commands',
        ),
        () => commands.map((command) => command.command).join('\n'),
      ],
      [
        t('core-terminal-session-log.copy_log', {}, 'Copy the whole log'),
        () => commands.map(sessionEntryText).join('\n\n'),
      ],
      [
        t(
          'core-terminal-session-log.copy_last_output',
          {},
          'Copy the last output',
        ),
        () => stripAnsi(commands.at(-1)?.text || ''),
      ],
    ];
    copies.replaceChildren();
    for (const [label, take] of copyOptions) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'link';
      button.textContent = label;
      button.addEventListener('click', () => copyText(take()));
      copies.appendChild(button);
    }
    if (!commands.length) {
      body.replaceChildren(
        note(
          t(
            'core-terminal-session-log.empty',
            {},
            'Nothing was run in this one, or it has been swept.',
          ),
        ),
      );
      return;
    }
    const fragment = document.createDocumentFragment();
    for (const command of commands) {
      const entry = terminalEntry({
        command: command.command,
        text: command.text || '',
        running: command.running,
        took: command.took,
        exit_code: command.exit_code,
        timed_out: command.timed_out,
        stopped_by: command.stopped_by,
      });
      const tools = document.createElement('div');
      tools.className = 'term-copy';
      const one = document.createElement('button');
      one.type = 'button';
      one.className = 'link';
      one.textContent = t(
        'core-terminal-session-log.copy_entry',
        {},
        'Copy this one',
      );
      one.addEventListener('click', () => copyText(sessionEntryText(command)));
      tools.appendChild(one);
      if (command.text) {
        const output = document.createElement('button');
        output.type = 'button';
        output.className = 'link';
        output.textContent = t(
          'core-terminal-session-log.copy_entry_output',
          {},
          'Copy only its output',
        );
        output.addEventListener('click', () =>
          copyText(stripAnsi(command.text)),
        );
        tools.appendChild(output);
      }
      if (command.text_from_end) {
        const tail = document.createElement('span');
        tail.className = 'muted small';
        tail.textContent = t(
          'core-terminal-session-log.output_tail',
          {},
          'the end of a longer output',
        );
        tools.appendChild(tail);
      }
      entry.appendChild(tools);
      fragment.appendChild(entry);
    }
    const wrap = document.createElement('div');
    wrap.className = 'term-out session-log';
    wrap.appendChild(fragment);
    body.replaceChildren(wrap);
  },
};
