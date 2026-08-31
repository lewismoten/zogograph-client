/* Core card package: an image generated in a conversation. */
window.ZogographChatPictureCard = {
  manifest: Object.freeze({
    id: 'core-chat-picture',
    locales: ['core-chat-picture.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-picture.alt': 'A generated picture',
        'core-chat-picture.portrait':
          '{{name}}, as it describes itself: "{{description}}"',
        'core-chat-picture.caption': 'Drawn from: "{{description}}"',
      }),
      es: Object.freeze({
        'core-chat-picture.alt': 'Una imagen generada',
        'core-chat-picture.portrait':
          '{{name}}, como se describe: "{{description}}"',
        'core-chat-picture.caption': 'Dibujado a partir de: "{{description}}"',
      }),
      hi: Object.freeze({
        'core-chat-picture.alt': 'बनाया गया चित्र',
        'core-chat-picture.portrait':
          '{{name}}, जैसा यह स्वयं का वर्णन करता है: "{{description}}"',
        'core-chat-picture.caption': 'इससे बनाया गया: "{{description}}"',
      }),
      ar: Object.freeze({
        'core-chat-picture.alt': 'صورة مولدة',
        'core-chat-picture.portrait':
          '{{name}}، كما يصف نفسه: "{{description}}"',
        'core-chat-picture.caption': 'رُسم من: "{{description}}"',
      }),
      zh: Object.freeze({
        'core-chat-picture.alt': '生成的图片',
        'core-chat-picture.portrait':
          '{{name}}，按其自我描述："{{description}}"',
        'core-chat-picture.caption': '根据以下描述绘制："{{description}}"',
      }),
    }),
  }),
  render(input, helpers) {
    const { message, assistant } = input;
    const { i18n } = helpers;
    const args = message.arguments ?? {};
    const card = document.createElement('div');
    card.className = 'chat-picture';
    const image = document.createElement('img');
    image.src = message.url;
    image.alt = String(
      args.description ||
        i18n.t('core-chat-picture.alt', {}, 'A generated picture'),
    );
    image.loading = 'lazy';
    const caption = document.createElement('div');
    caption.className = 'muted small';
    caption.textContent = args.portrait
      ? i18n.t(
          'core-chat-picture.portrait',
          { name: assistant, description: args.description },
          '{{name}}, as it describes itself: "{{description}}"',
        )
      : i18n.t(
          'core-chat-picture.caption',
          { description: args.description ?? '' },
          'Drawn from: "{{description}}"',
        );
    card.append(image, caption);
    return card;
  },
};
