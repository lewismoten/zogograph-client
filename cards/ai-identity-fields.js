/* Core card package: editable identity fields used by personality presets. */
window.ZogographAiIdentityFieldsCard = {
  manifest: Object.freeze({
    id: 'core-ai-identity-fields',
    locales: ['core-ai-identity-fields.'],
    helpers: ['fieldRow', 'aiSettingsText', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-identity-fields.name': 'Name',
        'core-ai-identity-fields.emoji': 'Emoji',
        'core-ai-identity-fields.character': 'Character',
      }),
      es: Object.freeze({
        'core-ai-identity-fields.name': 'Nombre',
        'core-ai-identity-fields.emoji': 'Emoji',
        'core-ai-identity-fields.character': 'Carácter',
      }),
      hi: Object.freeze({
        'core-ai-identity-fields.name': 'नाम',
        'core-ai-identity-fields.emoji': 'इमोजी',
        'core-ai-identity-fields.character': 'चरित्र',
      }),
      ar: Object.freeze({
        'core-ai-identity-fields.name': 'الاسم',
        'core-ai-identity-fields.emoji': 'الرمز التعبيري',
        'core-ai-identity-fields.character': 'الشخصية',
      }),
      zh: Object.freeze({
        'core-ai-identity-fields.name': '名称',
        'core-ai-identity-fields.emoji': '表情符号',
        'core-ai-identity-fields.character': '角色设定',
      }),
    }),
  }),
  render(input, helpers) {
    const { who } = input;
    const { fieldRow, aiSettingsText, i18n } = helpers;
    const form = document.createElement('div');
    form.className = 'settings';
    const name = document.createElement('input');
    name.type = 'text';
    name.id = 'aiName';
    name.value = who.name;
    name.size = 24;
    name.maxLength = 40;
    form.appendChild(
      fieldRow(
        i18n.t('core-ai-identity-fields.name', {}, 'Name'),
        name,
        aiSettingsText(
          'identity_name_help',
          'What it calls itself, and what this page and the menu call it.',
        ),
      ),
    );
    const emoji = document.createElement('input');
    emoji.type = 'text';
    emoji.id = 'aiEmoji';
    emoji.value = who.emoji;
    emoji.size = 4;
    emoji.maxLength = 8;
    form.appendChild(
      fieldRow(
        i18n.t('core-ai-identity-fields.emoji', {}, 'Emoji'),
        emoji,
        aiSettingsText(
          'identity_emoji_help',
          'Shown beside the name everywhere it appears, including in the menu.',
        ),
      ),
    );
    const personality = document.createElement('textarea');
    personality.id = 'aiPersonality';
    personality.rows = 24;
    personality.className = 'wide-field persona-box';
    personality.spellcheck = false;
    personality.value = who.personality;
    form.appendChild(
      fieldRow(
        i18n.t('core-ai-identity-fields.character', {}, 'Character'),
        personality,
        aiSettingsText(
          'identity_character_help',
          'Sent with every single request, so length here is a cost on every turn. It cannot override the rules that keep the answers honest — never state a cause you have not measured, say when a tool returned nothing — which are added after it and say so.',
        ),
      ),
    );
    return { form, name, emoji, personality };
  },
};
