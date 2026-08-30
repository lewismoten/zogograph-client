/* Core card package: editable identity fields used by personality presets. */
window.ZogographAiIdentityFieldsCard = {
  manifest: Object.freeze({
    id: 'core-ai-identity-fields',
    locales: ['core-ai-identity-fields.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-identity-fields.name': 'Name',
        'core-ai-identity-fields.emoji': 'Emoji',
        'core-ai-identity-fields.character': 'Character',
        'core-ai-identity-fields.name_help':
          'What it calls itself, and what this page and the menu call it.',
        'core-ai-identity-fields.emoji_help':
          'Shown beside the name everywhere it appears, including in the menu.',
        'core-ai-identity-fields.character_help':
          'Sent with every single request, so length here is a cost on every turn. It cannot override the rules that keep the answers honest — never state a cause you have not measured, say when a tool returned nothing — which are added after it and say so.',
      }),
      es: Object.freeze({
        'core-ai-identity-fields.name': 'Nombre',
        'core-ai-identity-fields.emoji': 'Emoji',
        'core-ai-identity-fields.character': 'Carácter',
        'core-ai-identity-fields.name_help':
          'Cómo se llama y cómo lo llaman esta página y el menú.',
        'core-ai-identity-fields.emoji_help':
          'Se muestra junto al nombre en todos los lugares donde aparece, incluido el menú.',
        'core-ai-identity-fields.character_help':
          'Se envía con cada solicitud, por lo que su longitud cuesta en cada turno. No puede anular las reglas que mantienen las respuestas honestas: nunca afirmar una causa que no se ha medido e indicar cuando una herramienta no devolvió nada; se añaden después y así lo dicen.',
      }),
      hi: Object.freeze({
        'core-ai-identity-fields.name': 'नाम',
        'core-ai-identity-fields.emoji': 'इमोजी',
        'core-ai-identity-fields.character': 'चरित्र',
        'core-ai-identity-fields.name_help':
          'यह स्वयं को क्या कहता है और यह पृष्ठ व मेनू इसे क्या कहते हैं।',
        'core-ai-identity-fields.emoji_help':
          'मेनू सहित, जहाँ भी नाम दिखता है उसके साथ दिखाया जाता है।',
        'core-ai-identity-fields.character_help':
          'यह हर अनुरोध के साथ भेजा जाता है, इसलिए इसकी लंबाई हर टर्न पर लागत बढ़ाती है। यह उत्तरों को ईमानदार रखने वाले नियमों को नहीं बदल सकता — बिना मापे कारण न बताना और किसी टूल से कुछ न लौटने पर ऐसा कहना — वे इसके बाद जोड़े जाते हैं और यही कहते हैं।',
      }),
      ar: Object.freeze({
        'core-ai-identity-fields.name': 'الاسم',
        'core-ai-identity-fields.emoji': 'الرمز التعبيري',
        'core-ai-identity-fields.character': 'الشخصية',
        'core-ai-identity-fields.name_help':
          'ما الاسم الذي يطلقه على نفسه، وما الذي تسميه به هذه الصفحة والقائمة.',
        'core-ai-identity-fields.emoji_help':
          'يظهر بجانب الاسم في كل موضع يظهر فيه، بما في ذلك القائمة.',
        'core-ai-identity-fields.character_help':
          'يُرسل مع كل طلب، لذا فإن طوله يضيف تكلفة في كل دور. لا يمكنه تجاوز القواعد التي تُبقي الإجابات صادقة — لا تذكر سببًا لم يُقَس، وقل عندما لا تعيد أداة شيئًا — فهي تُضاف بعده وتقول ذلك.',
      }),
      zh: Object.freeze({
        'core-ai-identity-fields.name': '名称',
        'core-ai-identity-fields.emoji': '表情符号',
        'core-ai-identity-fields.character': '角色设定',
        'core-ai-identity-fields.name_help':
          '它如何称呼自己，以及本页面和菜单如何称呼它。',
        'core-ai-identity-fields.emoji_help':
          '会显示在名称旁边，包括菜单中的名称旁边。',
        'core-ai-identity-fields.character_help':
          '每个请求都会附带这段内容，因此其长度会增加每轮的成本。它不能覆盖保证回答诚实的规则——不得说明未测量的原因，工具没有返回结果时要说明——这些规则会在其后附加并明确说明这一点。',
      }),
    }),
  }),
  render(input, helpers) {
    const { who } = input;
    const { fieldRow, i18n } = helpers;
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
        i18n.t(
          'core-ai-identity-fields.name_help',
          {},
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
        i18n.t(
          'core-ai-identity-fields.emoji_help',
          {},
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
        i18n.t(
          'core-ai-identity-fields.character_help',
          {},
          'Sent with every single request, so length here is a cost on every turn. It cannot override the rules that keep the answers honest — never state a cause you have not measured, say when a tool returned nothing — which are added after it and say so.',
        ),
      ),
    );
    return { form, name, emoji, personality };
  },
};
