/* Core card package: terminal output formatted for an assistant conversation. */
window.ZogographTerminalChatTransferCard = {
  manifest: Object.freeze({
    id: 'core-terminal-chat-transfer',
    locales: ['core-terminal-chat-transfer.'],
    helpers: ['humanCount', 'humanBytes', 'humanSpan', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-terminal-chat-transfer.truncated':
          '\n\n… {{amount}} characters from the middle are not shown …\n\n',
        'core-terminal-chat-transfer.truncated_note':
          ' The middle was left out; {{size}} in total.',
        'core-terminal-chat-transfer.intro':
          'I ran this in the terminal:\n\n$ {{command}}\n\n',
        'core-terminal-chat-transfer.running': 'is still going',
        'core-terminal-chat-transfer.timed_out': 'hit the time limit',
        'core-terminal-chat-transfer.stopped': 'was stopped',
        'core-terminal-chat-transfer.finished': 'finished',
        'core-terminal-chat-transfer.summary':
          'It {{state}} after {{duration}}.{{note}}\n\nOutput:\n\n{{output}}',
      }),
      es: Object.freeze({
        'core-terminal-chat-transfer.truncated':
          '\n\n… no se muestran {{amount}} caracteres del medio …\n\n',
        'core-terminal-chat-transfer.truncated_note':
          ' Se omitió la parte central; {{size}} en total.',
        'core-terminal-chat-transfer.intro':
          'Ejecuté esto en la terminal:\n\n$ {{command}}\n\n',
        'core-terminal-chat-transfer.running': 'sigue en ejecución',
        'core-terminal-chat-transfer.timed_out': 'alcanzó el límite de tiempo',
        'core-terminal-chat-transfer.stopped': 'se detuvo',
        'core-terminal-chat-transfer.finished': 'terminó',
        'core-terminal-chat-transfer.summary':
          'El comando {{state}} después de {{duration}}.{{note}}\n\nSalida:\n\n{{output}}',
      }),
      hi: Object.freeze({
        'core-terminal-chat-transfer.truncated':
          '\n\n… बीच के {{amount}} वर्ण नहीं दिखाए गए हैं …\n\n',
        'core-terminal-chat-transfer.truncated_note':
          ' बीच का भाग छोड़ दिया गया; कुल {{size}}.',
        'core-terminal-chat-transfer.intro':
          'मैंने टर्मिनल में यह चलाया:\n\n$ {{command}}\n\n',
        'core-terminal-chat-transfer.running': 'अभी भी चल रहा है',
        'core-terminal-chat-transfer.timed_out': 'समय सीमा पर पहुंच गया',
        'core-terminal-chat-transfer.stopped': 'रोक दिया गया',
        'core-terminal-chat-transfer.finished': 'समाप्त हुआ',
        'core-terminal-chat-transfer.summary':
          'यह {{duration}} बाद {{state}}।{{note}}\n\nआउटपुट:\n\n{{output}}',
      }),
      ar: Object.freeze({
        'core-terminal-chat-transfer.truncated':
          '\n\n… لا تظهر {{amount}} حرفًا من المنتصف …\n\n',
        'core-terminal-chat-transfer.truncated_note':
          ' تم حذف الجزء الأوسط؛ الحجم الكلي {{size}}.',
        'core-terminal-chat-transfer.intro':
          'شغّلت هذا في الطرفية:\n\n$ {{command}}\n\n',
        'core-terminal-chat-transfer.running': 'ما زال قيد التشغيل',
        'core-terminal-chat-transfer.timed_out': 'بلغ الحد الزمني',
        'core-terminal-chat-transfer.stopped': 'تم إيقافه',
        'core-terminal-chat-transfer.finished': 'انتهى',
        'core-terminal-chat-transfer.summary':
          'الأمر {{state}} بعد {{duration}}.{{note}}\n\nالمخرجات:\n\n{{output}}',
      }),
      zh: Object.freeze({
        'core-terminal-chat-transfer.truncated':
          '\n\n… 未显示中间的 {{amount}} 个字符 …\n\n',
        'core-terminal-chat-transfer.truncated_note':
          ' 中间部分已省略；总计 {{size}}。',
        'core-terminal-chat-transfer.intro':
          '我在终端中运行了：\n\n$ {{command}}\n\n',
        'core-terminal-chat-transfer.running': '仍在运行',
        'core-terminal-chat-transfer.timed_out': '已达到时间限制',
        'core-terminal-chat-transfer.stopped': '已停止',
        'core-terminal-chat-transfer.finished': '已完成',
        'core-terminal-chat-transfer.summary':
          '它在 {{duration}} 后{{state}}。{{note}}\n\n输出：\n\n{{output}}',
      }),
    }),
  }),

  render(input, helpers) {
    const { data, limit, text: originalText } = input;
    const { humanCount, humanBytes, humanSpan, i18n } = helpers;
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    let text = originalText;
    let note = '';

    if (text.length > limit) {
      const half = Math.floor(limit / 2);
      const dropped = text.length - limit;
      text =
        text.slice(0, half) +
        t(
          'core-terminal-chat-transfer.truncated',
          { amount: humanCount(dropped) },
          '\n\n… {{amount}} characters from the middle are not shown …\n\n',
        ) +
        text.slice(-half);
      note = t(
        'core-terminal-chat-transfer.truncated_note',
        { size: humanBytes(data.size) },
        ' The middle was left out; {{size}} in total.',
      );
    }

    const state = data.running
      ? t('core-terminal-chat-transfer.running', {}, 'is still going')
      : data.timed_out
        ? t('core-terminal-chat-transfer.timed_out', {}, 'hit the time limit')
        : data.stopped_by
          ? t('core-terminal-chat-transfer.stopped', {}, 'was stopped')
          : t('core-terminal-chat-transfer.finished', {}, 'finished');
    return (
      t(
        'core-terminal-chat-transfer.intro',
        { command: data.command },
        'I ran this in the terminal:\n\n$ {{command}}\n\n',
      ) +
      t(
        'core-terminal-chat-transfer.summary',
        {
          state,
          duration: humanSpan(data.ran_for, true),
          note,
          output: text,
        },
        'It {{state}} after {{duration}}.{{note}}\n\nOutput:\n\n{{output}}',
      )
    );
  },
};
