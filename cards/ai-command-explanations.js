/* Core card package: explanatory terminal-command preference. */
window.ZogographAiCommandExplanationsCard = {
  manifest: Object.freeze({
    id: 'core-ai-command-explanations',
    locales: ['core-ai-command-explanations.'],
    helpers: ['fieldRow', 'i18n'],
    catalogs: Object.freeze({
      en: Object.freeze({
        'core-ai-command-explanations.label': 'Explain its commands',
        'core-ai-command-explanations.help':
          'When it suggests a terminal command, it comments each part, says what the output should look like, and links to the documentation. Worth leaving on: somebody pasting a command they have not understood is the thing this is meant to prevent. Turn it off if you read shell fluently and find the commentary noise.',
      }),
      es: Object.freeze({
        'core-ai-command-explanations.label': 'Explicar sus comandos',
        'core-ai-command-explanations.help':
          'Cuando sugiere un comando de terminal, comenta cada parte, indica el resultado esperado y enlaza la documentación. Desactívelo si domina la shell y le molesta el comentario.',
      }),
      hi: Object.freeze({
        'core-ai-command-explanations.label': 'इसके कमांड समझाएँ',
        'core-ai-command-explanations.help':
          'टर्मिनल कमांड सुझाने पर यह हर भाग समझाता है, अपेक्षित आउटपुट बताता है और दस्तावेज़ से जोड़ता है। शेल में सहज हों तो इसे बंद कर सकते हैं।',
      }),
      ar: Object.freeze({
        'core-ai-command-explanations.label': 'اشرح أوامره',
        'core-ai-command-explanations.help':
          'عند اقتراح أمر طرفية، يشرح كل جزء ويذكر شكل الناتج المتوقع ويربط التوثيق. أوقفه إن كنت تقرأ shell بطلاقة.',
      }),
      zh: Object.freeze({
        'core-ai-command-explanations.label': '解释其命令',
        'core-ai-command-explanations.help':
          '建议终端命令时，它会解释每个部分、说明预期输出并链接文档。若熟悉 shell 且觉得说明多余，可将其关闭。',
      }),
    }),
  }),
  render(data, helpers) {
    const input = document.createElement('input');
    input.type = 'checkbox';
    input.id = 'aiExplain';
    input.checked = data.explain_commands !== false;
    return helpers.fieldRow(
      helpers.i18n.t(
        'core-ai-command-explanations.label',
        {},
        'Explain its commands',
      ),
      input,
      helpers.i18n.t(
        'core-ai-command-explanations.help',
        {},
        'When it suggests a terminal command, it comments each part, says what the output should look like, and links to the documentation. Worth leaving on: somebody pasting a command they have not understood is the thing this is meant to prevent. Turn it off if you read shell fluently and find the commentary noise.',
      ),
    );
  },
};
