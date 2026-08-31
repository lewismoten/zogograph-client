/* Core card package: actions for the most recent terminal command output. */
window.ZogographTerminalActionsCard = {
  manifest: Object.freeze({
    id: 'core-terminal-actions',
    locales: ['core-terminal-actions.'],
    helpers: ['target', 'sendOutput', 'notice', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-actions.send_output_to':
          'Send this output to {{assistant}}',
        'core-terminal-actions.send_output_help':
          'It goes into the conversation as a message from you.',
        'core-terminal-actions.copy_output': 'Copy the output',
        'core-terminal-actions.copy_refused':
          'This browser would not let the page write to the clipboard.',
        'core-terminal-actions.assistant_fallback': 'the assistant',
      }),
      es: Object.freeze({
        'core-terminal-actions.send_output_to':
          'Enviar esta salida a {{assistant}}',
        'core-terminal-actions.send_output_help':
          'Se incorpora a la conversación como un mensaje suyo.',
        'core-terminal-actions.copy_output': 'Copiar la salida',
        'core-terminal-actions.copy_refused':
          'Este navegador no permitió que la página escribiera en el portapapeles.',
        'core-terminal-actions.assistant_fallback': 'el asistente',
      }),
      hi: Object.freeze({
        'core-terminal-actions.send_output_to':
          'यह आउटपुट {{assistant}} को भेजें',
        'core-terminal-actions.send_output_help':
          'यह बातचीत में आपकी ओर से संदेश के रूप में जाता है।',
        'core-terminal-actions.copy_output': 'आउटपुट कॉपी करें',
        'core-terminal-actions.copy_refused':
          'इस ब्राउज़र ने पृष्ठ को क्लिपबोर्ड में लिखने नहीं दिया।',
        'core-terminal-actions.assistant_fallback': 'सहायक',
      }),
      ar: Object.freeze({
        'core-terminal-actions.send_output_to':
          'أرسل هذا الإخراج إلى {{assistant}}',
        'core-terminal-actions.send_output_help':
          'يُضاف إلى المحادثة كرسالة منك.',
        'core-terminal-actions.copy_output': 'نسخ المخرجات',
        'core-terminal-actions.copy_refused':
          'لم يسمح هذا المتصفح للصفحة بالكتابة إلى الحافظة.',
        'core-terminal-actions.assistant_fallback': 'المساعد',
      }),
      zh: Object.freeze({
        'core-terminal-actions.send_output_to': '将此输出发送给 {{assistant}}',
        'core-terminal-actions.send_output_help':
          '它会作为来自您的消息进入对话。',
        'core-terminal-actions.copy_output': '复制输出',
        'core-terminal-actions.copy_refused': '此浏览器不允许页面写入剪贴板。',
        'core-terminal-actions.assistant_fallback': '助手',
      }),
    }),
  }),

  render(input, helpers) {
    const { target, sendOutput, notice, i18n } = helpers;
    const { data, text, assistant = '' } = input;
    if (!target) return;
    target.replaceChildren();
    if (!data || text === '') return;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const who =
      assistant ||
      t('core-terminal-actions.assistant_fallback', {}, 'the assistant');
    const send = document.createElement('button');
    send.className = 'link';
    send.textContent = t(
      'core-terminal-actions.send_output_to',
      { assistant: who },
      'Send this output to {{assistant}}',
    );
    send.title = t(
      'core-terminal-actions.send_output_help',
      {},
      'It goes into the conversation as a message from you.',
    );
    send.addEventListener('click', () => {
      sendOutput(data).catch((error) => notice(String(error.message || error)));
    });
    target.appendChild(send);

    const copy = document.createElement('button');
    copy.className = 'link';
    const copyLabel = () =>
      t('core-terminal-actions.copy_output', {}, 'Copy the output');
    copy.textContent = copyLabel();
    copy.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(text);
        copy.textContent = i18n.t('action.copied', {}, 'Copied');
        setTimeout(() => {
          copy.textContent = copyLabel();
        }, 1400);
      } catch {
        notice(
          t(
            'core-terminal-actions.copy_refused',
            {},
            'This browser would not let the page write to the clipboard.',
          ),
        );
      }
    });
    target.appendChild(copy);
  },
};
