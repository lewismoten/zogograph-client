/* Core card package: dialog explaining a conversation compaction. */
window.ZogographChatSummaryDialogCard = {
  manifest: Object.freeze({
    id: 'core-chat-summary-dialog',
    locales: ['core-chat-summary-dialog.'],
    helpers: ['renderMarkdown', 'showListDialog', 'whenLabel', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-summary-dialog.replaced_count':
          '{{messages}} earlier messages were replaced with the summary below, {{time}}.',
        'core-chat-summary-dialog.replaced':
          'The older part of this conversation was replaced with the summary below, {{time}}.',
        'core-chat-summary-dialog.retained':
          'They are all still above this point in the panel and still in the database — nothing was deleted. What changed is what the model is sent: from here on it gets this summary instead of those messages.',
        'core-chat-summary-dialog.summary':
          'The summary the model now receives',
        'core-chat-summary-dialog.range':
          'It covers messages {{from}} to {{to}} in this conversation.',
        'core-chat-summary-dialog.title': 'What was summarised',
        'core-chat-summary-dialog.help':
          'Written by the model, from the messages it replaced. If it says something the conversation above does not, that is worth knowing — and is the reason this can be opened.',
      }),
      es: Object.freeze({
        'core-chat-summary-dialog.replaced_count':
          '{{messages}} mensajes anteriores se reemplazaron por el resumen siguiente, {{time}}.',
        'core-chat-summary-dialog.replaced':
          'La parte anterior de esta conversación se reemplazó por el resumen siguiente, {{time}}.',
        'core-chat-summary-dialog.retained':
          'Todos siguen encima de este punto en el panel y en la base de datos; no se eliminó nada. Lo que cambió es lo que se envía al modelo: desde aquí recibe este resumen.',
        'core-chat-summary-dialog.summary':
          'El resumen que ahora recibe el modelo',
        'core-chat-summary-dialog.range':
          'Cubre los mensajes {{from}} a {{to}} de esta conversación.',
        'core-chat-summary-dialog.title': 'Lo que se resumió',
        'core-chat-summary-dialog.help':
          'Lo escribió el modelo a partir de los mensajes que reemplazó. Si dice algo que la conversación anterior no dice, conviene saberlo.',
      }),
      hi: Object.freeze({
        'core-chat-summary-dialog.replaced_count':
          '{{messages}} पहले के संदेशों को नीचे दिए सारांश से बदला गया, {{time}}।',
        'core-chat-summary-dialog.replaced':
          'इस बातचीत का पुराना भाग नीचे दिए सारांश से बदला गया, {{time}}।',
        'core-chat-summary-dialog.retained':
          'वे सभी अभी भी पैनल में इस बिंदु के ऊपर और डेटाबेस में हैं — कुछ हटाया नहीं गया। अब मॉडल को इन संदेशों के बजाय यह सारांश भेजा जाता है।',
        'core-chat-summary-dialog.summary': 'वह सारांश जो मॉडल को अब मिलता है',
        'core-chat-summary-dialog.range':
          'यह इस बातचीत के संदेश {{from}} से {{to}} तक को कवर करता है।',
        'core-chat-summary-dialog.title': 'जिसका सारांश बनाया गया',
        'core-chat-summary-dialog.help':
          'मॉडल ने इसे बदले गए संदेशों से लिखा है। यदि यह ऊपर की बातचीत से अलग कुछ कहता है, तो यह जानना महत्वपूर्ण है।',
      }),
      ar: Object.freeze({
        'core-chat-summary-dialog.replaced_count':
          'استُبدلت {{messages}} رسالة سابقة بالملخص أدناه، {{time}}.',
        'core-chat-summary-dialog.replaced':
          'استُبدل الجزء الأقدم من هذه المحادثة بالملخص أدناه، {{time}}.',
        'core-chat-summary-dialog.retained':
          'ما زالت كلها فوق هذه النقطة في اللوحة وفي قاعدة البيانات — لم يُحذف شيء. ما تغيّر هو ما يُرسل إلى النموذج: من هنا يتلقى هذا الملخص بدلًا من الرسائل.',
        'core-chat-summary-dialog.summary': 'الملخص الذي يتلقاه النموذج الآن',
        'core-chat-summary-dialog.range':
          'يغطي الرسائل من {{from}} إلى {{to}} في هذه المحادثة.',
        'core-chat-summary-dialog.title': 'ما لُخّص',
        'core-chat-summary-dialog.help':
          'كتبه النموذج من الرسائل التي استبدلها. إذا قال شيئًا لا تقوله المحادثة أعلاه، فهذه معلومة مهمة.',
      }),
      zh: Object.freeze({
        'core-chat-summary-dialog.replaced_count':
          '较早的 {{messages}} 条消息已在 {{time}} 被下方摘要替换。',
        'core-chat-summary-dialog.replaced':
          '此对话较早的部分已在 {{time}} 被下方摘要替换。',
        'core-chat-summary-dialog.retained':
          '它们仍在面板此处上方，也仍在数据库中——没有删除任何内容。改变的是发送给模型的内容：从这里开始，它会收到此摘要而不是那些消息。',
        'core-chat-summary-dialog.summary': '模型现在收到的摘要',
        'core-chat-summary-dialog.range':
          '它涵盖此对话中的消息 {{from}} 到 {{to}}。',
        'core-chat-summary-dialog.title': '已摘要的内容',
        'core-chat-summary-dialog.help':
          '该摘要由模型根据它替换的消息编写。如果它说了上方对话没有说的内容，这值得注意。',
      }),
    }),
  }),
  render(message, helpers) {
    const { renderMarkdown, showListDialog, whenLabel, i18n } = helpers;
    const covers = message.covers ?? {};
    const t = (key, values, fallback) => i18n.t(key, values, fallback);
    const what = document.createElement('p');
    what.className = 'muted small';
    what.textContent =
      t(
        covers.messages
          ? 'core-chat-summary-dialog.replaced_count'
          : 'core-chat-summary-dialog.replaced',
        { messages: covers.messages, time: whenLabel(message.at) },
        covers.messages
          ? '{{messages}} earlier messages were replaced with the summary below, {{time}}.'
          : 'The older part of this conversation was replaced with the summary below, {{time}}.',
      ) +
      ' ' +
      t(
        'core-chat-summary-dialog.retained',
        {},
        'They are all still above this point in the panel and still in the database — nothing was deleted. What changed is what the model is sent: from here on it gets this summary instead of those messages.',
      );
    const body = document.createElement('div');
    body.className = 'day-entry';
    const title = document.createElement('strong');
    title.textContent = t(
      'core-chat-summary-dialog.summary',
      {},
      'The summary the model now receives',
    );
    const text = document.createElement('div');
    text.appendChild(renderMarkdown(message.content));
    body.append(title, text);
    const nodes = [what, body];
    if (covers.from_message && covers.to_message) {
      const range = document.createElement('p');
      range.className = 'muted small';
      range.textContent = t(
        'core-chat-summary-dialog.range',
        { from: covers.from_message, to: covers.to_message },
        'It covers messages {{from}} to {{to}} in this conversation.',
      );
      nodes.push(range);
    }
    showListDialog(
      t('core-chat-summary-dialog.title', {}, 'What was summarised'),
      nodes,
      t(
        'core-chat-summary-dialog.help',
        {},
        'Written by the model, from the messages it replaced. If it says something the conversation above does not, that is worth knowing — and is the reason this can be opened.',
      ),
    );
  },
};
