/* Core card package: composition of identity fields, presets, and portrait. */
window.ZogographAiIdentitySettingsCard = {
  manifest: Object.freeze({
    id: 'core-ai-identity-settings',
    locales: ['core-ai-identity-settings.'],
    helpers: [
      'fieldRow',
      'renderCard',
      'identityFieldsCard',
      'personalityPickerCard',
      'portraitCard',
      'ago',
      'askTextDialog',
      'askFirst',
      'post',
      'renderAiSettings',
      'notice',
      'aiPortraitAlt',
      'i18n',
    ],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-identity-settings.picker_note':
          'Choose a saved personality or edit the fields below.',
      }),
      es: Object.freeze({
        'core-ai-identity-settings.picker_note':
          'Elija una personalidad guardada o edite los campos.',
      }),
      hi: Object.freeze({
        'core-ai-identity-settings.picker_note':
          'सहेजी गई व्यक्तित्व चुनें या नीचे के फ़ील्ड संपादित करें।',
      }),
      ar: Object.freeze({
        'core-ai-identity-settings.picker_note':
          'اختر شخصية محفوظة أو حرر الحقول أدناه.',
      }),
      zh: Object.freeze({
        'core-ai-identity-settings.picker_note':
          '选择已保存的个性，或编辑以下字段。',
      }),
    }),
  }),
  render(input, helpers) {
    const { data } = input;
    const {
      fieldRow,
      renderCard,
      identityFieldsCard,
      personalityPickerCard,
      portraitCard,
      ago,
      askTextDialog,
      askFirst,
      post,
      renderAiSettings,
      notice,
      aiPortraitAlt,
      i18n,
    } = helpers;
    const who = data.who;
    const identity = renderCard(
      identityFieldsCard,
      'core-ai-identity-fields',
      { who },
      { fieldRow, i18n },
    );
    const { form, name, emoji, personality } = identity;
    const wrap = document.createDocumentFragment();
    wrap.appendChild(
      renderCard(
        personalityPickerCard,
        'core-ai-personality-picker',
        {
          personalities: data.personalities ?? [],
          boxes: { name, emoji, personality },
        },
        { askTextDialog, askFirst, post, renderAiSettings, notice, i18n },
      ),
    );
    if (who.portrait) {
      form.appendChild(
        renderCard(
          portraitCard,
          'core-ai-portrait',
          { who },
          {
            fieldRow,
            ago,
            askFirst,
            post,
            renderAiSettings,
            notice,
            aiPortraitAlt,
            i18n,
          },
        ),
      );
    }
    wrap.appendChild(form);
    return wrap;
  },
};
