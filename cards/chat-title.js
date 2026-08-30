/* Core card package: assistant identity shown at the top of a chat. */
window.ZogographChatTitleCard = {
  manifest: Object.freeze({
    id: 'core-chat-title',
    locales: ['core-chat-title.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-title.portrait_title':
          '{{name}}, as it describes itself: "{{description}}"',
      }),
      es: Object.freeze({
        'core-chat-title.portrait_title':
          '{{name}}, como se describe: "{{description}}"',
      }),
      hi: Object.freeze({
        'core-chat-title.portrait_title':
          '{{name}}, जैसा यह स्वयं को बताता है: "{{description}}"',
      }),
      ar: Object.freeze({
        'core-chat-title.portrait_title':
          '{{name}}، كما يصف نفسه: "{{description}}"',
      }),
      zh: Object.freeze({
        'core-chat-title.portrait_title':
          '{{name}}，按其自我描述：“{{description}}”',
      }),
    }),
  }),
  render(input, helpers) {
    const { i18n } = helpers;
    const wrap = document.createDocumentFragment();
    if (input.portrait) {
      const face = document.createElement('img');
      face.className = 'chat-face';
      face.src = input.portrait.url;
      face.alt = '';
      face.title = i18n.t(
        'core-chat-title.portrait_title',
        { name: input.name, description: input.portrait.as },
        '{{name}}, as it describes itself: "{{description}}"',
      );
      wrap.append(face);
    } else {
      const mark = document.createElement('span');
      mark.className = 'chat-emoji';
      mark.textContent = input.emoji;
      wrap.append(mark);
    }
    const name = document.createElement('span');
    name.textContent = ` ${input.name}`;
    wrap.append(name);
    return wrap;
  },
};
