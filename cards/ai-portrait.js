/* Core card package: the current AI portrait and its retention-aware clear action. */
window.ZogographAiPortraitCard = {
  manifest: Object.freeze({
    id: 'core-ai-portrait',
    locales: ['core-ai-portrait.'],
    helpers: [
      'fieldRow',
      'aiSettingsText',
      'ago',
      'askFirst',
      'post',
      'renderAiSettings',
      'notice',
      'aiPortraitAlt',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-portrait.clear': 'Clear this portrait',
        'core-ai-portrait.cleared': 'Cleared.',
      }),
      es: Object.freeze({
        'core-ai-portrait.clear': 'Borrar este retrato',
        'core-ai-portrait.cleared': 'Borrado.',
      }),
      hi: Object.freeze({
        'core-ai-portrait.clear': 'यह चित्र हटाएँ',
        'core-ai-portrait.cleared': 'हटा दिया गया।',
      }),
      ar: Object.freeze({
        'core-ai-portrait.clear': 'امسح هذه الصورة',
        'core-ai-portrait.cleared': 'تم المسح.',
      }),
      zh: Object.freeze({
        'core-ai-portrait.clear': '清除此肖像',
        'core-ai-portrait.cleared': '已清除。',
      }),
    }),
  }),
  render(input, helpers) {
    const { who } = input;
    const {
      fieldRow,
      aiSettingsText,
      ago,
      askFirst,
      post,
      renderAiSettings,
      notice,
      aiPortraitAlt,
      i18n,
    } = helpers;
    const portrait = who.portrait;
    const face = document.createElement('div');
    face.className = 'portrait-block';
    const image = document.createElement('img');
    image.className = 'portrait';
    image.src = portrait.url;
    image.alt = aiPortraitAlt(who.name);
    face.appendChild(image);
    const caption = document.createElement('div');
    caption.className = 'muted small';
    caption.textContent = i18n.t(
      'image.portrait_caption',
      { elapsed: ago(portrait.at), description: portrait.as },
      `Drawn ${ago(portrait.at)}, from: "${portrait.as}"`,
    );
    face.appendChild(caption);
    const clear = document.createElement('button');
    clear.type = 'button';
    clear.className = 'link danger';
    clear.textContent = i18n.t(
      'core-ai-portrait.clear',
      {},
      'Clear this portrait',
    );
    clear.addEventListener('click', async () => {
      const yes = await askFirst(
        i18n.t('ai.clear_portrait_title', {}, 'Clear the portrait?'),
        [
          i18n.t(
            'ai.clear_portrait_hide',
            {},
            'The picture stops being shown and the emoji takes its place until it draws another one.',
          ),
          i18n.t(
            'ai.clear_portrait_retention',
            {},
            'The record of it stays in the image log — it was generated and it cost something, and that is worth keeping whether or not the picture is shown.',
          ),
        ],
        i18n.t('notes.clear', {}, 'Clear it'),
        true,
      );
      if (!yes) return;
      try {
        renderAiSettings(await post('ai-portrait-clear', {}));
        notice(i18n.t('core-ai-portrait.cleared', {}, 'Cleared.'), true);
      } catch (error) {
        notice(String(error.message || error));
      }
    });
    face.appendChild(clear);
    return fieldRow(
      aiSettingsText('identity_portrait_label', 'Portrait'),
      face,
      aiSettingsText(
        'identity_portrait_help',
        'It drew this itself, the last time it was asked to. Turning off self-portraits below stops it drawing another; this one stays until it does.',
      ),
    );
  },
};
