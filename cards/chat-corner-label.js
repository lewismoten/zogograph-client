/* Core card package: dynamic label for the corner chat button. */
window.ZogographChatCornerLabelCard = {
  manifest: Object.freeze({
    id: 'core-chat-corner-label',
    locales: ['core-chat-corner-label.'],
    helpers: ['i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-chat-corner-label.text': '{{emoji}} Ask {{assistant}}',
      }),
      es: Object.freeze({
        'core-chat-corner-label.text': '{{emoji}} Preguntar a {{assistant}}',
      }),
      hi: Object.freeze({
        'core-chat-corner-label.text': '{{emoji}} {{assistant}} से पूछें',
      }),
      ar: Object.freeze({
        'core-chat-corner-label.text': '{{emoji}} اسأل {{assistant}}',
      }),
      zh: Object.freeze({
        'core-chat-corner-label.text': '{{emoji}} 询问 {{assistant}}',
      }),
    }),
  }),
  render(input, helpers) {
    if (input.button)
      input.button.textContent = helpers.i18n.t(
        'core-chat-corner-label.text',
        input.who,
        '{{emoji}} Ask {{assistant}}',
      );
    return null;
  },
};
