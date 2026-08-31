/* Core card package: reaction image shown in a conversation. */
window.ZogographChatReactionCard = {
  manifest: Object.freeze({
    id: 'core-chat-reaction',
    locales: ['core-chat-reaction.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({ 'core-chat-reaction.alt': 'A reaction' }),
      es: Object.freeze({ 'core-chat-reaction.alt': 'Una reacción' }),
      hi: Object.freeze({ 'core-chat-reaction.alt': 'एक प्रतिक्रिया' }),
      ar: Object.freeze({ 'core-chat-reaction.alt': 'تفاعل' }),
      zh: Object.freeze({ 'core-chat-reaction.alt': '一张反应图片' }),
    }),
  }),
  text(key, valuesOrHelpers = {}, maybeHelpers = null) {
    const helpers = maybeHelpers || valuesOrHelpers;
    const values = maybeHelpers ? valuesOrHelpers : {};
    return helpers.i18n.t(
      `core-chat-reaction.${key}`,
      values,
      this.manifest.catalogs.en[`core-chat-reaction.${key}`],
    );
  },
  render(message, helpers) {
    const args = message.arguments ?? {};
    const card = document.createElement('div');
    card.className = 'chat-reaction';
    const image = document.createElement('img');
    image.src = message.url;
    image.alt = String(
      args.mood || helpers.i18n.t('core-chat-reaction.alt', {}, 'A reaction'),
    );
    image.loading = 'lazy';
    card.appendChild(image);
    if (args.why) {
      const caption = document.createElement('div');
      caption.className = 'muted small';
      caption.textContent = args.why;
      card.appendChild(caption);
    }
    return card;
  },
};
